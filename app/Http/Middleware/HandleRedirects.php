<?php

namespace App\Http\Middleware;

use App\Models\Redirect;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * CMS'ten yönetilen 301/302 yönlendirmeleri.
 * Yalnızca yanıt 404 olduğunda devreye girer → normal sayfalara ek sorgu maliyeti yok.
 */
class HandleRedirects
{
    public const CACHE_KEY = 'redirects.map';

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($response->getStatusCode() !== 404 || ! $request->isMethod('GET')) {
            return $response;
        }

        $path = Redirect::normalize($request->getPathInfo());
        $map = Cache::rememberForever(self::CACHE_KEY, fn () => Redirect::query()
            ->where('is_active', true)
            ->get(['id', 'from_path', 'to_url', 'status_code'])
            ->keyBy('from_path')
            ->map(fn (Redirect $r) => ['id' => $r->id, 'to' => $r->to_url, 'code' => $r->status_code])
            ->all());

        if (! isset($map[$path])) {
            return $response;
        }

        Redirect::whereKey($map[$path]['id'])->incrementEach(['hits' => 1], ['last_hit_at' => now()]);

        return redirect($map[$path]['to'], $map[$path]['code']);
    }
}
