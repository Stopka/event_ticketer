<?php
/**
 * Created by IntelliJ IDEA.
 * AdministratorEntity: stopka
 * Date: 12.1.17
 * Time: 0:18
 */

namespace App\Model\Entities;

use App\Model\Entities\Attributes\CapacityAttribute;
use App\Model\Entities\Attributes\IdentifierAttribute;
use App\Model\Entities\Attributes\InternalInfoAttribute;
use App\Model\Entities\Attributes\NameAttribute;
use App\Model\Entities\Attributes\PriceAttribute;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Administrátor systému
 * @package App\Model\Entities
 * @ORM\Entity
 */
class OptionEntity extends BaseEntity {
    use IdentifierAttribute, NameAttribute, CapacityAttribute, PriceAttribute, InternalInfoAttribute;

    public function __construct() {
        $this->choices = new ArrayCollection();
    }


    /**
     * @ORM\ManyToOne(targetEntity="AdditionEntity", inversedBy="additionItems")
     * @var AdditionEntity
     */
    private $addition;

    /**
     * @ORM\OneToMany(targetEntity="ChoiceEntity", mappedBy="option")
     * @var ChoiceEntity[]
     */
    private $choices;

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

    /**
     * @return ChoiceEntity
     */
    public function getChoices() {
        return $this->choices;
    }

    /**
     * @param ChoiceEntity $choice
     */
    public function addChoice($choice) {
        $choice->setOption($this);
    }

    /**
     * @param ChoiceEntity $choice
     */
    public function removeChoice($choice) {
        $choice->setOption(NULL);
    }

    /**
     * @param ChoiceEntity $choice
     * @internal
     */
    public function addInversedChoice($choice) {
        $this->choices->add($choice);
    }

    /**
     * @param ChoiceEntity $choices
     * @internal
     */
    public function removeInversedChoice($choices) {
        $this->choices->removeElement($choices);
    }


}