<?php

declare(strict_types=1);

namespace EzPhp\AsyncApi\Attributes;

use Attribute;

/**
 * Attribute Channel
 *
 * The channel a message class is published on or received from — e.g. a
 * broadcast channel, a webhook target or a WebSocket topic. `{name}`
 * placeholders in the address become channel parameters.
 *
 *   #[Channel('orders.{orderId}', description: 'Lifecycle events of one order')]
 *
 * Several message classes may share an address; they end up in one channel.
 *
 * @package EzPhp\AsyncApi\Attributes
 */
#[Attribute(Attribute::TARGET_CLASS)]
final readonly class Channel
{
    /**
     * @param string      $address     Channel address, e.g. "orders.{orderId}".
     * @param string|null $description Channel description (the first class declaring one wins).
     */
    public function __construct(
        public string $address,
        public ?string $description = null,
    ) {
    }
}
