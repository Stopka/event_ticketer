<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Entities;

use Ticketer\Model\Database\Attributes\TEmailAttribute;
use Ticketer\Model\Database\Attributes\TIdentifierAttribute;
use Ticketer\Model\Database\Attributes\TPasswordAttribute;
use Ticketer\Model\Database\Attributes\TPersonNameAttribute;
use Ticketer\Model\Database\Attributes\TUsernameAttribute;
use Doctrine\ORM\Mapping as ORM;

/**
 * Administrátor systému
 * @package App\Model\Entities
 * @ORM\Entity
 */
class AdministratorEntity extends BaseEntity
{
    use TIdentifierAttribute;
    use TUsernameAttribute;
    use TPasswordAttribute;
    use TPersonNameAttribute;
    use TEmailAttribute;
}
