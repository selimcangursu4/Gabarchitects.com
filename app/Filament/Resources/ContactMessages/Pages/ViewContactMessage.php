<?php

namespace App\Filament\Resources\ContactMessages\Pages;

use App\Enums\ContactStatus;
use App\Filament\Resources\ContactMessages\ContactMessageResource;
use App\Models\ContactMessage;
use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\ViewRecord;

class ViewContactMessage extends ViewRecord
{
    protected static string $resource = ContactMessageResource::class;

    /** Açılan mesaj okundu sayılır; "Yeni" ise "İnceleniyor"a geçer. */
    public function mount(int|string $record): void
    {
        parent::mount($record);

        /** @var ContactMessage $message */
        $message = $this->getRecord();

        if (! $message->read_at) {
            $message->update([
                'read_at' => now(),
                'status' => $message->status === ContactStatus::New ? ContactStatus::InReview : $message->status,
                'handled_by' => $message->handled_by ?? auth()->id(),
            ]);
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            ...ContactMessageResource::statusActions(),
            DeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
