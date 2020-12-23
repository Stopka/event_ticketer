<?php

declare(strict_types=1);

namespace Ticketer\Responses\PdfResponse;

use Nette\Bridges\ApplicationLatte\Template;
use Nette\SmartObject;

/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 3.1.18
 * Time: 16:24
 */
class PdfResponseDependencies
{
    private string $tempDir;

    /**
     * PdfResponse constructor.
     * @param string $tempDir
     */
    public function __construct(string $tempDir)
    {
        $this->setTempDir($tempDir);
    }

    /**
     * @return string
     */
    public function getTempDir(): string
    {
        return $this->tempDir;
    }

    /**
     * @param string $tempDir
     */
    public function setTempDir(string $tempDir): void
    {
        $this->tempDir = $tempDir;
    }
}
