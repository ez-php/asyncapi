<?php

declare(strict_types=1);

namespace Tests\Support;

use EzPhp\AsyncApi\Attributes\Channel;

/**
 * Has #[Channel] but no #[Message] — rejected by the generator.
 */
#[Channel('nowhere')]
final class AsyncApiUnannotated
{
    public string $value = '';
}
