<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 20.1.17
 * Time: 20:18
 */

namespace App\Model;


use App\Model\Exception\NotFoundException;
use Nette\SmartObject;
use Nette\Utils\Finder;

class FileStorage {
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
    public function __construct(string $baseDir, string $subDir, bool $create = false) {
        $this->dir = $baseDir;
        if ($create) {
            $path = explode('/', $subDir);
            array_shift($path);
            foreach ($path as $dir) {
                $this->createDir('/' . $dir);
            }
        }
        $this->dir = $baseDir . $subDir;
        if (!is_dir($this->dir)) {
            throw new NotFoundException("Directory " . $this->dir . " not found!");
        }
    }

    /**
     * @return string
     */
    private function getBasePath() {
        return $this->dir;
    }

    /**
     * @param $path string
     * @return string
     */
    public function getFullPath($path) {
        return $this->getBasePath() . $path;
    }

    public function createDir($dir) {
        $dir = $this->getFullPath($dir);
        if (!is_dir($dir)) {
            umask(0);
            mkdir($dir, 0777);
        }
    }

    /**
     * @return string[]
     */
    public function getAllFiles(): array {
        $paths = [];
        foreach (Finder::findFiles()->in($this->dir) as $filename => $fileInfo) {
            $paths[] = $filename;
        }
        return $paths;
    }

}