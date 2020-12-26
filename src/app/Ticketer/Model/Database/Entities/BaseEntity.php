<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Entities;

use Ticketer\Model\Database\Attributes\TIdentifierAttribute;

/**
 * Základ všech entit
 * @package App\Model\Entities
 */
abstract class BaseEntity implements EntityInterface
{
    use TArrayValue;
    use TIdentifierAttribute;
}
