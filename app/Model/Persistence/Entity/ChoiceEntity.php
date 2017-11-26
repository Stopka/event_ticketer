<?php
/**
 * Created by IntelliJ IDEA.
 * AdministratorEntity: stopka
 * Date: 12.1.17
 * Time: 0:18
 */

namespace App\Model\Persistence\Entity;

use App\Model\Persistence\Attribute\TIdentifierAttribute;
use Doctrine\ORM\Mapping as ORM;

/**
 * Provedená volba v přihlášce
 * @package App\Model\Entities
 * @ORM\Entity
 */
class ChoiceEntity extends BaseEntity {
    use TIdentifierAttribute;

    /**
     * @ORM\ManyToOne(targetEntity="ApplicationEntity", inversedBy="choices")
     * @var ApplicationEntity
     */
    private $application;

    /**
     * @ORM\ManyToOne(targetEntity="OptionEntity", inversedBy="choices")
     * @var OptionEntity
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
    public function getApplication(): ?ApplicationEntity {
        return $this->application;
    }

    /**
     * @param ApplicationEntity $application
     * @return $this
     */
    public function setApplication(?ApplicationEntity $application): self {
        if($this->application){
            $this->application->removeInversedChoice($this);
        }
        $this->application = $application;
        if($application) {
            $application->addInversedChoice($this);
        }
        return $this;
    }

    /**
     * @return OptionEntity
     */
    public function getOption(): ?OptionEntity {
        return $this->option;
    }

    /**
     * @param OptionEntity $option
     */
    public function setOption(OptionEntity $option): self {
        if($this->option){
            $this->option->removeInversedChoice($this);
        }
        $this->option = $option;
        if($option) {
            $option->addInversedChoice($this);
        }
        return $this;
    }

    /**
     * @return bool
     */
    public function isPayed(): bool {
        return $this->payed;
    }

    /**
     * @param bool $payed
     */
    public function setPayed(bool $payed = true): void {
        $this->payed = $payed;
        $this->application->updateState();
    }

    public function inversePayed(): void {
        $this->setPayed(!$this->isPayed());
    }



}