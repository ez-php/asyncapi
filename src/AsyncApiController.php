<?php

declare(strict_types=1);

namespace EzPhp\AsyncApi;

use EzPhp\Http\Request;
use EzPhp\Http\Response;

/**
 * Class AsyncApiController
 *
 * Serves the generated AsyncAPI document as JSON (GET /asyncapi.json by
 * default, registered by AsyncApiServiceProvider).
 *
 * @package EzPhp\AsyncApi
 */
final readonly class AsyncApiController
{
    /**
     * @param AsyncApiGenerator $generator
     */
    public function __construct(private AsyncApiGenerator $generator)
    {
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function __invoke(Request $request): Response
    {
        $json = json_encode($this->generator->generate()->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        return (new Response($json !== false ? $json : '{}', 200))
            ->withHeader('Content-Type', 'application/json');
    }
}
