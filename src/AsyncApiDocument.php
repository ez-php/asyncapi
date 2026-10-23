<?php

declare(strict_types=1);

namespace EzPhp\AsyncApi;

/**
 * Class AsyncApiDocument
 *
 * Immutable AsyncAPI 3.0 document. `toArray()` returns the JSON-ready
 * structure; empty `servers` and empty `components` sections are omitted.
 *
 * @package EzPhp\AsyncApi
 */
final readonly class AsyncApiDocument
{
    public const string VERSION = '3.0.0';

    /**
     * @param string                              $title
     * @param string                              $version    The application's (API) version, not the AsyncAPI version.
     * @param array<string, array<string, mixed>> $servers    AsyncAPI Server Objects keyed by name.
     * @param array<string, array<string, mixed>> $channels
     * @param array<string, array<string, mixed>> $operations
     * @param array<string, array<string, mixed>> $components `messages` / `schemas` maps.
     */
    public function __construct(
        public string $title,
        public string $version,
        public array $servers = [],
        public array $channels = [],
        public array $operations = [],
        public array $components = [],
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $document = [
            'asyncapi' => self::VERSION,
            'info' => ['title' => $this->title, 'version' => $this->version],
        ];

        if ($this->servers !== []) {
            $document['servers'] = $this->servers;
        }

        $document['channels'] = $this->channels;
        $document['operations'] = $this->operations;

        $components = array_filter($this->components, static fn (array $section): bool => $section !== []);

        if ($components !== []) {
            $document['components'] = $components;
        }

        return $document;
    }
}
