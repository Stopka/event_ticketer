<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 20.1.17
 * Time: 20:18
 */

namespace App\Model;


use Nette\SmartObject;

class FileStorageFactory {
    use SmartObject;

    /** @var string */
    private $fileDir;

    public function __construct(string $fileDir) {
        $this->fileDir = $fileDir;
    }


    public function create(string $subDir, bool $create = false): FileStorage {
        return new FileStorage($this->fileDir, $subDir, $create);
    }
}