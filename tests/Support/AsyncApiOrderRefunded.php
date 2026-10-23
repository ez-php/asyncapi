<?php

declare(strict_types=1);

namespace Tests\Support;

use EzPhp\AsyncApi\Attributes\Channel;
use EzPhp\AsyncApi\Attributes\Message;

/**
 * Fixture whose address differs from AsyncApiOrderShipped's ("orders.{orderId}")
 * only in its separator, so both reduce to the channel id "orders_orderId".
 */
#[Channel('orders/{orderId}')]
#[Message('order.refunded')]
final class AsyncApiOrderRefunded
{
    public function __construct(public string $orderId)
    {
    }
}
