<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
| Sayfalar, blog, referanslar, gayrimenkul portföyü, SSS ve ekip.
*/
return new class extends Migration
{
    public function up(): void
    {
        // Statik sayfalar. 'key' kod tarafından sabit referanstır (home, about, contact, kvkk...).
        // 'content' sayfaya özel yapılandırılmış alanlar (ör. ana sayfa hero, süreç adımları).
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('key', 50)->unique();
            $table->string('title');
            $table->string('slug')->nullable()->unique();
            $table->json('content')->nullable();
            $table->longText('body')->nullable();
            $table->foreignId('cover_media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->string('status', 20)->default('published');
            $table->string('meta_title')->nullable();
            $table->string('meta_description', 320)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('blog_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('meta_title')->nullable();
            $table->string('meta_description', 320)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('blog_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blog_category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('excerpt')->nullable();
            $table->longText('body')->nullable();
            $table->foreignId('cover_media_id')->nullable()->constrained('media')->nullOnDelete();
            // draft | published. "Zamanlanmış" = published + gelecekteki published_at (TD-006).
            $table->string('status', 20)->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->string('meta_title')->nullable();
            $table->string('meta_description', 320)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'published_at']);
        });

        // "references" MySQL'de ayrılmış kelime olduğu için tablo adı client_references.
        Schema::create('client_references', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('service')->nullable();      // ör. "Tasarım ve uygulama"
            $table->text('description')->nullable();
            $table->foreignId('logo_media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->string('website_url')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_active', 'sort_order']);
        });

        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('listing_type', 10);                 // sale | rent (config/real_estate.php)
            $table->string('property_type', 50);                // daire, villa, arsa... (config/real_estate.php)
            $table->string('city', 80)->nullable();             // il
            $table->string('district', 80)->nullable();         // ilçe
            $table->string('location')->nullable();             // semt / açık konum metni
            $table->string('listing_status', 20)->default('available'); // available | reserved | sold | rented
            $table->text('summary')->nullable();
            $table->longText('body')->nullable();
            $table->decimal('price', 15, 2)->nullable();
            $table->char('currency', 3)->default('TRY');
            $table->boolean('show_price')->default(true);
            $table->unsignedInteger('area_m2')->nullable();      // brüt
            $table->unsignedInteger('net_area_m2')->nullable();
            $table->string('rooms', 20)->nullable();            // "3+1"
            $table->unsignedTinyInteger('room_count')->nullable(); // filtre için: "3+1" → 3
            $table->unsignedTinyInteger('bathrooms')->nullable();
            $table->foreignId('cover_media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->string('status', 20)->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_sample')->default(false);       // lokal tasarım için örnek ilan (production'a konmaz)
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('meta_title')->nullable();
            $table->string('meta_description', 320)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'published_at']);
            $table->index(['listing_type', 'property_type']);
            $table->index(['city', 'district']);
            $table->index('price');
            $table->index('room_count');
            $table->index(['is_featured', 'created_at']);
            $table->index('listing_status');
        });

        Schema::create('faqs', function (Blueprint $table) {
            $table->id();
            $table->string('group', 50)->default('general');
            $table->string('question');
            $table->text('answer');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['group', 'is_active', 'sort_order']);
        });

        Schema::create('team_members', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('title')->nullable();
            $table->text('bio')->nullable();
            $table->foreignId('photo_media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->string('linkedin_url')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('team_members');
        Schema::dropIfExists('faqs');
        Schema::dropIfExists('properties');
        Schema::dropIfExists('client_references');
        Schema::dropIfExists('blog_posts');
        Schema::dropIfExists('blog_categories');
        Schema::dropIfExists('pages');
    }
};
