<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Entities;

use Ticketer\Model\Database\Attributes\TIdentifierAttribute;
use Doctrine\ORM\Mapping as ORM;

/**
 * Provedená volba v přihlášce
 * @ORM\Entity
 */
class ChoiceEntity extends BaseEntity
{
    /**
     * @ORM\ManyToOne(targetEntity="ApplicationEntity", inversedBy="choices")
     * @var ApplicationEntity|null
     */
    private $application;

    /**
     * @ORM\ManyToOne(targetEntity="OptionEntity", inversedBy="choices")
     * @var OptionEntity|null
     */
    private $option;


    /**
     * @ORM\Column(type="boolean")
     * @var boolean
     */
    private $payed = false;

    /**
     * @return ApplicationEntity
     */
    public function getApplication(): ?ApplicationEntity
    {
        return $this->application;
    }

    /**
     * @param ApplicationEntity $application
     * @return $this
     */
    public function setApplication(?ApplicationEntity $application): self
    {
        if (null !== $this->application) {
            $this->application->removeInversedChoice($this);
        }
        $this->application = $application;
        if (null !== $application) {
            $application->addInversedChoice($this);
        }

        return $this;
    }

    /**
     * @return OptionEntity
     */
    public function getOption(): ?OptionEntity
    {
        return $this->option;
    }

    /**
     * @param OptionEntity|null $option
     * @return ChoiceEntity
     */
    public function setOption(?OptionEntity $option): self
    {
        if (null !== $this->option) {
            $this->option->removeInversedChoice($this);
        }
        $this->option = $option;
        if (null !== $option) {
            $option->addInversedChoice($this);
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function isPayed(): bool
    {
        return $this->payed;
    }

    /**
     * @param bool $payed
     */
    public function setPayed(bool $payed = true): void
    {
        $this->payed = $payed;
        if (null === $this->application) {
            return;
        }
        $this->application->updateState();
    }

    public function inversePayed(): void
    {
        $this->setPayed(!$this->isPayed());
    }
}
