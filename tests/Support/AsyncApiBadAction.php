<?php

declare(strict_types=1);

namespace Tests\Support;

use EzPhp\AsyncApi\Attributes\Channel;
use EzPhp\AsyncApi\Attributes\Message;

/**
 * Declares an action other than send/receive — rejected by the generator.
 */
#[Channel('broken')]
#[Message('broken', action: 'publish')]
final class AsyncApiBadAction
{
    public string $value = '';
}
