<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use ImranDev\UnicodePdf\Facades\UnicodePdf;
use ImranDev\UniversalSlug\UniversalSlug;
use ImranDevBd\AiHub\Facades\AIHub;
use ImranDevBd\AttendanceHub\Facades\AttendanceHub;
use ImranDevBd\AttendanceHub\Models\AttendanceLog;

uses(RefreshDatabase::class);

it('loads every required package provider', function () {
    foreach ([
        'Drutogoti\\DrutogotiServiceProvider',
        'ImranDevBD\\LaravelAdminPanel\\AdminPanelServiceProvider',
        'ImranDevBd\\AiHub\\AiHubServiceProvider',
        'ImranDevBd\\AiCatalog\\AiCatalogServiceProvider',
        'ImranDevBd\\AttendanceHub\\AttendanceHubServiceProvider',
        'ImranDev\\UnicodePdf\\UnicodePdfServiceProvider',
        'ImranDev\\UniversalSlug\\UniversalSlugServiceProvider',
    ] as $provider) {
        expect(app()->providerIsLoaded($provider))->toBeTrue();
    }
});

it('generates Bangla slugs without whitespace', function () {
    expect(UniversalSlug::generate('জামদানি কটন থ্রি-পিস'))->toBe('জামদানি-কটন-থ্রি-পিস');
});

it('uses the actual AI fake without external requests', function () {
    AIHub::fake('নূর বুটিক');
    $response = AIHub::prompt('Name a boutique')->forJob('shop:1:spike')->send();
    expect($response->content)->toBe('নূর বুটিক');
    Http::assertNothingSent();
});

it('installs the attendance fake', function () {
    AttendanceHub::fake();
    expect(AttendanceHub::isFake())->toBeTrue();
});

it('simulates attendance through package normalization and persistence', function () {
    $this->artisan('attendance:simulate', ['--employees' => 2, '--days' => 14])->assertSuccessful();
    expect(AttendanceLog::query()->count())->toBeGreaterThan(0);
    Http::assertNothingSent();
});

it('generates a Bengali PDF through the native engine', function () {
    $pdf = UnicodePdf::preset('bengali')->loadHtml('<h1>শিক্ষার্থী কৃষ্ণ</h1><p>৳ ১,২৫০</p>')->output();
    expect($pdf)->toStartWith('%PDF-')->and(strlen($pdf))->toBeGreaterThan(1000);
});

it('supports the PDF testing fake', function () {
    $fake = UnicodePdf::fake();
    UnicodePdf::loadHtml('<p>শিক্ষার্থী</p>')->output();
    $fake->assertGenerated();
});

it('denies unconfigured package endpoints', function () {
    $this->get('/insights')->assertForbidden();
    $this->get('/ai-hub')->assertForbidden();
    $this->get('/ai-catalog')->assertForbidden();
    $this->post('/api/attendance/virtual/check-in')->assertForbidden();
    $this->post('/api/attendance/webhook-bridge')->assertForbidden();
    $this->get('/iclock/cdata')->assertForbidden();
});

it('documents rather than invents the absent upstream doctor command', function () {
    expect(array_key_exists('admin-panel:doctor', Artisan::all()))->toBeFalse();
    expect(file_get_contents(base_path('docs/KNOWN-GAPS.md')))->toContain('admin-panel:doctor');
});
