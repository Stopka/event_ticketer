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

    public function __construct() {
        $this->applications = new ArrayCollection();
        $this->created = new \DateTime();
        $this->generateGuid();
    }

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

}