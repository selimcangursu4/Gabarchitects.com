<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
| Menüler, iletişim talepleri, site ayarları, aktivite logları, 301 yönlendirmeleri.
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->string('key', 50)->unique();   // header | footer | legal
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('menu_items')->cascadeOnDelete();
            $table->string('label');
            $table->string('url')->nullable();     // site içi yol (/projeler) veya tam URL; boş = yalnızca açılır menü başlığı
            $table->boolean('open_in_new_tab')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['menu_id', 'parent_id', 'sort_order']);
        });

        Schema::create('contact_messages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('phone', 40)->nullable();
            $table->string('subject')->nullable();
            $table->text('message');
            $table->boolean('privacy_consent')->default(false);
            $table->timestamp('consented_at')->nullable();
            $table->string('status', 20)->default('new');  // new | in_review | answered | archived
            $table->string('source', 30)->default('contact_page'); // home_cta | contact_page | property
            $table->foreignId('property_id')->nullable()->constrained()->nullOnDelete();
            $table->string('source_url')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamp('read_at')->nullable();
            $table->foreignId('handled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('internal_notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'created_at']);
            $table->index('read_at');
            $table->index('source');
        });

        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('group', 50)->default('general');
            $table->string('key', 100)->unique();
            $table->json('value')->nullable();
            $table->timestamps();

            $table->index('group');
        });

        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('event', 50);           // created | updated | deleted | restored | login ...
            $table->nullableMorphs('subject');
            $table->string('description')->nullable();
            $table->json('properties')->nullable(); // {"old":{...},"new":{...}}
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('created_at');
            $table->index('event');
        });

        Schema::create('redirects', function (Blueprint $table) {
            $table->id();
            $table->string('from_path')->unique();  // baştaki / ile, sorgu dizesi olmadan
            $table->string('to_url');
            $table->unsignedSmallInteger('status_code')->default(301);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('hits')->default(0);
            $table->timestamp('last_hit_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('redirects');
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('site_settings');
        Schema::dropIfExists('contact_messages');
        Schema::dropIfExists('menu_items');
        Schema::dropIfExists('menus');
    }
};
