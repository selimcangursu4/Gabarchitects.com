<?php

namespace App\Filament\Resources\Media\Pages;

use App\Filament\Resources\Media\MediaResource;
use App\Models\Media;
use App\Services\MediaService;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use InvalidArgumentException;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class ListMedia extends ListRecords
{
    protected static string $resource = MediaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('upload')->label('Görsel yükle')->icon(Heroicon::OutlinedArrowUpTray)
                ->authorize(fn () => auth()->user()->can('create', Media::class))
                ->schema([
                    FileUpload::make('files')->label('Görseller')->multiple()->required()->image()
                        ->acceptedFileTypes(config('media.allowed_mimes'))
                        ->maxSize(config('media.max_upload_kb'))
                        ->maxFiles(30)
                        ->storeFiles(false)
                        ->helperText('JPG, PNG veya WebP · en fazla '.(config('media.max_upload_kb') / 1024).' MB. WebP/AVIF boyutları otomatik üretilir.'),
                ])
                ->action(function (array $data) {
                    $service = app(MediaService::class);
                    $ok = 0;
                    $failed = [];

                    foreach ($data['files'] as $file) {
                        /** @var TemporaryUploadedFile $file */
                        try {
                            $service->storeUpload($file, ['uploaded_by' => auth()->id()]);
                            $ok++;
                        } catch (InvalidArgumentException) {
                            $failed[] = $file->getClientOriginalName();
                        }
                    }

                    Notification::make()
                        ->title("{$ok} görsel yüklendi")
                        ->body($failed ? 'Yüklenemeyen: '.implode(', ', $failed) : null)
                        ->{$failed ? 'warning' : 'success'}()
                        ->send();
                }),
        ];
    }
}
