<?php
/**
 * Created by IntelliJ IDEA.
 * AdministratorEntity: stopka
 * Date: 12.1.17
 * Time: 0:18
 */

namespace App\Model\Persistence\Entity;

use App\Model\Persistence\Attribute\TCapacityAttribute;
use App\Model\Persistence\Attribute\TIdentifierAttribute;
use App\Model\Persistence\Attribute\TInternalInfoAttribute;
use App\Model\Persistence\Attribute\TNameAttribute;
use App\Model\Persistence\Attribute\TOccupancyIconAttribute;
use App\Model\Persistence\Attribute\TPriceAttribute;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Jedna z voleb v addtion
 * @package App\Model\Entities
 * @ORM\Entity
 */
class OptionEntity extends BaseEntity {
    use TIdentifierAttribute, TNameAttribute, TCapacityAttribute, TPriceAttribute, TInternalInfoAttribute, TOccupancyIconAttribute;

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
    public function getAddition(): ?AdditionEntity {
        return $this->addition;
    }

    /**
     * @param AdditionEntity $addition
     * @return $this
     */
    public function setAddtition(?AdditionEntity $addition): self {
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
     * @return ChoiceEntity[]
     */
    public function getChoices(): array {
        return $this->choices->toArray();
    }

    /**
     * @param ChoiceEntity $choice
     */
    public function addChoice(ChoiceEntity $choice): void {
        $choice->setOption($this);
    }

    /**
     * @param ChoiceEntity $choice
     */
    public function removeChoice(ChoiceEntity $choice): void {
        $choice->setOption(NULL);
    }

    /**
     * @param ChoiceEntity $choice
     * @internal
     */
    public function addInversedChoice(ChoiceEntity $choice): void {
        $this->choices->add($choice);
    }

    /**
     * @param ChoiceEntity $choices
     * @internal
     */
    public function removeInversedChoice(ChoiceEntity $choices): void {
        $this->choices->removeElement($choices);
    }


}