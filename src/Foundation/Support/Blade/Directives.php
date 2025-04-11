<?php

namespace Pablomadariaga\Kanban\Foundation\Support\Blade;

use Illuminate\Support\Facades\Blade;
use Pablomadariaga\Kanban\Facades\Kanban as Facade;

class Directives
{
    /**
     * Register the Blade directives.
     */
    public static function register(): void
    {
        Blade::directive('kanbanScript', fn(): string => Facade::directives()->script());

        Blade::directive('kanbanSetup', function (): string {
            $script = Facade::directives()->script();
            return "{$script}";
        });

        Blade::precompiler(fn(string $string): string => preg_replace_callback(
            '/<\s*kanban\:(setup|script)(\s+[a-zA-Z0-9_-]+(?:\s+[a-zA-Z0-9_-]+)*)?\s*\/?>/',
            function (array $matches): string {
                $script = Facade::directives()->script();
                return match ($matches[1]) {
                    'setup' => "{$script}",
                    'script' => $script,
                };
            },
            $string
        ));
    }

    /**
     * Get the HTML that represents the script load.
     *
     * @return string HTML script tag to load the compiled Kanban JS.
     */
    public function script(): string
    {
        // Attempt to get manifest entry using the default key.
        $manifest = $this->manifest('js/kanban.js');
        if (!$manifest || !isset($manifest['file'])) {
            // Fallback: search the manifest for a JS file that matches "kanban-*.js"
            $manifest = $this->findKanbanJsInManifest();
        }
        if (!$manifest || !isset($manifest['file'])) {
            // Optionally, return an HTML comment for debugging purposes.
            return '<!-- Kanban JS manifest entry not found -->';
        }

        $js = $manifest['file'];
        return $this->format($js);
    }

    /**
     * Format according to the file extension.
     *
     * @param string $file The JS file path.
     * @return string HTML script tag with the correct src attribute.
     */
    private function format(string $file): string
    {
        return (match (true) { // @phpstan-ignore-line
            str_ends_with($file, '.js') => fn() => "<script src=\"/kaban/script/{$file}\" defer></script>",
        })();
    }

    /**
     * Load the manifest file and retrieve the desired data.
     *
     * @param string      $file  The key to search in the manifest.
     * @param string|null $index Optional index to retrieve.
     * @return string|array Returns the manifest entry or an empty array if not found.
     */
    private function manifest(string $file, ?string $index = null): string|array
    {
        $manifestPath = __DIR__ . '/../../../../dist/.vite/manifest.json';
        if (!file_exists($manifestPath)) {
            return [];
        }
        $content = json_decode(file_get_contents($manifestPath), true);
        return data_get($content[$file], $index);
    }

    /**
     * Fallback method: search the manifest for a JS file that matches the pattern "kanban-*.js".
     *
     * @return array Returns the manifest entry as an array for the Kanban JS file, or an empty array if not found.
     */
    private function findKanbanJsInManifest(): array
    {
        $manifestPath = __DIR__ . '/../../../../dist/.vite/manifest.json';
        if (!file_exists($manifestPath)) {
            return [];
        }
        $content = json_decode(file_get_contents($manifestPath), true);
        foreach ($content as $key => $entry) {
            if (str_starts_with($key, 'kanban-') && str_ends_with($key, '.js')) {
                return $entry;
            }
        }
        return [];
    }
}
