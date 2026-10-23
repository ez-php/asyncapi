<?php

declare(strict_types=1);

namespace Tests\Support;

use EzPhp\AsyncApi\Attributes\Channel;
use EzPhp\AsyncApi\Attributes\Message;

/**
 * Second message on the same channel as AsyncApiOrderShipped (merged channel).
 */
#[Channel('orders.{orderId}', description: 'Ignored: the first description wins')]
#[Message('order.cancelled')]
final class AsyncApiOrderCancelled
{
    public function __construct(
        public string $orderId,
        public string $reason,
    ) {
    }
}
