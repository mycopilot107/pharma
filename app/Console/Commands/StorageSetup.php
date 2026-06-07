<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class StorageSetup extends Command
{
    protected $signature = 'storage:setup';

    protected $description = 'Create required storage directories and the public symlink';

    public function handle(): int
    {
        $this->ensureDirectories();
        $this->ensureSymlink();

        $this->info('Storage setup complete.');

        return self::SUCCESS;
    }

    private function ensureDirectories(): void
    {
        $dirs = [
            storage_path('app/public/visit-photos'),
            storage_path('app/public/expense-receipts'),
            storage_path('app/private'),
            storage_path('logs'),
            storage_path('framework/cache'),
            storage_path('framework/sessions'),
            storage_path('framework/views'),
        ];

        foreach ($dirs as $dir) {
            if (! File::isDirectory($dir)) {
                File::makeDirectory($dir, 0775, recursive: true);
                $this->line("Created: $dir");
            }
        }
    }

    private function ensureSymlink(): void
    {
        $link   = public_path('storage');
        $target = storage_path('app/public');

        if (is_link($link)) {
            $this->line('Symlink already exists: public/storage');
            return;
        }

        if (File::isDirectory($link)) {
            $this->warn('public/storage is a real directory — skipping symlink creation. Delete it manually and re-run if needed.');
            return;
        }

        symlink($target, $link);
        $this->info("Symlink created: public/storage → storage/app/public");
    }
}
