<?php

declare(strict_types=1);

namespace Tests;

use EzPhp\AsyncApi\AsyncApiDocument;
use EzPhp\AsyncApi\AsyncApiException;
use EzPhp\AsyncApi\AsyncApiGenerator;
use EzPhp\AsyncApi\Attributes\Channel;
use EzPhp\AsyncApi\Attributes\Message;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use Tests\Support\AsyncApiBadAction;
use Tests\Support\AsyncApiChatMessage;
use Tests\Support\AsyncApiOrderCancelled;
use Tests\Support\AsyncApiOrderShipped;
use Tests\Support\AsyncApiUnannotated;

/**
 * Class AsyncApiGeneratorTest
 *
 * @package Tests
 */
#[CoversClass(AsyncApiGenerator::class)]
#[CoversClass(AsyncApiDocument::class)]
#[CoversClass(Channel::class)]
#[CoversClass(Message::class)]
#[UsesClass(AsyncApiException::class)]
final class AsyncApiGeneratorTest extends TestCase
{
    /**
     * @return array<string, mixed>
     */
    private function document(): array
    {
        return (new AsyncApiGenerator(
            [AsyncApiOrderShipped::class, AsyncApiOrderCancelled::class, AsyncApiChatMessage::class],
            'Shop',
            '2.0.0',
            ['production' => ['host' => 'ws.shop.test', 'protocol' => 'wss']],
        ))->generate()->toArray();
    }

    public function testDocumentHeader(): void
    {
        $document = $this->document();

        $this->assertSame('3.0.0', $document['asyncapi']);
        $this->assertSame(['title' => 'Shop', 'version' => '2.0.0'], $document['info']);
        $this->assertSame(['production' => ['host' => 'ws.shop.test', 'protocol' => 'wss']], $document['servers']);
    }

    public function testChannelsMergeByAddressAndDetectParameters(): void
    {
        $this->assertSame([
            'orders_orderId' => [
                'address' => 'orders.{orderId}',
                'messages' => [
                    'AsyncApiOrderShipped' => ['$ref' => '#/components/messages/AsyncApiOrderShipped'],
                    'AsyncApiOrderCancelled' => ['$ref' => '#/components/messages/AsyncApiOrderCancelled'],
                ],
                'parameters' => ['orderId' => []],
                'description' => 'Lifecycle events of one order',
            ],
            'chat' => [
                'address' => 'chat',
                'messages' => ['AsyncApiChatMessage' => ['$ref' => '#/components/messages/AsyncApiChatMessage']],
            ],
        ], $this->document()['channels']);
    }

    public function testOneOperationPerMessageWithItsAction(): void
    {
        $this->assertSame([
            'sendAsyncApiOrderShipped' => [
                'action' => 'send',
                'channel' => ['$ref' => '#/channels/orders_orderId'],
                'messages' => [['$ref' => '#/channels/orders_orderId/messages/AsyncApiOrderShipped']],
                'summary' => 'An order left the warehouse',
            ],
            'sendAsyncApiOrderCancelled' => [
                'action' => 'send',
                'channel' => ['$ref' => '#/channels/orders_orderId'],
                'messages' => [['$ref' => '#/channels/orders_orderId/messages/AsyncApiOrderCancelled']],
            ],
            'receiveAsyncApiChatMessage' => [
                'action' => 'receive',
                'channel' => ['$ref' => '#/channels/chat'],
                'messages' => [['$ref' => '#/channels/chat/messages/AsyncApiChatMessage']],
            ],
        ], $this->document()['operations']);
    }

    public function testMessagesAndPayloadSchemasLandInComponents(): void
    {
        $document = $this->document();

        $this->assertSame([
            'name' => 'order.shipped',
            'summary' => 'An order left the warehouse',
            'description' => 'Sent once per shipment.',
            'contentType' => 'application/json',
            'payload' => ['$ref' => '#/components/schemas/AsyncApiOrderShipped'],
        ], $this->at($document, 'components', 'messages', 'AsyncApiOrderShipped'));

        $schema = $this->at($document, 'components', 'schemas', 'AsyncApiOrderShipped');
        $this->assertSame('object', $schema['type'] ?? null);
        $this->assertSame(['orderId', 'shippedAt'], $schema['required'] ?? null);
        $this->assertSame(['type' => 'string', 'format' => 'date-time'], $this->at($schema, 'properties', 'shippedAt'));
        $this->assertArrayHasKey('AsyncApiChatMessage', $this->at($document, 'components', 'schemas'));
    }

    public function testEmptyListProducesAMinimalDocument(): void
    {
        $this->assertSame([
            'asyncapi' => '3.0.0',
            'info' => ['title' => 'API', 'version' => '1.0.0'],
            'channels' => [],
            'operations' => [],
        ], (new AsyncApiGenerator([]))->generate()->toArray());
    }

    /**
     * @return array<string, array{list<string>, string}>
     */
    public static function invalidClassProvider(): array
    {
        return [
            'missing class' => [['App\\Missing'], 'does not exist'],
            'no #[Message]' => [[AsyncApiUnannotated::class], 'needs both #[Channel] and #[Message]'],
            'invalid action' => [[AsyncApiBadAction::class], 'action must be "send" or "receive"'],
            'short name clash' => [[AsyncApiOrderShipped::class, Support\Other\AsyncApiOrderShipped::class], 'share the short name'],
        ];
    }

    /**
     * @param list<string> $classes
     */
    #[DataProvider('invalidClassProvider')]
    public function testInvalidClassesThrow(array $classes, string $message): void
    {
        $this->expectException(AsyncApiException::class);
        $this->expectExceptionMessage($message);

        (new AsyncApiGenerator($classes))->generate();
    }

    /**
     * Walk nested arrays by key, asserting each level is an array.
     *
     * @param array<mixed> $array
     *
     * @return array<mixed>
     */
    private function at(array $array, string ...$keys): array
    {
        foreach ($keys as $key) {
            $array = $array[$key] ?? null;
            $this->assertIsArray($array, "missing array at key \"{$key}\"");
        }

        return $array;
    }
}
