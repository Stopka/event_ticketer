<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 12.1.17
 * Time: 22:42
 */

namespace App\Model\Persistence\Attribute;

use Doctrine\ORM\Mapping as ORM;

trait TInternalInfoAttribute {

    /**
     * @ORM\Column(type="text",nullable=true)
     * @var string
     */
    private $internalInfo;

    /**
     * @return array
     */
    public function getInternalInfo(): ?array {
        return json_decode($this->internalInfo,true);
    }

    /**
     * @param array $internalInfo
     */
    public function setInternalInfo(?array $internalInfo): void {
        $this->internalInfo = json_encode($internalInfo);
    }

    /**
     * @param $key string
     * @return mixed
     */
    public function getInternalInfoItem(string $key) {
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
    public function setInternalInfoItem(string $key,$value) {
        $info = $this->getInternalInfo();
        if(!$info){
            return $info = [];
        }
        $info[$key]=$value;
        $this->setInternalInfo($info);
    }


}