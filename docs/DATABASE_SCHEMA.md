# DATABASE SCHEMA — GAB Architects

> Faz: PHASE 02 · Motor: MySQL 8 / MariaDB 10.6+ (production), SQLite (lokal ve testler)
> Karakter seti: `utf8mb4_unicode_ci` · Migration'lar: [database/migrations/](../database/migrations/)

## Genel kurallar
- Site **yalnızca Türkçe** (TD-003 iptal): içerik alanları doğrudan ana tablolarda tutulur, `*_translations` tablosu yok.
- İçerik tablolarında **soft delete** (`deleted_at`) kullanılır. İstisnalar: `faqs`, `menus`, `menu_items`, `site_settings`, `redirects`, `activity_logs`.
- **Slug**'lar Türkçe kurallarla otomatik üretilir ve silinmiş kayıtlar dahil benzersizdir (`HasSlug`).
- **Yayın:** `status` (`draft` | `published`) + `published_at`. Zamanlanmış yayın = `published` + gelecekteki `published_at`. Cron gerekmez (TD-006, `HasPublishing`).
- Medya için yabancı anahtarlar `nullOnDelete`: görsel silinirse kayıt bozulmaz, görsel boş kalır.

## İlişki diyagramı

```
users ─┬─< blog_posts (author_id)
       ├─< media (uploaded_by)
       ├─< activity_logs
       ├─< contact_messages (handled_by)
       └─ roles/permissions (spatie: model_has_roles, role_has_permissions …)

project_categories ─< projects >─< services          (project_service pivot)
                       │
                       └─< client_references (project_id)

blog_categories ─< blog_posts
properties ─< contact_messages (property_id)
menus ─< menu_items ─< menu_items (parent_id, alt menü)

media ─< mediables >─ {projects, services, properties, …}   (polimorfik galeri)
media ─< *.cover_media_id / logo_media_id / photo_media_id  (kapak)
```

## Tablolar

### Kullanıcı ve yetki
| Tablo | Önemli alanlar | Not |
|---|---|---|
| `users` | name, email (unique), password, is_active, last_login_at, last_login_ip, deleted_at | Spatie `HasRoles` |
| `roles`, `permissions`, `model_has_roles`, `model_has_permissions`, `role_has_permissions` | — | spatie/laravel-permission |

**Roller:** `super_admin` (her şey, `Gate::before`), `admin` (kullanıcı yönetimi hariç her şey), `editor` (yalnızca içerik). İzin listesi: [RolesAndPermissionsSeeder](../database/seeders/RolesAndPermissionsSeeder.php).

### Medya
| Tablo | Önemli alanlar | Not |
|---|---|---|
| `media` | uuid (ULID, unique), disk, original_disk, original_path, original_name, mime_type, size, width, height, variants (JSON), alt, title, uploaded_by | Orijinal `storage/app/private/media/originals` altında; türevler `public/media/{uuid}/{genişlik}.{webp,avif}` |
| `mediables` | media_id, mediable_type/id, collection, sort_order, caption | Galeri sırası ve kullanım takibi. İndeks: (type, id, collection, sort_order) |

`variants` örneği: `{"webp":[480,960,1600],"avif":[480,960,1600]}`. Kaynaktan büyük boyut üretilmez.

### Portföy
| Tablo | Önemli alanlar | İndeksler |
|---|---|---|
| `services` | title, slug, excerpt, body, cover_media_id, status, published_at, sort_order, meta_* | (status, published_at), sort_order |
| `project_categories` | name, slug, description, sort_order, is_active, meta_* | (is_active, sort_order) |
| `projects` | project_category_id, title, slug, subtitle, location, client, year, area_m2, **progress** (ongoing/completed/null), summary, body, cover_media_id, status, published_at, is_featured, sort_order, meta_* | (status, published_at), (is_featured, sort_order), sort_order, progress |
| `project_service` | project_id, service_id | PK (project_id, service_id) |

### İçerik
| Tablo | Önemli alanlar | Not |
|---|---|---|
| `pages` | key (unique), title, slug, content (JSON), body, cover_media_id, status, meta_* | `key` kodda sabit referans: `home`, `about`, `contact`, `kvkk`, `privacy`, `cookies`. Ana sayfa bölümleri `content` JSON'unda |
| `blog_categories` | name, slug, description, sort_order, meta_* | |
| `blog_posts` | blog_category_id, author_id, title, slug, excerpt, body, cover_media_id, status, published_at, is_featured, meta_* | (status, published_at) |
| `client_references` | name, service, description, logo_media_id, project_id, website_url, is_featured, is_active, sort_order | `references` MySQL'de ayrılmış kelime olduğu için tablo adı farklı (TD-020). Model: `Reference` |
| `properties` | title, slug, **listing_type** (sale/rent), **property_type**, **city**, **district**, location, listing_status, summary, body, price, currency, show_price, area_m2, net_area_m2, rooms, **room_count**, bathrooms, cover_media_id, status, published_at, **is_featured**, **is_sample**, sort_order, meta_* | İndeksler: (status, published_at), (listing_type, property_type), (city, district), price, room_count, (is_featured, created_at). `room_count` "3+1"den otomatik türetilir. `is_sample` = lokal örnek ilan. Modül `settings('modules.real_estate')` (varsayılan açık) |
| `faqs` | group, question, answer, sort_order, is_active | |
| `team_members` | name, title, bio, photo_media_id, linkedin_url, sort_order, is_active | |

### Sistem
| Tablo | Önemli alanlar | Not |
|---|---|---|
| `menus` | key (unique: header, legal, …), name | |
| `menu_items` | menu_id, parent_id, label, url (**nullable**: boş = açılır menü başlığı), open_in_new_tab, sort_order, is_active | Alt menü: `parent_id` |
| `contact_messages` | name, email, phone, subject, message, privacy_consent, consented_at, status, **source** (home_cta, contact_page, property), property_id, source_url, ip_address, user_agent, read_at, handled_by, internal_notes | Panelde "Web Formları". `status`: new, in_review, answered, archived. İndeks: (status, created_at), read_at, source |
| `site_settings` | group, key (unique), value (JSON) | `settings('contact.address')` |
| `activity_logs` | user_id, event, subject_type/id, description, properties (JSON: old/new), ip_address, user_agent, created_at | Yazma işlemleri PHASE 04'te bağlanacak |
| `redirects` | from_path (unique, normalize), to_url, status_code, is_active, hits, last_hit_at | Yalnızca 404 durumunda devreye girer (`HandleRedirects`) |

### Laravel standart
`password_reset_tokens`, `sessions`, `cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs`.

## Brief §8'den sapmalar
| Brief | Uygulama | Gerekçe |
|---|---|---|
| `*_translations` tabloları | Yok | Site yalnızca Türkçe (kullanıcı kararı) |
| `project_images`, `property_images` | `mediables` (polimorfik) | Tek galeri yapısı her modelde kullanılabilir; kullanım takibi tek yerde |
| `references` | `client_references` | MySQL ayrılmış kelimesi |
| `menu_translations`, `site_setting_translations` | Yok | Tek dil |
| — | `redirects`, `faqs`, `team_members`, `mediables`, `project_service` eklendi | TD-012 |

## Seed verisi
`php artisan migrate --seed`:
1. `RolesAndPermissionsSeeder`: 3 rol, 15 izin.
2. `AdminUserSeeder`: `.env` → `ADMIN_EMAIL` (şifre boşsa rastgele üretilir ve bir kez gösterilir).
3. `SiteContentSeeder`: mevcut sitedeki içerik ([database/data/site-content.php](../database/data/site-content.php)) ve görseller (`database/seeders/assets/legacy`). Tekrar çalıştırılabilir. `SEED_MEDIA=false` ise görseller atlanır.
