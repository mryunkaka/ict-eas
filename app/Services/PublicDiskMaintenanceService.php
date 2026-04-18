<?php

namespace App\Services;

use App\Models\AssetHandover;
use App\Models\IctRequest;
use App\Models\IctRequestItem;
use App\Models\IctRequestPoDocument;
use App\Models\IctRequestPpmDocument;
use App\Models\IctRequestPpnkDocument;
use App\Models\IctRequestQuotation;
use App\Models\IctRequestReviewHistory;
use Illuminate\Support\Facades\Storage;

class PublicDiskMaintenanceService
{
    /**
     * @return array<int, string>
     */
    public function managedDirectories(): array
    {
        return [
            'ict-request-items',
            'ict-request-quotations',
            'ict-request-revisions',
            'ict-request-signed',
            'ict-request-ppnk',
            'ict-request-ppm',
            'ict-request-po',
            'ict-request-review-history',
            'ict-handover-documents',
            'ict-handover-reports',
        ];
    }

    /**
     * @return array<string, true>
     */
    public function referencedPaths(): array
    {
        return collect()
            ->merge($this->pluckPaths(IctRequest::class, 'final_signed_pdf_path'))
            ->merge($this->pluckPaths(IctRequest::class, 'revision_attachment_path'))
            ->merge($this->pluckPaths(IctRequestItem::class, 'photo_path'))
            ->merge($this->pluckPaths(IctRequestPpnkDocument::class, 'attachment_path'))
            ->merge($this->pluckPaths(IctRequestPpmDocument::class, 'attachment_path'))
            ->merge($this->pluckPaths(IctRequestPoDocument::class, 'attachment_path'))
            ->merge($this->pluckPaths(IctRequestQuotation::class, 'attachment_path'))
            ->merge($this->pluckPaths(IctRequestReviewHistory::class, 'attachment_path'))
            ->merge($this->pluckPaths(AssetHandover::class, 'serah_terima_path'))
            ->merge($this->pluckPaths(AssetHandover::class, 'surat_jalan_path'))
            ->merge($this->pluckPaths(AssetHandover::class, 'handover_report_path'))
            ->filter()
            ->mapWithKeys(fn (string $path) => [ltrim($path, '/') => true])
            ->all();
    }

    /**
     * @return array<int, string>
     */
    public function orphanPaths(): array
    {
        $disk = Storage::disk('public');
        $referenced = $this->referencedPaths();

        return collect($this->managedDirectories())
            ->flatMap(fn (string $directory) => $disk->allFiles($directory))
            ->reject(fn (string $path) => basename($path) === '.gitignore')
            ->reject(fn (string $path) => isset($referenced[$path]))
            ->values()
            ->all();
    }

    /**
     * @return array{deleted:int, paths: array<int, string>}
     */
    public function deleteOrphans(bool $dryRun): array
    {
        $disk = Storage::disk('public');
        $paths = $this->orphanPaths();

        if ($dryRun || $paths === []) {
            return ['deleted' => 0, 'paths' => $paths];
        }

        $deleted = 0;
        foreach ($paths as $path) {
            if ($disk->delete($path)) {
                $deleted++;
            }
        }

        return ['deleted' => $deleted, 'paths' => $paths];
    }

    /**
     * @return array{total_bytes:int, file_count:int, largest_path: ?string, largest_bytes: int}
     */
    public function publicDiskStats(): array
    {
        $disk = Storage::disk('public');
        $totalBytes = 0;
        $fileCount = 0;
        $largestPath = null;
        $largestBytes = 0;

        foreach ($disk->allFiles() as $path) {
            if (basename($path) === '.gitignore') {
                continue;
            }
            if (! $disk->exists($path)) {
                continue;
            }
            $size = $disk->size($path);
            $totalBytes += $size;
            $fileCount++;
            if ($size > $largestBytes) {
                $largestBytes = $size;
                $largestPath = $path;
            }
        }

        return [
            'total_bytes' => $totalBytes,
            'file_count' => $fileCount,
            'largest_path' => $largestPath,
            'largest_bytes' => $largestBytes,
        ];
    }

    /**
     * @return array<int, string>
     */
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
