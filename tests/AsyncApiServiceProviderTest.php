<?php

declare(strict_types=1);

namespace Tests;

use EzPhp\AsyncApi\AsyncApiController;
use EzPhp\AsyncApi\AsyncApiDocument;
use EzPhp\AsyncApi\AsyncApiGenerator;
use EzPhp\AsyncApi\AsyncApiServiceProvider;
use EzPhp\AsyncApi\Attributes\Channel;
use EzPhp\AsyncApi\Attributes\Message;
use EzPhp\Contracts\ConfigInterface;
use EzPhp\Contracts\RouterInterface;
use EzPhp\Http\Request;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use Tests\Support\AsyncApiChatMessage;
use Tests\Support\AsyncApiFakeConfig;
use Tests\Support\AsyncApiFakeContainer;
use Tests\Support\AsyncApiFakeRouter;

/**
 * Class AsyncApiServiceProviderTest
 *
 * @package Tests
 */
#[CoversClass(AsyncApiServiceProvider::class)]
#[CoversClass(AsyncApiController::class)]
#[UsesClass(AsyncApiGenerator::class)]
#[UsesClass(AsyncApiDocument::class)]
#[UsesClass(Channel::class)]
#[UsesClass(Message::class)]
final class AsyncApiServiceProviderTest extends TestCase
{
    public function testConfiguredGeneratorAndEndpoint(): void
    {
        $router = new AsyncApiFakeRouter();
        $container = new AsyncApiFakeContainer();
        $container->instance(RouterInterface::class, $router);
        $container->instance(ConfigInterface::class, new AsyncApiFakeConfig([
            'app.name' => 'Shop',
            'app.version' => '3.1.0',
            'asyncapi.endpoint' => '/docs/async.json',
            'asyncapi.classes' => [AsyncApiChatMessage::class, 42],
            'asyncapi.servers' => ['prod' => ['host' => 'ws.shop.test', 'protocol' => 'wss'], 'broken' => 'x', 3 => []],
        ]));

        $provider = new AsyncApiServiceProvider($container);
        $provider->register();
        $provider->boot();

        $this->assertSame([['GET', '/docs/async.json', [AsyncApiController::class, '__invoke']]], $router->routes);

        $document = $container->make(AsyncApiGenerator::class)->generate()->toArray();
        $this->assertSame(['title' => 'Shop', 'version' => '3.1.0'], $document['info']);
        $this->assertSame(['prod' => ['host' => 'ws.shop.test', 'protocol' => 'wss']], $document['servers']);
        $this->assertIsArray($document['channels']);
        $this->assertSame(['chat'], array_keys($document['channels']));
    }

    public function testDefaultsWithoutConfigOrRouter(): void
    {
        $container = new AsyncApiFakeContainer();
        $provider = new AsyncApiServiceProvider($container);
        $provider->register();
        $provider->boot(); // no router bound: silently skipped

        $document = $container->make(AsyncApiGenerator::class)->generate()->toArray();
        $this->assertSame(['title' => 'API', 'version' => '1.0.0'], $document['info']);
        $this->assertSame([], $document['channels']);
    }

    public function testDefaultEndpointWithoutConfig(): void
    {
        $router = new AsyncApiFakeRouter();
        $container = new AsyncApiFakeContainer();
        $container->instance(RouterInterface::class, $router);

        (new AsyncApiServiceProvider($container))->boot();

        $this->assertSame('/asyncapi.json', $router->routes[0][1] ?? null);
    }

    public function testControllerServesTheDocumentAsJson(): void
    {
        $controller = new AsyncApiController(new AsyncApiGenerator([AsyncApiChatMessage::class], 'Shop'));

        $response = $controller(new Request('GET', '/asyncapi.json'));

        $this->assertSame(200, $response->status());
        $this->assertSame('application/json', $response->headers()['Content-Type'] ?? null);
        $decoded = json_decode($response->body(), true);
        $this->assertIsArray($decoded);
        $this->assertSame('3.0.0', $decoded['asyncapi'] ?? null);
        $this->assertStringContainsString('"$ref": "#/components/messages/AsyncApiChatMessage"', $response->body());
    }
}
