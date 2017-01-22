<?php
/**
 * Created by IntelliJ IDEA.
 * AdministratorEntity: stopka
 * Date: 12.1.17
 * Time: 0:18
 */

namespace App\Model\Entities;

use App\Model\Entities\Attributes\CapacityAttribute;
use App\Model\Entities\Attributes\NameAttribute;
use App\Model\Entities\Attributes\PriceAttribute;
use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\Attributes\Identifier;

/**
 * Administrátor systému
 * @package App\Model\Entities
 * @ORM\Entity
 */
class OptionEntity extends BaseEntity {
    use Identifier, NameAttribute, CapacityAttribute, PriceAttribute;

    /**
     * @ORM\ManyToOne(targetEntity="AdditionEntity", inversedBy="additionItems")
     * @var AdditionEntity
     */
    private $addition;

    /**
     * @return AdditionEntity
     */
    public function getAddition() {
        return $this->addition;
    }

    /**
     * @param AdditionEntity $addition
     * @return $this
     */
    public function setAddtition($addition) {
        if($this->addition){
            $this->addition->removeInversedOption($this);
        }
        $this->addition = $addition;
        if($addition) {
            $addition->addInversedOption($this);
        }
        return $this;
    }

}