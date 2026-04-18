<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use setasign\Fpdi\Tcpdf\Fpdi;
use Symfony\Component\Process\Process;

class PdfCompressor
{
    public static function maybeCompressPublicDiskFile(string $relativePath): void
    {
        $relativePath = ltrim($relativePath, '/');
        if ($relativePath === '' || strtolower(pathinfo($relativePath, PATHINFO_EXTENSION)) !== 'pdf') {
            return;
        }

        if (! Storage::disk('public')->exists($relativePath)) {
            return;
        }

        $absoluteIn = Storage::disk('public')->path($relativePath);
        $oldSize = Storage::disk('public')->size($relativePath);

        if (self::tryGhostscriptCompress($relativePath, $absoluteIn, $oldSize)) {
            return;
        }

        if (config('services.pdf.php_rewrite.enabled', true)) {
            self::tryPhpRewriteCompress($relativePath, $absoluteIn, $oldSize);
        }
    }

    protected static function tryGhostscriptCompress(string $relativePath, string $absoluteIn, int|float|false $oldSize): bool
    {
        if ($oldSize === false || $oldSize <= 0) {
            return false;
        }

        $binary = self::ghostscriptBinary();
        if ($binary === null) {
            return false;
        }

        $tmpOut = tempnam(sys_get_temp_dir(), 'pdfc');
        if ($tmpOut === false) {
            return false;
        }

        $process = new Process([
            $binary,
            '-sDEVICE=pdfwrite',
            '-dCompatibilityLevel=1.4',
            '-dPDFSETTINGS=/ebook',
            '-dNOPAUSE',
            '-dQUIET',
            '-dBATCH',
            '-sOutputFile='.$tmpOut,
            $absoluteIn,
        ]);
        $process->setTimeout(120);
        $process->run();

        if (! $process->isSuccessful() || ! is_readable($tmpOut)) {
            @unlink($tmpOut);

            return false;
        }

        $newSize = filesize($tmpOut);
        $replaced = $newSize !== false && $newSize > 0 && $newSize < $oldSize;
        if ($replaced) {
            Storage::disk('public')->put($relativePath, (string) file_get_contents($tmpOut));
        }

        @unlink($tmpOut);

        return $replaced;
    }

    protected static function tryPhpRewriteCompress(string $relativePath, string $absoluteIn, int|float|false $oldSize): void
    {
        if ($oldSize === false || $oldSize <= 0) {
            return;
        }

        $maxPages = max(1, (int) config('services.pdf.php_rewrite.max_pages', 50));
        $timeout = max(5, (int) config('services.pdf.php_rewrite.timeout_seconds', 60));
        if (function_exists('set_time_limit')) {
            @set_time_limit($timeout);
        }

        $tmpOut = tempnam(sys_get_temp_dir(), 'pdfphp');
        if ($tmpOut === false) {
            return;
        }

        try {
            $pdf = new Fpdi;
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);

            $pageCount = $pdf->setSourceFile($absoluteIn);
            if ($pageCount > $maxPages) {
                @unlink($tmpOut);

                return;
            }

            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                $tplId = $pdf->importPage($pageNo);
                $size = $pdf->getTemplateSize($tplId);
                $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                $pdf->useTemplate($tplId);
            }

            $pdf->Output($tmpOut, 'F');
        } catch (\Throwable) {
            @unlink($tmpOut);

            return;
        }

        if (! is_readable($tmpOut)) {
            @unlink($tmpOut);

            return;
        }

        $newSize = filesize($tmpOut);
        if ($newSize !== false && $newSize > 0 && $newSize < $oldSize) {
            Storage::disk('public')->put($relativePath, (string) file_get_contents($tmpOut));
        }

        @unlink($tmpOut);
    }

    protected static function ghostscriptBinary(): ?string
    {
        $configured = config('services.ghostscript.binary');
        if (is_string($configured) && $configured !== '' && self::isExecutablePath($configured)) {
            return $configured;
        }

        foreach (['/usr/bin/gs', '/usr/local/bin/gs'] as $path) {
            if (self::isExecutablePath($path)) {
                return $path;
            }
        }

        return null;
    }

    protected static function isExecutablePath(string $path): bool
    {
        if ($path === '') {
            return false;
        }

        if (PHP_OS_FAMILY === 'Windows' && str_ends_with(strtolower($path), '.exe')) {
            return is_file($path);
        }

        return is_executable($path);
    }
}
