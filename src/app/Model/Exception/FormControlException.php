<?php

namespace App\Model\Exception;

use Throwable;

/**
 * Description of RuntimeException
 *
 * @author stopka
 */
class FormControlException extends Exception {
    const PATH_DIVIDER = '-';

    /** @var string[] */
    private $controlPath;


    public function __construct(Throwable $previous, array $controlPath = []) {
        parent::__construct($previous->getMessage(), 0, $previous);
        $this->setControlPathsArray($controlPath);
    }

    /**
     * @param array $controlPath
     * @return $this
     */
    public function setControlPathsArray(array $controlPath): self {
        $this->controlPath = $controlPath;
        return $this;
    }

    /**
     * @return  string[]
     */
    public function getControlPathsArray(): array {
        return $this->controlPath;
    }

    /**
     * @return string
     */
    public function getControlPath(): string {
        return implode(self::PATH_DIVIDER, $this->getControlPathsArray());
    }

    /**
     * @param string $controlPath
     * @return $this
     */
    public function setControlPath(string $controlPath): self {
        $this->setControlPathsArray(explode(self::PATH_DIVIDER, $controlPath));
        return $this;
    }

    public function prependControlPath(string $path): self {
        array_unshift($this->controlPath, $path);
        return $this;
    }

}


