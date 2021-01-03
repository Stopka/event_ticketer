<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Entities;

use Ticketer\Model\Database\Attributes\TIdentifierAttribute;
use Ticketer\Model\Database\Attributes\TNameAttribute;
use Ticketer\Model\Database\Attributes\TPositionAttribute;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Ticketer\Model\Database\Enums\AdditionVisibilityEnum;
use Ticketer\Model\Database\Enums\ApplicationStateEnum;
use Ticketer\Modules\AdminModule\Controls\Forms\Inputs\AdditionVisibilityCheckboxList;

/**
 * Přídavek k přihlášce (Přihláška, Faktura, Doprava, Tričko...)
 * @package App\Model\Entities
 * @ORM\Entity
 */
class AdditionEntity extends BaseEntity implements SortableEntityInterface
{
    use TIdentifierAttribute;
    use TPositionAttribute;
    use TNameAttribute;

    /**
     * @ORM\Column(type="application_state_enum", nullable=true)
     * @var ApplicationStateEnum|null
     */
    private ?ApplicationStateEnum $requiredForState = null;

    /**
     * @ORM\Column(type="application_state_enum", nullable=true)
     * @var ApplicationStateEnum|null
     */
    private ?ApplicationStateEnum $enoughForState = null;

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
     * @ORM\OneToOne (targetEntity="AdditionVisibilityEntity", cascade={"persist","remove"})
     * @var AdditionVisibilityEntity
     */
    private AdditionVisibilityEntity $visibility;

    /**
     * @ORM\ManyToOne(targetEntity="EventEntity", inversedBy="additions")
     * @var EventEntity|null
     */
    private $event;

    /**
     * @ORM\OneToMany(targetEntity="OptionEntity", mappedBy="addition", cascade={"persist","remove"})
     * @ORM\OrderBy({"position" = "ASC"})
     * @var ArrayCollection<int,OptionEntity>
     */
    private $options;

    public function __construct()
    {
        parent::__construct();
        $this->options = new ArrayCollection();
        $this->visibility = new AdditionVisibilityEntity();
    }

    /**
     * @return EventEntity
     */
    public function getEvent(): ?EventEntity
    {
        return $this->event;
    }

    /**
     * @param EventEntity $event
     * @return $this
     */
    public function setEvent(?EventEntity $event)
    {
        if (null !== $this->event) {
            $this->event->removeInversedAddition($this);
        }
        $this->event = $event;
        if (null !== $event) {
            $event->addInversedAddition($this);
        }

        return $this;
    }

    /**
     * @return OptionEntity[]
     */
    public function getOptions(): array
    {
        return $this->options->toArray();
    }

    /**
     * @param OptionEntity $option
     */
    public function addOption(OptionEntity $option): void
    {
        $option->setAddtition($this);
    }

    /**
     * @param OptionEntity $option
     */
    public function removeOption(OptionEntity $option): void
    {
        $option->setAddtition(null);
    }

    /**
     * @param OptionEntity $option
     * @internal
     */
    public function addInversedOption(OptionEntity $option): void
    {
        $this->options->add($option);
    }

    /**
     * @param OptionEntity $option
     * @internal
     */
    public function removeInversedOption(OptionEntity $option): void
    {
        $this->options->removeElement($option);
    }

    /**
     * @return int
     */
    public function getMinimum(): int
    {
        return $this->minimum;
    }

    /**
     * @param int $minimum
     */
    public function setMinimum(int $minimum): void
    {
        $this->minimum = $minimum;
    }

    /**
     * @return int
     */
    public function getMaximum(): int
    {
        return $this->maximum;
    }

    /**
     * @param int $maximum
     */
    public function setMaximum(int $maximum): void
    {
        $this->maximum = $maximum;
    }

    /**
     * @return ApplicationStateEnum
     */
    public function getRequiredForState(): ?ApplicationStateEnum
    {
        return $this->requiredForState;
    }

    /**
     * @param ApplicationStateEnum $requiredForState
     */
    public function setRequiredForState(?ApplicationStateEnum $requiredForState): void
    {
        $this->requiredForState = $requiredForState;
    }

    /**
     * @return AdditionVisibilityEntity
     */
    public function getVisibility(): AdditionVisibilityEntity
    {
        return $this->visibility;
    }

    /**
     * @param array<string> $visibility
     */
    public function setVisibility(array $visibility): void
    {
        $values = [];
        foreach (AdditionVisibilityCheckboxList::getItemLabels() as $key => $label) {
            $values[$key] = in_array($key, $visibility, true);
        }
        $this->visibility->setByValueArray($values);
    }

    /**
     * @return ApplicationStateEnum|null
     */
    public function getEnoughForState(): ?ApplicationStateEnum
    {
        return $this->enoughForState;
    }

    /**
     * @param ApplicationStateEnum|null $enoughForState
     */
    public function setEnoughForState(?ApplicationStateEnum $enoughForState): void
    {
        $this->enoughForState = $enoughForState;
    }
}
