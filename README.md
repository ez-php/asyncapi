# ez-php/asyncapi

[AsyncAPI 3.0](https://www.asyncapi.com/docs/reference/specification/v3.0.0) documentation for the event-driven side of an ez-php application: broadcast channels, outgoing webhooks, WebSocket messages. Attribute-driven like `ez-php/openapi`, with payload schemas from `ez-php/json-schema`.

## Installation

```bash
composer require ez-php/asyncapi
```

Register the provider (`provider/modules.php`):

```php
EzPhp\AsyncApi\AsyncApiServiceProvider::class,
```

## Annotating messages

Put `#[Channel]` and `#[Message]` on the class that is sent or received. Its public properties are the payload:

```php
use EzPhp\AsyncApi\Attributes\Channel;
use EzPhp\AsyncApi\Attributes\Message;

#[Channel('orders.{orderId}', description: 'Lifecycle events of one order')]
#[Message('order.shipped', summary: 'An order left the warehouse')]
final class OrderShipped
{
    public function __construct(
        public string $orderId,
        public DateTimeImmutable $shippedAt,
    ) {}
}

#[Channel('chat')]
#[Message('chat.message', action: Message::RECEIVE)]   // incoming WebSocket message
final class ChatMessage
{
    public function __construct(public string $text) {}
}
```

- `action`: `send` (default; the application publishes it) or `receive` (the application consumes it).
- `{orderId}` becomes a channel parameter.
- Classes with the same address share one channel.

## Configuration

`config/asyncapi.php`:

```php
return [
    'endpoint' => getenv('ASYNCAPI_ENDPOINT') ?: '/asyncapi.json',
    'classes' => [OrderShipped::class, ChatMessage::class],
    'servers' => [
        'production' => ['host' => 'ws.example.com', 'protocol' => 'wss'],
    ],
];
```

There is no discovery: only listed classes are documented. A listed class that is missing, lacks an attribute, or clashes on short name throws `AsyncApiException`. Title and version come from `app.name` / `app.version`.

## Output

`GET /asyncapi.json` returns:

- **`channels`:** one per address, with `{param}` parameters.
- **`operations`:** `sendOrderShipped`, `receiveChatMessage`, ...
- **`components.messages`:** one per class, keyed by its short name.
- **`components.schemas`:** the payload JSON Schemas.

Generate it without the endpoint:

```php
$document = (new AsyncApiGenerator([OrderShipped::class], 'Shop', '1.0.0'))->generate()->toArray();
```

## Not included

YAML output, an HTML/Studio viewer, code generation.

## License

MIT
