<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Entities;

use Ticketer\Model\Database\Attributes\TCapacityAttribute;
use Ticketer\Model\Database\Attributes\TIdentifierAttribute;
use Ticketer\Model\Database\Attributes\TInternalInfoAttribute;
use Ticketer\Model\Database\Attributes\TNameAttribute;
use Ticketer\Model\Database\Attributes\TOccupancyIconAttribute;
use Ticketer\Model\Database\Attributes\TPositionAttribute;
use Ticketer\Model\Database\Attributes\TPriceAttribute;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Ticketer\Model\Database\Enums\OptionAutoselectEnum;

/**
 * Jedna z voleb v addtion
 * @package App\Model\Entities
 * @ORM\Entity
 */
class OptionEntity extends BaseEntity implements SortableEntityInterface
{
    use TIdentifierAttribute;
    use TPositionAttribute;
    use TNameAttribute;
    use TCapacityAttribute;
    use TPriceAttribute;
    use TInternalInfoAttribute;
    use TOccupancyIconAttribute;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @var string|null
     */
    private $description;

    /**
     * @ORM\Column(type="option_autoselect_enum")
     * @var OptionAutoselectEnum
     */
    private OptionAutoselectEnum $autoSelect;


    /**
     * @ORM\ManyToOne(targetEntity="AdditionEntity", inversedBy="additionItems")
     * @var AdditionEntity|null
     */
    private $addition;

    /**
     * @ORM\OneToMany(targetEntity="ChoiceEntity", mappedBy="option", cascade={"persist","remove"})
     * @var ArrayCollection<int,ChoiceEntity>
     */
    private $choices;

    public function __construct()
    {
        parent::__construct();
        $this->choices = new ArrayCollection();
        $this->autoSelect = OptionAutoselectEnum::NONE();
    }

    /**
     * @return OptionAutoselectEnum
     */
    public function getAutoSelect(): OptionAutoselectEnum
    {
        return $this->autoSelect;
    }

    /**
     * @param OptionAutoselectEnum $autoSelect
     */
    public function setAutoSelect(OptionAutoselectEnum $autoSelect): void
    {
        $this->autoSelect = $autoSelect;
    }

    /**
     * @return string
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     */
    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return AdditionEntity
     */
    public function getAddition(): ?AdditionEntity
    {
        return $this->addition;
    }

    /**
     * @param AdditionEntity|null $addition
     * @return $this
     */
    public function setAddtition(?AdditionEntity $addition): self
    {
        if (null !== $this->addition) {
            $this->addition->removeInversedOption($this);
        }
        $this->addition = $addition;
        if (null !== $addition) {
            $addition->addInversedOption($this);
        }

        return $this;
    }

    /**
     * @return ChoiceEntity[]
     */
    public function getChoices(): array
    {
        return $this->choices->toArray();
    }

    /**
     * @return ChoiceEntity[]
     */
    public function getIssuedChoices(): array
    {
        return $this->choices->filter(
            function (ChoiceEntity $choiceEntity): bool {
                $application = $choiceEntity->getApplication();
                if (null !== $application) {
                    return $application->getState()->isIssued();
                }

                return false;
            }
        )->toArray();
    }

    /**
     * @param ChoiceEntity $choice
     */
    public function addChoice(ChoiceEntity $choice): void
    {
        $choice->setOption($this);
    }

    /**
     * @param ChoiceEntity $choice
     */
    public function removeChoice(ChoiceEntity $choice): void
    {
        $choice->setOption(null);
    }

    /**
     * @param ChoiceEntity $choice
     * @internal
     */
    public function addInversedChoice(ChoiceEntity $choice): void
    {
        $this->choices->add($choice);
    }

    /**
     * @param ChoiceEntity $choices
     * @internal
     */
    public function removeInversedChoice(ChoiceEntity $choices): void
    {
        $this->choices->removeElement($choices);
    }

    public function countCapacityUsage(): int
    {
        return count($this->getIssuedChoices());
    }

    public function updateCapacityFull(): void
    {
        $this->setCapacityFull(false);
    }
}
