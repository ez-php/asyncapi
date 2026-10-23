<?php

declare(strict_types=1);

namespace Tests\Support\Other;

use EzPhp\AsyncApi\Attributes\Channel;
use EzPhp\AsyncApi\Attributes\Message;

/**
 * Same short name as Tests\Support\AsyncApiOrderShipped — a component key clash.
 */
#[Channel('other')]
#[Message('other.shipped')]
final class AsyncApiOrderShipped
{
    public string $value = '';
}
