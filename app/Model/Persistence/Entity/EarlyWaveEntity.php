<?php
/**
 * Created by IntelliJ IDEA.
 * AdministratorEntity: stopka
 * Date: 12.1.17
 * Time: 0:18
 */

namespace App\Model\Persistence\Entity;

use App\Model\Persistence\Attribute\TIdentifierAttribute;
use App\Model\Persistence\Attribute\TNameAttribute;
use App\Model\Persistence\Attribute\TStartDateAttribute;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Seznam uživatelů s přednostím právem k přihláškám , kteří mají společné datum spuštění
 * @package App\Model\Entities
 * @ORM\Entity
 */
class EarlyWaveEntity extends BaseEntity {
    use TIdentifierAttribute, TStartDateAttribute, TNameAttribute;

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
    public function getEvent(): ?EventEntity {
        return $this->event;
    }

    /**
     * @param EventEntity $event
     */
    public function setEvent(EventEntity $event): void {
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
    public function getEarlies(): array {
        return $this->earlies->toArray();
    }

    /**
     * @param EarlyEntity $early
     */
    public function addEarly(EarlyEntity $early): void {
        $early->setEarlyWave($this);
    }

    /**
     * @param EarlyEntity $early
     */
    public function removeEarly(EarlyEntity $early): void {
        $early->setEarlyWave(NULL);
    }

    /**
     * @param EarlyEntity $early
     * @internal
     */
    public function addInversedEarly(EarlyEntity $early): void {
        $this->earlies->add($early);
    }

    /**
     * @param EarlyEntity $early
     * @internal
     */
    public function removeInversedEarly(EarlyEntity $early): void {
        $this->earlies->removeElement($early);
    }

    /**
     * @return bool
     */
    public function isReadyToRegister(): bool{
        $event = $this->getEvent();
        if(!$event){
            return false;
        }
        return $event->isActive()&&$this->isStarted();
    }

    /**
     * @return bool
     */
    public function isInviteSent(): bool {
        return $this->inviteSent;
    }

    /**
     * @param bool $inviteSent
     */
    public function setInviteSent(bool $inviteSent = true): void {
        $this->inviteSent = $inviteSent;
    }

}