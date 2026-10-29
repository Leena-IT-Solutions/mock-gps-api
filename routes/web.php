<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::middleware(['auth'])->group(function () {
    Route::get('/git-info', function () {
        $basePath = base_path();

        $branch = trim(Process::path($basePath)->run('git rev-parse --abbrev-ref HEAD')->output()) ?: 'N/A';
        $commit = trim(Process::path($basePath)->run('git log -1 --format="%h - %s (%cr)"')->output()) ?: 'N/A';
        $remote = trim(Process::path($basePath)->run('git config --get remote.origin.url')->output()) ?: 'No remote configured';
        $status = trim(Process::path($basePath)->run('git status -s')->output());

        return response()->json([
            'status' => true,
            'branch' => $branch,
            'commit' => $commit,
            'remote' => $remote,
            'clean' => $status === '',
            'changes' => $status ?: 'Working tree clean',
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'timezone' => config('app.timezone'),
            'timestamp' => now()->format('d M Y, h:i:s A'),
        ]);
    })->name('git.info');

    Route::post('/git-update', function () {
        $basePath = base_path();
        $logs = [];

        $logs[] = '$ git pull origin ' . (trim(Process::path($basePath)->run('git rev-parse --abbrev-ref HEAD')->output()) ?: 'main');
        $pull = Process::path($basePath)->run('git pull 2>&1');
        $logs[] = trim($pull->output()) ?: 'Already up to date.';

        $logs[] = "\n$ php artisan migrate --force";
        Artisan::call('migrate', ['--force' => true]);
        $logs[] = trim(Artisan::output()) ?: 'Nothing to migrate.';

        $logs[] = "\n$ php artisan optimize:clear";
        Artisan::call('optimize:clear');
        $logs[] = trim(Artisan::output()) ?: 'Caches cleared successfully.';

        return response()->json([
            'status' => $pull->successful(),
            'output' => implode("\n", $logs),
            'timestamp' => now()->format('d M Y, h:i:s A'),
        ]);
    })->name('git.update');
});

require __DIR__.'/auth.php';
