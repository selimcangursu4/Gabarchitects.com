<?php

namespace App\Filament\Widgets;

use App\Enums\PublishStatus;
use App\Filament\Pages\ManageSettings;
use App\Filament\Resources\BlogPosts\BlogPostResource;
use App\Filament\Resources\ContactMessages\ContactMessageResource;
use App\Filament\Resources\Projects\ProjectResource;
use App\Filament\Resources\Properties\PropertyResource;
use App\Models\BlogPost;
use App\Models\ContactMessage;
use App\Models\Project;
use App\Models\Property;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/** Brief §7.1: proje, blog ve iletişim sayıları. */
class StatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $user = auth()->user();
        $stats = [];

        if ($user->can('projects.manage')) {
            $published = Project::published()->count();
            $stats[] = Stat::make('Toplam proje', Project::count())
                ->description("{$published} yayında · ".Project::where('status', PublishStatus::Draft->value)->count().' taslak')
                ->icon(Heroicon::OutlinedBuildingOffice2)
                ->url(ProjectResource::getUrl());
        }

        if ($user->can('properties.manage')) {
            $samples = Property::where('is_sample', true)->count();
            $stats[] = Stat::make('İlanlar', Property::published()->count().' yayında')
                ->description($samples ? "{$samples} örnek ilan var — gerçek ilanlar girilince silin" : Property::published()->featured()->count().' öne çıkan')
                ->descriptionColor($samples ? 'warning' : null)
                ->icon(Heroicon::OutlinedHomeModern)
                ->url(PropertyResource::getUrl());
        }

        if ($user->can('blog.manage')) {
            $stats[] = Stat::make('Blog yazıları', BlogPost::count())
                ->description(BlogPost::published()->count().' yayında · '.BlogPost::scheduled()->count().' zamanlanmış')
                ->icon(Heroicon::OutlinedNewspaper)
                ->url(BlogPostResource::getUrl());
        }

        if ($user->can('contact_messages.manage')) {
            $unread = ContactMessage::unread()->count();
            $stats[] = Stat::make('Web formları', ContactMessage::count())
                ->description($unread ? "{$unread} okunmamış" : 'Okunmamış mesaj yok')
                ->descriptionColor($unread ? 'danger' : 'success')
                ->icon(Heroicon::OutlinedInboxArrowDown)
                ->url(ContactMessageResource::getUrl());
        }

        // İlan sayfasındaki WhatsApp / Ara butonları numara girilene kadar pasif
        if ($user->can('settings.manage')) {
            $missing = array_keys(array_filter([
                'WhatsApp' => blank(settings('contact.whatsapp')),
                'Telefon' => blank(settings('contact.phone')),
            ]));

            if ($missing) {
                $stats[] = Stat::make('İlan butonları', 'Pasif')
                    ->description(implode(' ve ', $missing).' numarası girilmedi — Site Ayarları\'ndan ekleyin')
                    ->descriptionColor('warning')
                    ->color('warning')
                    ->icon(Heroicon::OutlinedExclamationTriangle)
                    ->url(ManageSettings::getUrl());
            }
        }

        return $stats;
    }
}
