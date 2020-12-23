<?php

declare(strict_types=1);

namespace Ticketer\Configurators;

use Nette\Configurator;

class TicketerConfigurator extends Configurator
{
    /**
     * @param string $config
     * @return $this
     */
    public function addConfigIfExists(string $config): self
    {
        if (file_exists($config)) {
            $this->addConfig($config);
        }

        return $this;
    }
}
