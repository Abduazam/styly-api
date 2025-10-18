<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CopyDefaultMedia extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'media:copy-defaults {--force : Force overwrite existing files}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Copy all files and folders from resources/media/default to public/storage';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $sourcePath = resource_path('media/default');
        $destinationPath = public_path('storage');

        // Check if source directory exists
        if (!File::exists($sourcePath)) {
            $this->error("Source directory does not exist: {$sourcePath}");
            return 1;
        }

        // Create destination directory if it doesn't exist
        if (!File::exists($destinationPath)) {
            File::makeDirectory($destinationPath, 0755, true);
            $this->info("Created destination directory: {$destinationPath}");
        }

        $force = $this->option('force');
        $copiedCount = 0;
        $skippedCount = 0;

        $this->info("Copying files from {$sourcePath} to {$destinationPath}...");

        // Copy all files and directories recursively
        $this->copyDirectory($sourcePath, $destinationPath, $force, $copiedCount, $skippedCount);

        $this->info("Copy operation completed!");
        $this->info("Files copied: {$copiedCount}");
        $this->info("Files skipped: {$skippedCount}");

        return 0;
    }

    /**
     * Recursively copy directory contents.
     */
    protected function copyDirectory(string $source, string $destination, bool $force, int &$copiedCount, int &$skippedCount): void
    {
        if (!File::exists($source)) {
            return;
        }

        // Get all items in the source directory
        $items = File::allFiles($source);
        $directories = File::directories($source);

        // Copy all files
        foreach ($items as $file) {
            $relativePath = str_replace($source . DIRECTORY_SEPARATOR, '', $file->getPathname());
            $destPath = $destination . DIRECTORY_SEPARATOR . $relativePath;
            $destDir = dirname($destPath);

            // Create destination directory if it doesn't exist
            if (!File::exists($destDir)) {
                File::makeDirectory($destDir, 0755, true);
            }

            // Check if file already exists
            if (File::exists($destPath) && !$force) {
                $this->line("Skipping existing file: {$relativePath}");
                $skippedCount++;
                continue;
            }

            // Copy the file
            if (File::copy($file->getPathname(), $destPath)) {
                $this->line("Copied: {$relativePath}");
                $copiedCount++;
            } else {
                $this->error("Failed to copy: {$relativePath}");
            }
        }

        // Copy subdirectories recursively
        foreach ($directories as $directory) {
            $dirName = basename($directory);
            $newDestination = $destination . DIRECTORY_SEPARATOR . $dirName;
            
            if (!File::exists($newDestination)) {
                File::makeDirectory($newDestination, 0755, true);
            }

            $this->copyDirectory($directory, $newDestination, $force, $copiedCount, $skippedCount);
        }
    }
}