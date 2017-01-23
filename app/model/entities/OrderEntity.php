<?php
/**
 * Created by IntelliJ IDEA.
 * AdministratorEntity: stopka
 * Date: 12.1.17
 * Time: 0:18
 */

namespace App\Model\Entities;

use App\Model\Entities\Attributes\EmailAttribute;
use App\Model\Entities\Attributes\GuidAttribute;
use App\Model\Entities\Attributes\IdentifierAttribute;
use App\Model\Entities\Attributes\PersonNameAttribute;
use App\Model\Entities\Attributes\PhoneAttribute;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Administrátor systému
 * @package App\Model\Entities
 * @ORM\Entity
 */
class OrderEntity extends BaseEntity {
    use IdentifierAttribute, GuidAttribute, PersonNameAttribute, EmailAttribute, PhoneAttribute;

    const STATE_ORDER = 0;
    const STATE_WAITING = 1;
    const STATE_SUBSTITUTE = 2;

    /**
     * OrderEntity constructor.
     * @param bool $substitute
     */
    public function __construct($substitute = false) {
        $this->applications = new ArrayCollection();
        $this->created = new \DateTime();
        if($substitute){
            $this->setState(self::STATE_SUBSTITUTE);
        }
        $this->generateGuid();
    }

    /**
     * @ORM\Column(type="integer")
     * @var int
     */
    private $state = self::STATE_ORDER;

    /**
     * @ORM\OneToMany(targetEntity="ApplicationEntity", mappedBy="order"))
     * @var ApplicationEntity[]
     */
    private $applications;

    /**
     * @ORM\ManyToOne(targetEntity="EventEntity", inversedBy="orders")
     * @var EventEntity
     */
    private $event;

    /**
     * @ORM\ManyToOne(targetEntity="EarlyEntity", inversedBy="orders")
     * @var EarlyEntity
     */
    private $early;

    /**
     * @ORM\Column(type="datetime")
     * @var \DateTime
     */
    private $created;

    /**
     * @return ApplicationEntity[]
     */
    public function getApplications() {
        return $this->applications;
    }

    /**
     * @param ApplicationEntity $application
     */
    public function addApplication($application) {
        $application->setOrder($this);
    }

    /**
     * @param ApplicationEntity $application
     */
    public function removeApplication($application) {
        $application->setOrder(NULL);
    }

    /**
     * @param ApplicationEntity $application
     * @internal
     */
    public function addInversedApplication($application) {
        $this->applications->add($application);
    }

    /**
     * @param ApplicationEntity $application
     * @internal
     */
    public function removeInversedApplication($application) {
        $this->applications->removeElement($application);
    }

    /**
     * @return EventEntity
     */
    public function getEvent() {
        return $this->event;
    }

    /**
     * @param EventEntity|NULL $event
     */
    public function setEvent($event) {
        if($this->event){
            $event->removeIversedOrder($this);
        }
        $this->event = $event;
        if($event) {
            $event->addIversedOrder($this);
        }
    }

    /**
     * @return EarlyEntity
     */
    public function getEarly() {
        return $this->early;
    }

    /**
     * @param EarlyEntity $early
     */
    public function setEarly($early) {
        $this->early = $early;
    }

    /**
     * @return \DateTime
     */
    public function getCreated() {
        return $this->created;
    }

    /**
     * @return int
     */
    public function getState() {
        return $this->state;
    }

    /**
     * @return integer[]
     */
    public static function getSubstituteStates(){
        return [self::STATE_SUBSTITUTE,self::STATE_WAITING];
    }

    /**
     * @param int $state
     */
    public function setState($state) {
        $this->state = $state;
    }

}