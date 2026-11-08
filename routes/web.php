<?php

use App\Http\Controllers\Site\BlogController;
use App\Http\Controllers\Site\ContactController;
use App\Http\Controllers\Site\HomeController;
use App\Http\Controllers\Site\PageController;
use App\Http\Controllers\Site\ProjectController;
use App\Http\Controllers\Site\PropertyController;
use App\Http\Controllers\Site\SeoController;
use App\Http\Controllers\Site\ServiceController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

// SEO (public/robots.txt dosyası yok: ortama göre üretilir)
Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [SeoController::class, 'robots'])->name('robots');

// Kurumsal
Route::get('/hakkimizda', [PageController::class, 'about'])->name('about');
Route::get('/referanslar', [PageController::class, 'references'])->name('references');

// Hizmetler
Route::get('/hizmetler', [ServiceController::class, 'index'])->name('services.index');
Route::get('/hizmetler/{service}', [ServiceController::class, 'show'])->name('services.show');

// Projeler
Route::get('/projeler', [ProjectController::class, 'index'])->name('projects.index');
Route::get('/projeler/kategori/{category}', [ProjectController::class, 'index'])->name('projects.category');
Route::get('/projeler/{project}', [ProjectController::class, 'show'])->name('projects.show');

// Portföy (gayrimenkul) — panelden kapatılabilir modül
Route::middleware('module:real_estate')->group(function () {
    Route::get('/portfoy', [PropertyController::class, 'index'])->name('properties.index');
    Route::get('/satilik', [PropertyController::class, 'index'])->defaults('listingType', 'satilik')->name('properties.sale');
    Route::get('/kiralik', [PropertyController::class, 'index'])->defaults('listingType', 'kiralik')->name('properties.rent');
    Route::get('/kat-karsiligi', [PropertyController::class, 'index'])->defaults('listingType', 'kat-karsiligi')->name('properties.exchange');
    Route::get('/portfoy/{property}', [PropertyController::class, 'show'])->name('properties.show');
});

// Blog
Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/kategori/{category}', [BlogController::class, 'index'])->name('blog.category');
Route::get('/blog/{post}', [BlogController::class, 'show'])->name('blog.show');

// İletişim
Route::get('/iletisim', [ContactController::class, 'show'])->name('contact');
Route::post('/iletisim', [ContactController::class, 'store'])->middleware('throttle:contact')->name('contact.store');

// Hukuki metinler
Route::get('/kvkk', [PageController::class, 'legal'])->defaults('key', 'kvkk')->name('legal.kvkk');
Route::get('/gizlilik-politikasi', [PageController::class, 'legal'])->defaults('key', 'privacy')->name('legal.privacy');
Route::get('/cerez-politikasi', [PageController::class, 'legal'])->defaults('key', 'cookies')->name('legal.cookies');
