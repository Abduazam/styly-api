<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Throwable;

final class ClearCacheCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clear:all {--storage=* : Relative directories on the public disk to purge}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will clear all caches, views, routes and configs.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->clearPublicStorage();

        $this->call('cache:clear');
        $this->call('view:clear');
        $this->call('route:clear');
        $this->call('config:clear');

        Cache::store('redis')->flush();

        $this->info('All clears have been ran.');
    }

    protected function clearPublicStorage(): void
    {
        $disk = Storage::disk('public');

        foreach ($disk->allDirectories() as $directory) {
            Storage::disk('public')->deleteDirectory($directory);
        }
    }
}
