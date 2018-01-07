<?php
/**
 * Created by IntelliJ IDEA.
 * AdministratorEntity: stopka
 * Date: 12.1.17
 * Time: 0:18
 */

namespace App\Model\Persistence\Entity;

use App\Model\Persistence\Attribute\ISortableEntity;
use App\Model\Persistence\Attribute\TIdentifierAttribute;
use App\Model\Persistence\Attribute\TNameAttribute;
use App\Model\Persistence\Attribute\TPositionAttribute;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Přídavek k přihlášce (Přihláška, Faktura, Doprava, Tričko...)
 * @package App\Model\Entities
 * @ORM\Entity
 */
class AdditionEntity extends BaseEntity implements ISortableEntity {
    use TPositionAttribute, TIdentifierAttribute, TNameAttribute;

    public function __construct() {
        $this->options = new ArrayCollection();
    }

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @var integer
     */
    private $requiredForState;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @var integer
     */
    private $enoughForState;

    /**
     * @ORM\Column(type="integer")
     * @var integer
     */
    private $minimum = 1;

    /**
     * @ORM\Column(type="integer")
     * @var integer
     */
    private $maximum = 1;

    /**
     * @ORM\Column(type="boolean")
     * @var boolean
     */
    private $visible = true;

    /**
     * @ORM\Column(type="boolean")
     * @var boolean
     */
    private $hidden = false;

    /**
     * @ORM\ManyToOne(targetEntity="EventEntity", inversedBy="additions")
     * @var EventEntity
     */
    private $event;

    /**
     * @ORM\OneToMany(targetEntity="OptionEntity", mappedBy="addition")
     * @ORM\OrderBy({"position" = "ASC"})
     * @var OptionEntity[]
     */
    private $options;

    /**
     * @return EventEntity
     */
    public function getEvent(): ?EventEntity {
        return $this->event;
    }

    /**
     * @param EventEntity $event
     * @return $this
     */
    public function setEvent(?EventEntity $event) {
        if ($this->event) {
            $event->event->removeInversedAddition($this);
        }
        $this->event = $event;
        if ($event) {
            $event->addInversedAddition($this);
        }
        return $this;
    }

    /**
     * @return OptionEntity[]
     */
    public function getOptions(): array {
        return $this->options->toArray();
    }

    /**
     * @param OptionEntity $option
     */
    public function addOption(OptionEntity $option): void {
        $option->setAddtition($this);
    }

    /**
     * @param OptionEntity $option
     */
    public function removeOption(OptionEntity $option): void {
        $option->setAddtition(NULL);
    }

    /**
     * @param OptionEntity $option
     * @internal
     */
    public function addInversedOption(OptionEntity $option): void {
        $this->options->add($option);
    }

    /**
     * @param OptionEntity $option
     * @internal
     */
    public function removeInversedOption(OptionEntity $option): void {
        $this->options->removeElement($option);
    }

    /**
     * @return int
     */
    public function getMinimum(): int {
        return $this->minimum;
    }

    /**
     * @param int $minimum
     */
    public function setMinimum(int $minimum): void {
        $this->minimum = $minimum;
    }

    /**
     * @return int
     */
    public function getMaximum(): int {
        return $this->maximum;
    }

    /**
     * @param int $maximum
     */
    public function setMaximum(int $maximum): void {
        $this->maximum = $maximum;
    }

    /**
     * @return int
     */
    public function getRequiredForState(): ?int {
        return $this->requiredForState;
    }

    /**
     * @param int $requiredForState
     */
    public function setRequiredForState(?int $requiredForState): void {
        $this->requiredForState = $requiredForState;
    }

    /**
     * @return bool
     */
    public function isVisible(): bool {
        return $this->visible;
    }

    /**
     * @param bool $visible
     */
    public function setVisible(bool $visible): void {
        $this->visible = $visible;
    }

    /**
     * @return int
     */
    public function getEnoughForState(): ?int {
        return $this->enoughForState;
    }

    /**
     * @param int $enoughForState
     */
    public function setEnoughForState(?int $enoughForState): void {
        $this->enoughForState = $enoughForState;
    }

    /**
     * @return bool
     */
    public function isHidden(): bool {
        return $this->hidden;
    }

    /**
     * @param bool $hidden
     */
    public function setHidden(bool $hidden) {
        $this->hidden = $hidden;
    }

}