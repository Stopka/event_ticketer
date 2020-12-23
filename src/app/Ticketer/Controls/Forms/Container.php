<?php

declare(strict_types=1);

namespace Ticketer\Controls\Forms;

use Stopka\NetteFormRenderer\Forms\IFormOptionKeys;

class Container extends \Nette\Forms\Container implements IFormOptionKeys
{
    use TContainerExtension;
}
