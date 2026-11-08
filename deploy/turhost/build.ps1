# Turhost upload package (TD-063, Scenario B). ASCII only (Windows PowerShell 5.1).
#
#   powershell -ExecutionPolicy Bypass -File deploy\turhost\build.ps1
#
# Output (dist\turhost):
#   gab.zip          -> extract into ~/gab          (app; outside the web root)
#   public_html.zip  -> extract into ~/public_html  (web root)
#   gab\.env         -> generated from env.production.example with a fresh APP_KEY (NOT inside any zip;
#                       fill the <...> values and upload it separately as ~/gab/.env)
#
# Package = last commit (git archive). Uncommitted changes are NOT included.

param([string]$Out = 'dist\turhost')

$ErrorActionPreference = 'Stop'
$root = (Resolve-Path (Join-Path $PSScriptRoot '..\..')).Path
Set-Location $root

Add-Type -AssemblyName System.IO.Compression, System.IO.Compression.FileSystem

# Zip with forward-slash entry names (Windows PowerShell 5.1 ZipFile writes backslashes, which Linux/cPanel
# extracts as literal file names like "vendor\autoload.php"). Hidden files (.htaccess) and empty folders included.
function New-Zip([string]$dir, [string]$zipPath) {
    $dir = (Resolve-Path $dir).Path.TrimEnd('\')
    $fs = [IO.File]::Open($zipPath, [IO.FileMode]::Create)
    $zip = New-Object IO.Compression.ZipArchive($fs, [IO.Compression.ZipArchiveMode]::Create)
    try {
        foreach ($f in [IO.Directory]::EnumerateFiles($dir, '*', [IO.SearchOption]::AllDirectories)) {
            $name = $f.Substring($dir.Length + 1).Replace('\', '/')
            [IO.Compression.ZipFileExtensions]::CreateEntryFromFile($zip, $f, $name, [IO.Compression.CompressionLevel]::Optimal) | Out-Null
        }
        foreach ($d in [IO.Directory]::EnumerateDirectories($dir, '*', [IO.SearchOption]::AllDirectories)) {
            if (-not [IO.Directory]::EnumerateFileSystemEntries($d).GetEnumerator().MoveNext()) {
                $zip.CreateEntry($d.Substring($dir.Length + 1).Replace('\', '/') + '/') | Out-Null
            }
        }
    } finally {
        $zip.Dispose()
        $fs.Dispose()
    }
}

function Run($exe, [string[]]$arguments) {
    & $exe @arguments
    if ($LASTEXITCODE -ne 0) { throw "$exe failed ($LASTEXITCODE)" }
}

if (git status --porcelain) { Write-Warning 'Uncommitted changes exist; the package uses the last commit only.' }

$outDir = Join-Path $root $Out
if (Test-Path $outDir) { Remove-Item -Recurse -Force $outDir }
$app = Join-Path $outDir 'gab'
$web = Join-Path $outDir 'public_html'
New-Item -ItemType Directory -Force $app, $web | Out-Null

Write-Output '1/6 Source (git archive HEAD)'
$srcZip = Join-Path $outDir 'source.zip'
Run git @('archive', '--format=zip', '-o', $srcZip, 'HEAD')
[IO.Compression.ZipFile]::ExtractToDirectory($srcZip, $app)
Remove-Item $srcZip

Write-Output '2/6 Front-end assets (npm run build)'
Run npm @('run', 'build')
Copy-Item -Recurse (Join-Path $root 'public\build') (Join-Path $app 'public\build')

Write-Output '3/6 Composer (no-dev, optimized)'
Run composer @('install', '--no-dev', '--optimize-autoloader', '--no-interaction', '--no-progress', "--working-dir=$app")

Write-Output '4/6 Remove files not needed on the server'
foreach ($p in @('tests', 'deploy', 'docs', '.github',
                 'phpunit.xml', 'package.json', 'package-lock.json', 'vite.config.js', 'resources\js', 'resources\css',
                 '.editorconfig', '.gitattributes', '.env.example', 'CLAUDE.md', 'AGENTS.md', 'database\database.sqlite')) {
    $t = Join-Path $app $p
    if (Test-Path $t) { Remove-Item -Recurse -Force $t }
}

Write-Output '5/6 Web root (public -> public_html)'
Get-ChildItem -Force (Join-Path $app 'public') | Move-Item -Destination $web
Remove-Item -Recurse -Force (Join-Path $app 'public')
Copy-Item -Force (Join-Path $PSScriptRoot 'index.php') (Join-Path $web 'index.php')
New-Item -ItemType Directory -Force (Join-Path $web 'media') | Out-Null
[IO.File]::WriteAllText((Join-Path $app '.public_path'), "../public_html`n", (New-Object Text.UTF8Encoding($false)))

Write-Output '6/6 Zip + .env'
New-Zip $app (Join-Path $outDir 'gab.zip')
New-Zip $web (Join-Path $outDir 'public_html.zip')

$bytes = New-Object byte[] 32
[Security.Cryptography.RandomNumberGenerator]::Create().GetBytes($bytes)
$key = 'base64:' + [Convert]::ToBase64String($bytes)
$envText = [IO.File]::ReadAllText((Join-Path $PSScriptRoot 'env.production.example'), [Text.Encoding]::UTF8)
$envText = $envText -replace '(?m)^APP_KEY=.*$', ('APP_KEY=' + $key)
[IO.File]::WriteAllText((Join-Path $app '.env'), $envText, (New-Object Text.UTF8Encoding($false)))

Write-Output ''
Write-Output "Ready: $outDir"
Get-ChildItem $outDir -File | ForEach-Object { '{0,-18} {1,8:N1} MB' -f $_.Name, ($_.Length / 1MB) }
Write-Output 'Fill the <...> values in gab\.env, then upload it as ~/gab/.env (never into public_html).'
