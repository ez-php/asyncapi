<?php

declare(strict_types=1);

namespace Tests\Support;

use EzPhp\Contracts\ConfigInterface;

/**
 * ConfigInterface over a flat dot-key map.
 *
 * @package Tests\Support
 */
final readonly class AsyncApiFakeConfig implements ConfigInterface
{
    /**
     * @param array<string, mixed> $values
     */
    public function __construct(private array $values)
    {
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return array_key_exists($key, $this->values) ? $this->values[$key] : $default;
    }
}
