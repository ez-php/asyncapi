<?php

declare(strict_types=1);

namespace Tests\Support;

/**
 * Nested payload type (inlined into the payload schema by SchemaGenerator).
 */
final class AsyncApiAddress
{
    public function __construct(
        public string $city,
    ) {
    }
}
