<?php

declare(strict_types=1);

namespace EzPhp\AsyncApi;

/**
 * Class AsyncApiException
 *
 * A configured message class cannot be documented: it does not exist, lacks
 * #[Channel] or #[Message], declares an invalid action, or its short name
 * clashes with another message class.
 *
 * @package EzPhp\AsyncApi
 */
final class AsyncApiException extends \InvalidArgumentException
{
}
