<?php

use App\Http\Controllers\PublicController;
use App\Models\ContactMessage;
use App\Modules\ModuleRegistry;
use App\Support\PublicContent;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders every public marketing page in both languages', function () {
    foreach (['en', 'bn'] as $locale) {
        foreach (['/', '/features', '/solutions', '/modules', '/pricing', '/demo', '/how-it-works', '/compare', '/docs', '/faq', '/contact', '/blog', '/privacy', '/terms', '/changelog', '/status'] as $path) {
            $this->get($path.'?lang='.$locale)->assertOk()->assertSee('lang="'.$locale.'"', false);
        }
    }
});

it('renders all registry modules and presets and lists them in discovery', function () {
    $sitemap = $this->get('/sitemap.xml')->assertOk();
    $llms = $this->get('/llms.txt')->assertOk();
    foreach (app(ModuleRegistry::class)->all() as $key => $module) {
        $this->get('/features/'.$key)->assertOk()->assertSee($module['name']['en']);
        $sitemap->assertSee('/features/'.$key, false);
        $llms->assertSee('/features/'.$key, false);
    }
    foreach (app(ModuleRegistry::class)->presets() as $key => $preset) {
        $this->get('/solutions/'.$key)->assertOk();
    }
});

it('persists contact interest and rejects honeypots', function () {
    $data = ['name' => 'Sample owner', 'email' => 'sample@example.test', 'plan' => 'general', 'message' => 'Please share more about the demo.'];
    $this->post('/contact', $data)->assertRedirect();
    $this->assertDatabaseHas('contact_messages', ['email' => 'sample@example.test']);
    $this->post('/contact', $data + ['website' => 'spam'])->assertSessionHasErrors('website');
    expect(ContactMessage::query()->count())->toBe(1);
});

it('does not accept unknown modules, presets or documentation paths', function () {
    $this->get('/features/not-a-module')->assertNotFound();
    $this->get('/solutions/unknown')->assertNotFound();
    $this->get('/docs/env')->assertNotFound();
});

it('disables demo login in production or without DEMO_MODE', function () {
    config(['demo.enabled' => false]);
    $this->post('/demo/noor', ['role' => 'owner'])->assertNotFound();
});

it('renders every documentation page and article from the allowlist', function () {
    foreach (array_keys(PublicController::documents()) as $slug) {
        $this->get('/docs/'.$slug)->assertOk();
    }
    foreach (array_keys(PublicContent::articles()) as $slug) {
        $this->get('/blog/'.$slug)->assertOk();
    }
});

it('keeps the README feature matrix and module discovery in parity', function () {
    $this->artisan('docs:features', ['--check' => true])->assertSuccessful();
    $readme = file_get_contents(base_path('README.md'));
    foreach (app(ModuleRegistry::class)->all() as $key => $module) {
        expect($readme)->toContain('`'.$key.'`');
    }
    foreach (['repo-cover.svg', 'homepage.png', 'dashboard.png', 'storefront.png', 'demo.png', 'workflow.svg'] as $file) {
        expect(is_file(base_path('docs/images/'.$file)))->toBeTrue();
    }
});

it('keeps demo sign-in and seeding disabled in production', function () {
    config(['demo.enabled' => true]);
    app()->detectEnvironment(fn () => 'production');
    $this->withSession(['_token' => 'production-test-token'])->post('/demo/sample', ['role' => 'owner', '_token' => 'production-test-token'])->assertNotFound();
    expect(fn () => (new DemoSeeder)->run())->toThrow(RuntimeException::class);
});
