<?php

use App\Http\Controllers\DemoController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\StorefrontController;
use App\Http\Controllers\WorkspaceController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicController::class, 'home'])->name('home');
Route::get('/features', [PublicController::class, 'features'])->name('features');
Route::get('/features/{key}', [PublicController::class, 'feature'])->name('feature');
Route::get('/solutions', [PublicController::class, 'solutions'])->name('solutions');
Route::get('/solutions/{key}', [PublicController::class, 'solution'])->name('solution');
Route::get('/modules', [PublicController::class, 'modules'])->name('modules');
Route::get('/pricing', [PublicController::class, 'pricing'])->name('pricing');
Route::get('/demo', [PublicController::class, 'demo'])->name('demo');
Route::post('/demo/{slug}', [DemoController::class, 'enter'])->middleware('throttle:20,1')->name('demo.enter');
Route::post('/logout', [DemoController::class, 'leave'])->name('logout');
foreach (['how-it-works' => 'how', 'compare' => 'compare', 'docs' => 'docs', 'faq' => 'faq', 'contact' => 'contact', 'blog' => 'blog', 'privacy' => 'privacy', 'terms' => 'terms', 'changelog' => 'changelog', 'status' => 'status'] as $path => $page) {
    Route::get('/'.$path, [PublicController::class, 'content'])->defaults('page', $page)->name($page);
}
Route::get('/docs/{slug}', [PublicController::class, 'document'])->name('document');
Route::get('/blog/{slug}', [PublicController::class, 'article'])->name('article');
Route::post('/contact', [PublicController::class, 'contact'])->middleware('throttle:5,1')->name('contact.store');
Route::get('/sitemap.xml', [PublicController::class, 'sitemap'])->name('sitemap');
Route::get('/llms.txt', [PublicController::class, 'llms'])->name('llms');
Route::get('/robots.txt', fn () => response("User-agent: *\nAllow: /\nDisallow: /app/\nDisallow: /s/*/receipt/\nSitemap: ".url('/sitemap.xml')."\n", 200, ['Content-Type' => 'text/plain']));
Route::get('/s/{slug}', [StorefrontController::class, 'index'])->name('storefront');
Route::get('/s/{slug}/product/{productSlug}', [StorefrontController::class, 'product'])->name('store.product');
Route::post('/s/{slug}/product/{productSlug}', [StorefrontController::class, 'checkout'])->middleware('throttle:10,1')->name('store.checkout');
Route::get('/s/{slug}/receipt/{number}', [StorefrontController::class, 'receipt'])->name('store.receipt');
Route::match(['get', 'post'], '/s/{slug}/track', [StorefrontController::class, 'track'])->middleware('throttle:10,1')->name('store.track');
Route::get('/app/{slug}/{section?}', [WorkspaceController::class, 'index'])->name('workspace');
Route::post('/app/{slug}/catalog/create', [WorkspaceController::class, 'createProduct'])->name('workspace.product');
Route::post('/app/{slug}/inventory/{id}', [WorkspaceController::class, 'stock'])->name('workspace.stock');
Route::post('/app/{slug}/orders/{id}/status', [WorkspaceController::class, 'status'])->name('workspace.status');
Route::post('/app/{slug}/payments/{id}', [WorkspaceController::class, 'payment'])->name('workspace.payment');
Route::post('/app/{slug}/pos/sale', [WorkspaceController::class, 'pos'])->middleware('throttle:30,1')->name('workspace.sale');
Route::get('/app/{slug}/orders/{id}/receipt', [WorkspaceController::class, 'receipt'])->name('workspace.receipt');
Route::post('/app/{slug}/modules/{key}', [WorkspaceController::class, 'toggle'])->name('workspace.toggle');
Route::get('/app/{slug}/reports/export/csv', [WorkspaceController::class, 'export'])->name('workspace.export');

Route::get('/app/{slug}/orders/{id}/pdf',[WorkspaceController::class, 'pdf'])->name('workspace.pdf');
