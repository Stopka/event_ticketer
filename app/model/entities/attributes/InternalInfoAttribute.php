<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 12.1.17
 * Time: 22:42
 */

namespace App\Model\Entities\Attributes;

use Doctrine\ORM\Mapping as ORM;

trait InternalInfoAttribute {

    /**
     * @ORM\Column(type="text",nullable=true)
     * @var string
     */
    private $internalInfo;

    /**
     * @return array
     */
    public function getInternalInfo() {
        return json_decode($this->internalInfo,true);
    }

    /**
     * @param array $internalInfo
     */
    public function setInternalInfo($internalInfo) {
        $this->internalInfo = json_encode($internalInfo);
    }

    /**
     * @param $key string
     * @return mixed
     */
    public function getInternalInfoItem($key) {
        $info = $this->getInternalInfo();
        if(!$info||!isset($info[$key])){
            return NULL;
        }
        return $info[$key];
    }

    /**
     * @param $key string
     * @param $value mixed
     */
    public function setInternalInfoItem($key,$value) {
        $info = $this->getInternalInfo();
        if(!$info){
            return $info = [];
        }
        $info[$key]=$value;
        $this->setInternalInfo($info);
    }


}