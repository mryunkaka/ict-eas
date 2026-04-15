<?php

namespace App\Console\Commands;

use App\Models\IctRequest;
use App\Models\IctRequestItem;
use App\Models\IctRequestPpmDocument;
use App\Models\IctRequestPpnkDocument;
use App\Models\IctRequestQuotation;
use App\Models\IctRequestReviewHistory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CleanOrphanedPublicFiles extends Command
{
    protected array $managedDirectories = [
        'ict-request-items',
        'ict-request-quotations',
        'ict-request-revisions',
        'ict-request-signed',
        'ict-request-ppnk',
        'ict-request-ppm',
        'ict-request-review-history',
    ];

    protected $signature = 'storage:clean-orphaned-public-files {--dry-run : Tampilkan file orphan tanpa menghapus}';

    protected $description = 'Hapus file orphan di storage/app/public yang tidak lagi direferensikan database.';

    public function handle(): int
    {
        $disk = Storage::disk('public');
        $referencedPaths = $this->referencedPaths();
        $allFiles = collect($this->managedDirectories)
            ->flatMap(fn (string $directory) => $disk->allFiles($directory))
            ->reject(fn (string $path) => basename($path) === '.gitignore')
            ->values();

        $orphans = $allFiles
            ->reject(fn (string $path) => isset($referencedPaths[$path]))
            ->values();

        if ($orphans->isEmpty()) {
            $this->info('Tidak ada file orphan di storage/app/public.');

            return self::SUCCESS;
        }

        if ($this->option('dry-run')) {
            $this->warn('Dry run: file orphan terdeteksi tanpa dihapus.');
            $this->table(['Path'], $orphans->map(fn (string $path) => [$path])->all());
            $this->line('Total orphan: '.$orphans->count());

            return self::SUCCESS;
        }

        $deleted = 0;

        foreach ($orphans as $path) {
            if ($disk->delete($path)) {
                $deleted += 1;
                $this->line('Deleted: '.$path);
            }
        }

        $this->info("Selesai. {$deleted} file orphan dihapus dari storage/app/public.");

        return self::SUCCESS;
    }

    protected function referencedPaths(): array
    {
        return collect()
            ->merge($this->pluckPaths(IctRequest::class, 'final_signed_pdf_path'))
            ->merge($this->pluckPaths(IctRequest::class, 'revision_attachment_path'))
            ->merge($this->pluckPaths(IctRequestItem::class, 'photo_path'))
            ->merge($this->pluckPaths(IctRequestPpnkDocument::class, 'attachment_path'))
            ->merge($this->pluckPaths(IctRequestPpmDocument::class, 'attachment_path'))
            ->merge($this->pluckPaths(IctRequestQuotation::class, 'attachment_path'))
            ->merge($this->pluckPaths(IctRequestReviewHistory::class, 'attachment_path'))
            ->filter()
            ->mapWithKeys(fn (string $path) => [ltrim($path, '/') => true])
            ->all();
    }

    protected function pluckPaths(string $modelClass, string $column): array
    {
        return $modelClass::query()
            ->whereNotNull($column)
            ->pluck($column)
            ->map(fn ($path) => (string) $path)
            ->filter()
            ->values()
            ->all();
    }
}
