<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 20.1.17
 * Time: 20:18
 */

namespace App\Model;


use Nette\Object;

class FileStorage extends Object {

    /**
     * @var string
     */
    private $dir;

    public function __construct($dir) {
        $this->dir = $dir;
    }

    /**
     * @return string
     */
    private function getBasePath(){
        return $this->dir;
    }

    /**
     * @param $path string
     * @return string
     */
    public function getFullPath($path){
        return $this->getBasePath().$path;
    }

    public function createDir($dir){
        $dir = $this->getFullPath($dir);
        if (!is_dir($dir)) {
            umask(0);
            mkdir($dir, 0777);
        }
    }

}