<?php
/**
 * Created by IntelliJ IDEA.
 * AdministratorEntity: stopka
 * Date: 12.1.17
 * Time: 0:18
 */

namespace App\Model\Entities;

use App\Model\Entities\Attributes\IdentifierAttribute;
use App\Model\Entities\Attributes\StartDateAttribute;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Administrátor systému
 * @package App\Model\Entities
 * @ORM\Entity
 */
class EarlyWaveEntity extends BaseEntity {
    use IdentifierAttribute, StartDateAttribute;

    public function __construct() {
        $this->earlies = new ArrayCollection();
    }

    /**
     * @ORM\ManyToOne(targetEntity="EventEntity", inversedBy="earlyWaves")
     * @var EventEntity
     */
    private $event;

    /**
     * @ORM\OneToMany(targetEntity="EarlyEntity", mappedBy="earlyWave")
     * @var EarlyEntity[]
     */
    private $earlies;

    /**
     * @ORM\Column(type="boolean")
     * @var boolean
     */
    private $inviteSent = false;

    /**
     * @return EventEntity
     */
    public function getEvent() {
        return $this->event;
    }

    /**
     * @param EventEntity $event
     */
    public function setEvent($event) {
        if($this->event){
            $this->event->removeInversedEarlyWave($this);
        }
        $this->event = $event;
        if($event) {
            $event->addInversedEarlyWave($this);
        }
    }

    /**
     * @return EarlyEntity[]
     */
    public function getEarlies() {
        return $this->earlies;
    }

    /**
     * @param EarlyEntity $early
     */
    public function addEarly($early) {
        $early->setEarlyWave($this);
    }

    /**
     * @param EarlyEntity $early
     */
    public function removeEarly($early) {
        $early->setEarlyWave(NULL);
    }

    /**
     * @param EarlyEntity $early
     * @internal
     */
    public function addInversedEarly($early) {
        $this->earlies->add($early);
    }

    /**
     * @param EarlyEntity $early
     * @internal
     */
    public function removeInversedEarly($early) {
        $this->earlies->removeElement($early);
    }

    /**
     * @return bool
     */
    public function isReadyToRegister(){
        $event = $this->getEvent();
        if(!$event){
            return false;
        }
        return $event->isActive()&&$this->isStarted();
    }

    /**
     * @return bool
     */
    public function isInviteSent() {
        return $this->inviteSent;
    }

    /**
     * @param bool $inviteSent
     */
    public function setInviteSent($inviteSent = true) {
        $this->inviteSent = $inviteSent;
    }


}