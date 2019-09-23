<?php


namespace App\Configurator;


class Configurator extends \Nette\Configurator
{
    public function addConfigIfExists(string $config)
    {
        if (file_exists($config)) {
            $this->addConfig($config);
        }
        return $this;
    }
}
