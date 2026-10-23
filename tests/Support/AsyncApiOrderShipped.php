<?php

declare(strict_types=1);

namespace Tests\Support;

use DateTimeImmutable;
use EzPhp\AsyncApi\Attributes\Channel;
use EzPhp\AsyncApi\Attributes\Message;

/**
 * Broadcast fixture: sent on a parameterised channel.
 */
#[Channel('orders.{orderId}', description: 'Lifecycle events of one order')]
#[Message('order.shipped', summary: 'An order left the warehouse', description: 'Sent once per shipment.')]
final class AsyncApiOrderShipped
{
    public function __construct(
        public string $orderId,
        public DateTimeImmutable $shippedAt,
        public ?AsyncApiAddress $address = null,
    ) {
    }
}
