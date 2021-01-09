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

    /**
     * @return array<mixed>
     */
    protected function getDefaultParameters(): array
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        $parameters = parent::getDefaultParameters();
        $parameters['appDir'] = isset($trace[1]['file']) ? dirname($trace[1]['file'], 2) : null;

        return $parameters;
    }

    public function setTempDirectory(string $path)
    {
        $this->tryCreateDir($path);

        return parent::setTempDirectory($path);
    }


    public function enableTracy(?string $logDirectory = null, ?string $email = null): void
    {
        $this->tryCreateDir($logDirectory);
        parent::enableTracy($logDirectory, $email);
    }

    private function tryCreateDir(?string $directory): void
    {
        if (null === $directory || is_dir($directory)) {
            return;
        }
        @mkdir($directory, 0777, true);
    }
}
