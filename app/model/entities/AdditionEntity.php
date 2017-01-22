<?php
/**
 * Created by IntelliJ IDEA.
 * AdministratorEntity: stopka
 * Date: 12.1.17
 * Time: 0:18
 */

namespace App\Model\Entities;

use App\Model\Entities\Attributes\NameAttribute;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\Attributes\Identifier;

/**
 * Administrátor systému
 * @package App\Model\Entities
 * @ORM\Entity
 */
class AdditionEntity extends BaseEntity {
    use Identifier, NameAttribute;

    public function __construct() {
        $this->options = new ArrayCollection();
    }

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
    private $forChild = false;

    /**
     * @ORM\ManyToOne(targetEntity="EventEntity", inversedBy="additions")
     * @var EventEntity
     */
    private $event;

    /**
     * @ORM\OneToMany(targetEntity="OptionEntity", mappedBy="addition")
     * @var OptionEntity[]
     */
    private $options;

    /**
     * @return EventEntity
     */
    public function getEvent() {
        return $this->event;
    }

    /**
     * @param EventEntity $event
     * @return $this
     */
    public function setEvent($event) {
        if($this->event){
            $event->event->removeInversedAddition($this);
        }
        $this->event = $event;
        if($event) {
            $event->addInversedAddition($this);
        }
        return $this;
    }

    /**
     * @return OptionEntity[]
     */
    public function getOptions() {
        return $this->options;
    }

    /**
     * @param OptionEntity $option
     */
    public function addOption($option) {
        $option->setAddtition($this);
    }

    /**
     * @param OptionEntity $option
     */
    public function removeOption($option) {
        $option->setAddtition(NULL);
    }

    /**
     * @param OptionEntity $option
     * @internal
     */
    public function addInversedOption($option) {
        $this->options->add($option);
    }

    /**
     * @param OptionEntity $option
     * @internal
     */
    public function removeInversedOption($option) {
        $this->options->removeElement($option);
    }

    /**
     * @return int
     */
    public function getMinimum() {
        return $this->minimum;
    }

    /**
     * @param int $minimum
     */
    public function setMinimum($minimum) {
        $this->minimum = $minimum;
    }

    /**
     * @return int
     */
    public function getMaximum() {
        return $this->maximum;
    }

    /**
     * @param int $maximum
     */
    public function setMaximum($maximum) {
        $this->maximum = $maximum;
    }



}