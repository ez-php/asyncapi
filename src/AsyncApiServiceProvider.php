<?php

declare(strict_types=1);

namespace EzPhp\AsyncApi;

use EzPhp\Contracts\ConfigInterface;
use EzPhp\Contracts\ContainerInterface;
use EzPhp\Contracts\RouterInterface;
use EzPhp\Contracts\ServiceProvider;

/**
 * Class AsyncApiServiceProvider
 *
 * Binds AsyncApiGenerator from config and registers the document endpoint —
 * the same shape as ez-php/openapi's provider.
 *
 * Configuration keys (config/asyncapi.php):
 *   - asyncapi.endpoint — URI of the JSON document (default "/asyncapi.json")
 *   - asyncapi.classes  — list of #[Channel]/#[Message] classes to document (default [])
 *   - asyncapi.servers  — AsyncAPI Server Objects keyed by name (default [])
 *
 * Title and version come from app.name / app.version. Like openapi, a missing
 * router or config (CLI, minimal test containers) degrades to defaults.
 *
 * @package EzPhp\AsyncApi
 */
final class AsyncApiServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function register(): void
    {
        $this->app->bind(AsyncApiGenerator::class, function (ContainerInterface $app): AsyncApiGenerator {
            $classes = [];
            $servers = [];
            $title = 'API';
            $version = '1.0.0';

            try {
                $config = $app->make(ConfigInterface::class);

                $raw = $config->get('app.name', 'API');
                $title = is_string($raw) ? $raw : 'API';
                $raw = $config->get('app.version', '1.0.0');
                $version = is_string($raw) ? $raw : '1.0.0';
                $raw = $config->get('asyncapi.classes', []);
                $classes = is_array($raw) ? array_values(array_filter($raw, 'is_string')) : [];
                $raw = $config->get('asyncapi.servers', []);
                $servers = is_array($raw) ? $this->servers($raw) : [];
            } catch (\Throwable) {
                // Config not bound — use defaults.
            }

            return new AsyncApiGenerator($classes, $title, $version, $servers);
        });
    }

    /**
     * @return void
     */
    public function boot(): void
    {
        try {
            $router = $this->app->make(RouterInterface::class);
        } catch (\Throwable) {
            return; // Router not available — route registration skipped.
        }

        $endpoint = '/asyncapi.json';

        try {
            $raw = $this->app->make(ConfigInterface::class)->get('asyncapi.endpoint', '/asyncapi.json');
            $endpoint = is_string($raw) && $raw !== '' ? $raw : '/asyncapi.json';
        } catch (\Throwable) {
            // Config not bound — use default endpoint.
        }

        $router->get($endpoint, [AsyncApiController::class, '__invoke']);
    }

    /**
     * @param array<mixed> $raw
     *
     * @return array<string, array<string, mixed>>
     */
    private function servers(array $raw): array
    {
        $servers = [];

        foreach ($raw as $name => $server) {
            if (is_string($name) && is_array($server)) {
                $entry = [];

                foreach ($server as $key => $value) {
                    $entry[(string) $key] = $value;
                }

                $servers[$name] = $entry;
            }
        }

        return $servers;
    }
}
