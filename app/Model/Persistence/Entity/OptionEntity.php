<?php
/**
 * Created by IntelliJ IDEA.
 * AdministratorEntity: stopka
 * Date: 12.1.17
 * Time: 0:18
 */

namespace App\Model\Persistence\Entity;

use App\Model\Persistence\Attribute\ISortableEntity;
use App\Model\Persistence\Attribute\TCapacityAttribute;
use App\Model\Persistence\Attribute\TIdentifierAttribute;
use App\Model\Persistence\Attribute\TInternalInfoAttribute;
use App\Model\Persistence\Attribute\TNameAttribute;
use App\Model\Persistence\Attribute\TOccupancyIconAttribute;
use App\Model\Persistence\Attribute\TPositionAttribute;
use App\Model\Persistence\Attribute\TPriceAttribute;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Jedna z voleb v addtion
 * @package App\Model\Entities
 * @ORM\Entity
 */
class OptionEntity extends BaseEntity implements ISortableEntity {
    use TIdentifierAttribute, TPositionAttribute, TNameAttribute, TCapacityAttribute, TPriceAttribute, TInternalInfoAttribute, TOccupancyIconAttribute;

    const AUTOSELECT_NONE = 0;
    const AUTOSELECT_ALWAYS = 1;
    const AUTOSELECT_SECONDON = 2;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @var string
     */
    private $description;

    /**
     * @ORM\Column(type="integer")
     * @var integer
     */
    private $autoSelect = self::AUTOSELECT_NONE;


    /**
     * @ORM\ManyToOne(targetEntity="AdditionEntity", inversedBy="additionItems")
     * @var AdditionEntity
     */
    private $addition;

    /**
     * @ORM\OneToMany(targetEntity="ChoiceEntity", mappedBy="option", cascade={"persist","remove"})
     * @var ChoiceEntity[]
     */
    private $choices;

    public function __construct() {
        parent::__construct();
        $this->choices = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getAutoSelect(): int {
        return $this->autoSelect;
    }

    /**
     * @param int $autoSelect
     */
    public function setAutoSelect(int $autoSelect): void {
        $this->autoSelect = $autoSelect;
    }

    /**
     * @return string
     */
    public function getDescription(): ?string {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription(?string $description): void {
        $this->description = $description;
    }

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