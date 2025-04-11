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

        // The objective of this directive is to allow interaction with contents of the table
        // component. The  concept was taken from konradkalemba/blade-components-scoped-slots.
        Blade::directive('interact', function (mixed $expression): string {
            $directive = array_map(trim(...), preg_split('/,(?![^(]*[)])/', $expression));
            $directive[1] ??= ''; // Prevents the error "Undefined key: 1" when the parameter is not defined.

            [$name, $arguments] = $directive;

            $parameters = collect(array_flip($directive))->except($name, $arguments)
                ->flip()
                ->push('$__env')
                ->implode(',');

            $name = str_replace('.', '_', $name);

            return "<?php \$__env->slot({$name}, function({$arguments}) use ({$parameters}) { ?>";
        });

        Blade::directive('endinteract', fn(): string => '<?php }); ?>');

        Blade::precompiler(fn(string $string): string => preg_replace_callback('/<\s*kanban\:(setup|script)(\s+[a-zA-Z0-9_-]+(?:\s+[a-zA-Z0-9_-]+)*)?\s*\/?>/', function (array $matches): string {
            $script = Facade::directives()->script();

            return match ($matches[1]) {
                'setup' => "{$script}",
                'script' => $script
            };
        }, $string));
    }

    /**
     * Get the HTML that represents the script load.
     */
    public function script(): string
    {
        $manifest = $this->manifest('js/kanban.js');
        $js = $manifest['file'];

        $html = $this->format($js);

        return $html;
    }

    /**
     * Format according to the file extension.
     */
    private function format(string $file): string
    {
        return (match (true) { // @phpstan-ignore-line
            str_ends_with($file, '.js') => fn() => "<script src=\"/kanban/script/{$file}\" defer></script>",
        })();
    }

    /**
     * Load the manifest file and retrieve the desired data.
     */
    private function manifest(string $file, ?string $index = null): string|array
    {
        $content = json_decode(file_get_contents(__DIR__ . '/../../../../dist/.vite/manifest.json'), true);

        return data_get($content[$file], $index);
    }
}
