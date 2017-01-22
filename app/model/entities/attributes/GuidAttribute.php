<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 12.1.17
 * Time: 22:42
 */

namespace App\Model\Entities\Attributes;

use Doctrine\ORM\Mapping as ORM;

trait GuidAttribute {

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    private $guid;

    protected function generateGuid(){
        $this->guid = uniqid();
    }

    /**
     * @return string
     */
    public function getGuid() {
        return $this->guid;
    }


}