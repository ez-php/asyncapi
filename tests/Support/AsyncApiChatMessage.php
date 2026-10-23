<?php

declare(strict_types=1);

namespace Tests\Support;

use EzPhp\AsyncApi\Attributes\Channel;
use EzPhp\AsyncApi\Attributes\Message;

/**
 * Incoming WebSocket message fixture (action "receive"), channel without parameters.
 */
#[Channel('chat')]
#[Message('chat.message', action: Message::RECEIVE)]
final class AsyncApiChatMessage
{
    public function __construct(
        public string $text,
    ) {
    }
}
