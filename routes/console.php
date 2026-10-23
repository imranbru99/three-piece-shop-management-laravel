<?php

use App\Modules\ModuleRegistry;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('demo:seed', function () {
    if (app()->isProduction() || ! config('demo.enabled')) {
        $this->error('Enable DEMO_MODE in a non-production environment.');

        return 1;
    }
    $this->call('db:seed', ['--class' => DemoSeeder::class]);

    return 0;
})->purpose('Create fictional sample shops without replacing existing data');

Artisan::command('docs:features {--check}', function () {
    $registry = app(ModuleRegistry::class);
    $table = "| Module | Features | Demo status |\n| --- | --- | --- |\n";
    foreach ($registry->all() as $module) {
        $table .= '| `'.$module['key'].'` · '.$module['name']['en'].' | '.implode('; ', $module['features']).' | '.$module['status']." |\n";
    }
    $path = base_path('README.md');
    $readme = file_get_contents($path);
    $start = '<!-- FEATURES:START -->';
    $end = '<!-- FEATURES:END -->';
    if (! str_contains($readme, $start) || ! str_contains($readme, $end)) {
        $this->error('README feature markers are required.');

        return 1;
    }
    $replacement = $start."\n".$table.$end;
    $updated = preg_replace('/'.preg_quote($start, '/').'.*?'.preg_quote($end, '/').'/s', $replacement, $readme);
    if ($this->option('check')) {
        if ($updated !== $readme) {
            $this->error('Feature matrix is stale.');

            return 1;
        }
    } else {
        file_put_contents($path, $updated);
    }
    $this->info('Feature matrix matches the module registry.');

    return 0;
})->purpose('Generate the README feature matrix from the public module registry');
