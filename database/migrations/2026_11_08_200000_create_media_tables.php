<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->ulid('uuid')->unique();             // dosya yollarında kullanılır (tahmin edilemez)
            $table->string('disk', 32)->default('public');
            $table->string('original_disk', 32)->default('local');
            $table->string('original_path');            // private diskte, değiştirilmeden saklanır
            $table->string('original_name');
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('size');         // byte
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->json('variants')->nullable();       // {"webp":[640,1024,...],"avif":[...]}
            $table->string('alt')->nullable();
            $table->string('title')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('mime_type');
            $table->index('original_name');
        });

        // Galeri ve kullanım takibi: hangi medya, hangi kayıtta, hangi koleksiyonda, hangi sırada
        Schema::create('mediables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('media_id')->constrained('media')->cascadeOnDelete();
            $table->morphs('mediable');
            $table->string('collection', 50)->default('gallery');
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('caption')->nullable();

            $table->index(['mediable_type', 'mediable_id', 'collection', 'sort_order'], 'mediables_lookup_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mediables');
        Schema::dropIfExists('media');
    }
};
