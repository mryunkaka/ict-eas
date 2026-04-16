<?php

namespace App\Console\Commands;

use App\Models\IctRequest;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Throwable;

class GenerateAiDocs extends Command
{
    protected $signature = 'docs:generate';

    protected $description = 'Generate markdown documentation in docs/ai-handbook for faster AI navigation.';

    public function handle(): int
    {
        $baseDir = base_path('docs/ai-handbook');
        $generatedDir = $baseDir.DIRECTORY_SEPARATOR.'generated';
        $generatedModulesDir = $baseDir.DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.'generated';

        File::ensureDirectoryExists($generatedDir);
        File::ensureDirectoryExists($baseDir.DIRECTORY_SEPARATOR.'modules');
        File::ensureDirectoryExists($generatedModulesDir);

        $generatedAt = now()->format('Y-m-d H:i:s');

        $this->writeFile($generatedDir.'/ROUTES.md', $this->renderRoutesDoc($generatedAt));
        $this->writeFile($generatedDir.'/VIEWS.md', $this->renderViewsDoc($generatedAt));
        $this->writeFile($generatedDir.'/STRUCTURE.md', $this->renderStructureDoc($generatedAt));
        $this->writeFile($generatedDir.'/MODELS.md', $this->renderModelsDoc($generatedAt));
        $this->writeFile($generatedDir.'/MIGRATIONS.md', $this->renderMigrationsDoc($generatedAt));
        $this->writeFile($generatedDir.'/TESTS.md', $this->renderTestsDoc($generatedAt));
        $this->writeFile($generatedDir.'/ICT_REQUEST_STATUSES.md', $this->renderIctStatusesDoc($generatedAt));
        $this->generateModuleDocs($generatedAt, $generatedModulesDir);

        if (! $this->output->isQuiet()) {
            $this->info('AI docs generated in: '.$generatedDir);
        }

        return self::SUCCESS;
    }

    protected function writeFile(string $path, string $contents): void
    {
        File::put($path, rtrim($contents).PHP_EOL);
    }

    protected function renderHeader(string $title, string $generatedAt): string
    {
        return "# {$title}\n\nGenerated at: `{$generatedAt}`\n";
    }

    protected function renderRoutesDoc(string $generatedAt): string
    {
        $header = $this->renderHeader('Routes', $generatedAt);

        $routes = $this->getRoutesJson($header);
        if (is_string($routes)) {
            return $routes;
        }

        if (! is_array($routes)) {
            return $header."\nNo routes found.\n";
        }

        $rows = collect($routes)
            ->map(function (array $route): array {
                $methods = is_array($route['method'] ?? null)
                    ? implode('|', $route['method'])
                    : (string) ($route['method'] ?? '');

                return [
                    'method' => $methods,
                    'uri' => (string) ($route['uri'] ?? ''),
                    'name' => (string) ($route['name'] ?? ''),
                    'action' => (string) ($route['action'] ?? ''),
                    'middleware' => is_array($route['middleware'] ?? null)
                        ? implode(', ', $route['middleware'])
                        : (string) ($route['middleware'] ?? ''),
                ];
            })
            ->sortBy('uri')
            ->values();

        $importantPrefixes = [
            'forms/ict-requests',
            'forms/assets',
            'approvals',
            'reports',
            'dashboard',
            'inventory',
            'tools',
        ];

        $doc = $header."\n".
            "This file is auto-generated. Use it to quickly map URLs/route-names to controllers.\n\n";

        foreach ($importantPrefixes as $prefix) {
            $subset = $rows->filter(fn (array $r) => Str::startsWith($r['uri'], $prefix))->values();
            if ($subset->isEmpty()) {
                continue;
            }

            $doc .= "\n## `{$prefix}`\n\n";
            $doc .= "| Method | URI | Name | Action |\n|---|---|---|---|\n";
            foreach ($subset as $r) {
                $doc .= '| `'.($r['method'] ?: '-').'` | `'.$r['uri'].'` | `'.($r['name'] ?: '-').'` | `'.($r['action'] ?: '-').'` |'."\n";
            }
        }

        $doc .= "\n## All routes (compact)\n\n";
        $doc .= "| Method | URI | Name | Action |\n|---|---|---|---|\n";
        foreach ($rows as $r) {
            $doc .= '| `'.($r['method'] ?: '-').'` | `'.$r['uri'].'` | `'.($r['name'] ?: '-').'` | `'.($r['action'] ?: '-').'` |'."\n";
        }

        return $doc;
    }

    /**
     * @return array<int, array<string, mixed>>|string
     */
    protected function getRoutesJson(string $headerForError): array|string
    {
        try {
            Artisan::call('route:list', ['--json' => true]);
            $output = trim((string) Artisan::output());
            $routes = json_decode($output, true, flags: JSON_THROW_ON_ERROR);
        } catch (Throwable $e) {
            return $headerForError."\nFailed to generate routes via `route:list --json`.\n\n".
                'Error: `'.Str::of($e->getMessage())->limit(200)->toString()."`\n";
        }

        return is_array($routes) ? $routes : [];
    }

    protected function renderViewsDoc(string $generatedAt): string
    {
        $header = $this->renderHeader('Views', $generatedAt);
        $viewsDir = resource_path('views');

        $files = collect(File::allFiles($viewsDir))
            ->filter(fn ($f) => Str::endsWith($f->getFilename(), '.blade.php'))
            ->map(fn ($f) => Str::replace('\\', '/', $f->getRelativePathname()))
            ->sort()
            ->values();

        $doc = $header."\n".
            "Blade views in `resources/views`.\n\n".
            "## Key entry pages\n\n".
            "- `dashboard.blade.php`\n".
            "- `forms/ict-requests/index.blade.php`\n".
            "- `forms/ict-requests/create.blade.php`\n".
            "- `approvals/index.blade.php`\n\n".
            "## All blade files\n\n";

        foreach ($files as $path) {
            $doc .= "- `resources/views/{$path}`\n";
        }

        return $doc;
    }

    protected function renderStructureDoc(string $generatedAt): string
    {
        $header = $this->renderHeader('Repository Structure', $generatedAt);

        $top = collect(File::directories(base_path()))
            ->map(fn (string $dir) => basename($dir))
            ->reject(fn (string $name) => in_array($name, ['node_modules', 'vendor', '.git'], true))
            ->sort()
            ->values();

        $doc = $header."\n".
            "High-level folders (excluding heavy vendor dirs).\n\n";

        foreach ($top as $name) {
            $doc .= "- `{$name}/`\n";
        }

        $doc .= "\n## Common locations\n\n".
            "- Routes: `routes/web.php`\n".
            "- Controllers: `app/Http/Controllers/**`\n".
            "- Models: `app/Models/**`\n".
            "- Views: `resources/views/**`\n".
            "- DB: `database/migrations/**`\n".
            "- Tests: `tests/**`\n";

        return $doc;
    }

    protected function renderModelsDoc(string $generatedAt): string
    {
        $header = $this->renderHeader('Models', $generatedAt);
        $modelsDir = app_path('Models');

        $files = collect(File::allFiles($modelsDir))
            ->filter(fn ($f) => Str::endsWith($f->getFilename(), '.php'))
            ->map(fn ($f) => Str::replace('\\', '/', $f->getRelativePathname()))
            ->sort()
            ->values();

        $doc = $header."\n".
            "Eloquent models in `app/Models`.\n\n";

        foreach ($files as $path) {
            $doc .= "- `app/Models/{$path}`\n";
        }

        return $doc;
    }

    protected function renderMigrationsDoc(string $generatedAt): string
    {
        $header = $this->renderHeader('Migrations', $generatedAt);
        $migrationsDir = database_path('migrations');

        $files = collect(File::files($migrationsDir))
            ->map(fn ($f) => $f->getFilename())
            ->sort()
            ->values();

        $doc = $header."\n".
            "Database migrations in `database/migrations`.\n\n";

        foreach ($files as $name) {
            $doc .= "- `database/migrations/{$name}`\n";
        }

        return $doc;
    }

    protected function renderTestsDoc(string $generatedAt): string
    {
        $header = $this->renderHeader('Tests', $generatedAt);
        $testsDir = base_path('tests');

        $files = collect(File::allFiles($testsDir))
            ->filter(fn ($f) => Str::endsWith($f->getFilename(), '.php'))
            ->map(fn ($f) => Str::replace('\\', '/', $f->getRelativePathname()))
            ->sort()
            ->values();

        $doc = $header."\n".
            "PHPUnit tests in `tests`.\n\n";

        foreach ($files as $path) {
            $doc .= "- `tests/{$path}`\n";
        }

        return $doc;
    }

    protected function renderIctStatusesDoc(string $generatedAt): string
    {
        $header = $this->renderHeader('ICT Request Status Labels', $generatedAt);

        /** @var array<string, string> $labels */
        $labels = IctRequest::STATUS_LABELS;

        $rows = collect($labels)
            ->map(fn (string $label, string $status) => ['status' => $status, 'label' => $label])
            ->sortBy('status')
            ->values();

        $doc = $header."\n".
            "Source of truth: `App\\\\Models\\\\IctRequest::STATUS_LABELS`.\n\n".
            "| Status | Label |\n|---|---|\n";

        foreach ($rows as $row) {
            $doc .= '| `'.$row['status'].'` | '.$row['label']." |\n";
        }

        $doc .= "\nNotes:\n\n".
            "- Special display label: if status is `checked_by_asmen` AND `print_count > 0` AND `final_signed_pdf_path` is empty, UI label becomes **Progress TTD**.\n";

        return $doc;
    }

    protected function generateModuleDocs(string $generatedAt, string $generatedModulesDir): void
    {
        $header = $this->renderHeader('Modules (Generated)', $generatedAt);
        $routes = $this->getRoutesJson($header);
        if (is_string($routes)) {
            $this->writeFile($generatedModulesDir.'/README.md', $routes);

            return;
        }

        $rows = collect($routes)
            ->map(function (array $route): array {
                $methods = is_array($route['method'] ?? null)
                    ? implode('|', $route['method'])
                    : (string) ($route['method'] ?? '');

                $uri = (string) ($route['uri'] ?? '');

                return [
                    'method' => $methods,
                    'uri' => $uri,
                    'name' => (string) ($route['name'] ?? ''),
                    'action' => (string) ($route['action'] ?? ''),
                    'module_key' => $this->inferModuleKey($uri),
                ];
            })
            ->filter(fn (array $r) => $r['module_key'] !== null)
            ->values();

        /** @var Collection<string, Collection<int, array{method:string,uri:string,name:string,action:string,module_key:string}>> $grouped */
        $grouped = $rows
            ->groupBy('module_key')
            ->sortKeys();

        $index = $this->renderHeader('Modules (Generated)', $generatedAt)."\n".
            "Auto-generated module handbooks grouped by route prefixes.\n\n".
            "Folder: `docs/ai-handbook/modules/generated/`\n\n";

        $isMenuModule = fn (string $moduleKey): bool => Str::startsWith($moduleKey, 'forms/')
            || in_array($moduleKey, ['dashboard', 'approvals', 'inventory', 'reports', 'tools', 'profile'], true);

        $menuModules = $grouped->keys()->filter($isMenuModule)->values();
        $otherModules = $grouped->keys()->reject($isMenuModule)->values();

        $index .= "## Menu modules\n\n";

        foreach ($menuModules as $moduleKey) {
            $moduleRoutes = $grouped->get($moduleKey, collect());
            $fileName = $this->moduleFileName($moduleKey);
            $title = $this->moduleTitle($moduleKey);
            $path = $generatedModulesDir.DIRECTORY_SEPARATOR.$fileName;

            $this->writeFile($path, $this->renderModuleDoc($title, $generatedAt, $moduleKey, $moduleRoutes));
            $index .= "- `docs/ai-handbook/modules/generated/{$fileName}`\n";
        }

        if ($otherModules->isNotEmpty()) {
            $index .= "\n## Other routes (auth/system)\n\n";

            foreach ($otherModules as $moduleKey) {
                $moduleRoutes = $grouped->get($moduleKey, collect());
                $fileName = $this->moduleFileName($moduleKey);
                $title = $this->moduleTitle($moduleKey);
                $path = $generatedModulesDir.DIRECTORY_SEPARATOR.$fileName;

                $this->writeFile($path, $this->renderModuleDoc($title, $generatedAt, $moduleKey, $moduleRoutes));
                $index .= "- `docs/ai-handbook/modules/generated/{$fileName}`\n";
            }
        }

        $index .= "\nNotes:\n\n".
            "- Manual/curated module docs live in `docs/ai-handbook/modules/`.\n".
            "- Generated docs are overwritten on every `php artisan docs:generate`.\n";

        $this->writeFile($generatedModulesDir.'/README.md', $index);
    }

    protected function inferModuleKey(string $uri): ?string
    {
        $uri = trim($uri, '/');
        if ($uri === '') {
            return 'root';
        }

        if (Str::startsWith($uri, 'forms/')) {
            $parts = explode('/', $uri);

            return isset($parts[1]) ? 'forms/'.$parts[1] : 'forms';
        }

        $first = explode('/', $uri)[0] ?? 'root';

        return $first !== '' ? $first : 'root';
    }

    protected function moduleFileName(string $moduleKey): string
    {
        return str_replace('/', '-', $moduleKey).'.md';
    }

    protected function moduleTitle(string $moduleKey): string
    {
        return Str::of($moduleKey)
            ->replace('/', ' / ')
            ->replace('-', ' ')
            ->title()
            ->toString();
    }

    /**
     * @param Collection<int, array{method:string,uri:string,name:string,action:string,module_key:string}> $moduleRoutes
     */
    protected function renderModuleDoc(string $title, string $generatedAt, string $moduleKey, Collection $moduleRoutes): string
    {
        $doc = $this->renderHeader("Module: {$title}", $generatedAt)."\n";
        $doc .= "Module key: `{$moduleKey}`\n\n";
        $doc .= "## Routes\n\n";
        $doc .= "| Method | URI | Name | Action |\n|---|---|---|---|\n";

        $controllers = collect();

        foreach ($moduleRoutes->sortBy('uri') as $r) {
            $doc .= '| `'.($r['method'] ?: '-').'` | `'.$r['uri'].'` | `'.($r['name'] ?: '-').'` | `'.($r['action'] ?: '-').'` |'."\n";

            if ($r['action'] && Str::contains($r['action'], '@')) {
                $controllers->push(Str::before($r['action'], '@'));
            }
        }

        $controllers = $controllers
            ->filter()
            ->unique()
            ->sort()
            ->values();

        if ($controllers->isNotEmpty()) {
            $doc .= "\n## Controllers\n\n";
            foreach ($controllers as $controller) {
                $doc .= "- `{$controller}`\n";
            }
        }

        $viewFiles = $this->guessModuleViews($moduleKey);
        if ($viewFiles->isNotEmpty()) {
            $doc .= "\n## Views\n\n";
            foreach ($viewFiles as $path) {
                $doc .= "- `{$path}`\n";
            }
        }

        $tests = $this->guessModuleTests($moduleKey, $moduleRoutes);
        if ($tests->isNotEmpty()) {
            $doc .= "\n## Tests (related)\n\n";
            foreach ($tests as $path) {
                $doc .= "- `{$path}`\n";
            }
        }

        return $doc;
    }

    /**
     * @return Collection<int, string>
     */
    protected function guessModuleViews(string $moduleKey): Collection
    {
        $resourcesViews = resource_path('views');

        $relativeDir = match (true) {
            $moduleKey === 'root' => null,
            Str::startsWith($moduleKey, 'forms/') => 'forms/'.Str::after($moduleKey, 'forms/'),
            default => $moduleKey,
        };

        if ($relativeDir === null) {
            $path = $resourcesViews.DIRECTORY_SEPARATOR.'dashboard.blade.php';

            return is_file($path) ? collect([Str::replace('\\', '/', 'resources/views/dashboard.blade.php')]) : collect();
        }

        $dirPath = $resourcesViews.DIRECTORY_SEPARATOR.$relativeDir;
        if (! is_dir($dirPath)) {
            return collect();
        }

        return collect(File::allFiles($dirPath))
            ->filter(fn ($f) => Str::endsWith($f->getFilename(), '.blade.php'))
            ->map(fn ($f) => 'resources/views/'.Str::replace('\\', '/', $f->getRelativePathname()))
            ->sort()
            ->values();
    }

    /**
     * @param Collection<int, array{method:string,uri:string,name:string,action:string,module_key:string}> $moduleRoutes
     * @return Collection<int, string>
     */
    protected function guessModuleTests(string $moduleKey, Collection $moduleRoutes): Collection
    {
        $keywords = collect([
            $moduleKey,
            str_replace('/', '.', $moduleKey),
        ])
            ->merge($moduleRoutes->pluck('name')->filter())
            ->map(fn ($v) => (string) $v)
            ->filter()
            ->unique()
            ->values();

        if ($keywords->isEmpty()) {
            return collect();
        }

        $testsDir = base_path('tests');

        return collect(File::allFiles($testsDir))
            ->filter(fn ($f) => Str::endsWith($f->getFilename(), '.php'))
            ->filter(function ($f) use ($keywords): bool {
                $contents = @file_get_contents($f->getRealPath());
                if ($contents === false) {
                    return false;
                }

                foreach ($keywords as $kw) {
                    if ($kw !== '' && Str::contains($contents, $kw)) {
                        return true;
                    }
                }

                return false;
            })
            ->map(fn ($f) => 'tests/'.Str::replace('\\', '/', $f->getRelativePathname()))
            ->unique()
            ->sort()
            ->values();
    }
}
