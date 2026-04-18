<?php

namespace App\Console\Commands;

use App\Services\PublicDiskMaintenanceService;
use Illuminate\Console\Command;

class CleanOrphanedPublicFiles extends Command
{
    protected $signature = 'storage:clean-orphaned-public-files {--dry-run : Tampilkan file orphan tanpa menghapus}';

    protected $description = 'Hapus file orphan di storage/app/public yang tidak lagi direferensikan database.';

    public function handle(PublicDiskMaintenanceService $maintenance): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $result = $maintenance->deleteOrphans($dryRun);
        $paths = $result['paths'];

        if ($paths === []) {
            $this->info('Tidak ada file orphan di folder terkelola.');

            return self::SUCCESS;
        }

        if ($dryRun) {
            $this->warn('Dry run: file orphan terdeteksi tanpa dihapus.');
            $this->table(['Path'], collect($paths)->map(fn (string $path) => [$path])->all());
            $this->line('Total orphan: '.count($paths));

            return self::SUCCESS;
        }

        foreach ($paths as $path) {
            $this->line('Deleted: '.$path);
        }

        $this->info("Selesai. {$result['deleted']} file orphan dihapus dari storage/app/public.");

        return self::SUCCESS;
    }
}
