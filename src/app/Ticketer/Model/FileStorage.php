<?php

declare(strict_types=1);

namespace Ticketer\Model;

use Ticketer\Model\Exceptions\NotFoundException;
use Nette\SmartObject;
use Nette\Utils\Finder;

class FileStorage
{
    use SmartObject;

    /**
     * @var string
     */
    private $dir;

    /**
     * FileStorage constructor.
     * @param string $baseDir
     * @param string $subDir
     * @param bool $create
     */
    public function __construct(string $baseDir, string $subDir, bool $create = false)
    {
        $this->dir = $baseDir;
        if ($create) {
            $this->createDir('/' . $subDir);
        }
        $this->dir = $baseDir . $subDir;
        if (!is_dir($this->dir)) {
            throw new NotFoundException("Directory " . $this->dir . " not found!");
        }
    }

    /**
     * @return string
     */
    private function getBasePath()
    {
        return $this->dir;
    }

    /**
     * @param string $path
     * @return string
     */
    public function getFullPath(string $path = "")
    {
        return $this->getBasePath() . $path;
    }

    public function createDir(string $dir = ''): void
    {
        $dir = $this->getFullPath($dir);
        if (!is_dir($dir)) {
            umask(0);
            if (!mkdir($dir, 0777, true) && !is_dir($dir)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $dir));
            }
        }
    }

    /**
     * @return string[]
     */
    public function getAllFiles(): array
    {
        $paths = [];
        foreach (Finder::findFiles()->in($this->dir) as $filename => $fileInfo) {
            $paths[] = $filename;
        }

        return $paths;
    }
}
