<?php

declare(strict_types=1);

namespace Ticketer\Model;

use Nette\SmartObject;

class FileStorageFactory
{
    use SmartObject;

    /** @var string */
    private $fileDir;

    public function __construct(string $fileDir)
    {
        $this->fileDir = $fileDir;
    }


    public function create(string $subDir, bool $create = false): FileStorage
    {
        return new FileStorage($this->fileDir, $subDir, $create);
    }
}
