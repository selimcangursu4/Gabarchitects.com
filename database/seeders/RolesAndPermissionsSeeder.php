<?php

namespace Database\Seeders;

use App\Enums\Role as RoleEnum;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Yetki matrisi (brief §7.8):
 *  - Super Admin: her şey (Gate::before ile).
 *  - Admin: kullanıcı yönetimi hariç her şey.
 *  - Editor: yalnızca içerik (proje, hizmet, sayfa, blog, referans, gayrimenkul, medya, SSS, ekip).
 */
class RolesAndPermissionsSeeder extends Seeder
{
    /** @var array<string, string> izin => açıklama */
    public const PERMISSIONS = [
        'projects.manage' => 'Projeleri yönet',
        'services.manage' => 'Hizmetleri yönet',
        'pages.manage' => 'Sayfaları yönet',
        'blog.manage' => 'Blogu yönet',
        'references.manage' => 'Referansları yönet',
        'properties.manage' => 'Gayrimenkul portföyünü yönet',
        'media.manage' => 'Medya kütüphanesini yönet',
        'faqs.manage' => 'SSS yönet',
        'team.manage' => 'Ekibi yönet',
        'menus.manage' => 'Menüleri yönet',
        'contact_messages.manage' => 'İletişim taleplerini yönet',
        'redirects.manage' => 'Yönlendirmeleri yönet',
        'settings.manage' => 'Site ayarlarını yönet',
        'activity_logs.view' => 'İşlem kayıtlarını görüntüle',
        'users.manage' => 'Kullanıcıları yönet',
    ];

    public const EDITOR_PERMISSIONS = [
        'projects.manage', 'services.manage', 'pages.manage', 'blog.manage', 'references.manage',
        'properties.manage', 'media.manage', 'faqs.manage', 'team.manage',
    ];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (array_keys(self::PERMISSIONS) as $name) {
            Permission::findOrCreate($name, 'web');
        }

        Role::findOrCreate(RoleEnum::SuperAdmin->value, 'web');

        Role::findOrCreate(RoleEnum::Admin->value, 'web')
            ->syncPermissions(array_diff(array_keys(self::PERMISSIONS), ['users.manage']));

        Role::findOrCreate(RoleEnum::Editor->value, 'web')
            ->syncPermissions(self::EDITOR_PERMISSIONS);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
