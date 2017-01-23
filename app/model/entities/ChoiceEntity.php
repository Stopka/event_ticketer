<?php
/**
 * Created by IntelliJ IDEA.
 * AdministratorEntity: stopka
 * Date: 12.1.17
 * Time: 0:18
 */

namespace App\Model\Entities;

use App\Model\Entities\Attributes\IdentifierAttribute;
use Doctrine\ORM\Mapping as ORM;

/**
 * Administrátor systému
 * @package App\Model\Entities
 * @ORM\Entity
 */
class ChoiceEntity extends BaseEntity {
    use IdentifierAttribute;

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
    public function getApplication() {
        return $this->application;
    }

    /**
     * @param ApplicationEntity $application
     */
    public function setApplication($application) {
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
    public function getOption() {
        return $this->option;
    }

    /**
     * @param OptionEntity $option
     */
    public function setOption($option) {
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
    public function isPayed() {
        return $this->payed;
    }

    /**
     * @param bool $payed
     */
    public function setPayed($payed = true) {
        $this->payed = $payed;
        $this->application->updateState();
    }

    public function inversePayed() {
        $this->setPayed(!$this->isPayed());
    }



}