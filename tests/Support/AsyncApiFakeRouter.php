<?php

declare(strict_types=1);

namespace Tests\Support;

use EzPhp\Contracts\RouterInterface;
use stdClass;

/**
 * RouterInterface that records registered routes as [method, path, handler].
 *
 * @package Tests\Support
 */
final class AsyncApiFakeRouter implements RouterInterface
{
    /** @var list<array{string, string, callable|array{class-string, string}}> */
    public array $routes = [];

    public function get(string $path, callable|array $handler): object
    {
        return $this->add('GET', $path, $handler);
    }

    public function post(string $path, callable|array $handler): object
    {
        return $this->add('POST', $path, $handler);
    }

    public function put(string $path, callable|array $handler): object
    {
        return $this->add('PUT', $path, $handler);
    }

    public function patch(string $path, callable|array $handler): object
    {
        return $this->add('PATCH', $path, $handler);
    }

    public function delete(string $path, callable|array $handler): object
    {
        return $this->add('DELETE', $path, $handler);
    }

    /**
     * @return array<mixed>
     */
    public function toCache(): array
    {
        return [];
    }

    /**
     * @param callable|array{class-string, string} $handler
     */
    private function add(string $method, string $path, callable|array $handler): object
    {
        $this->routes[] = [$method, $path, $handler];

        return new stdClass();
    }
}
