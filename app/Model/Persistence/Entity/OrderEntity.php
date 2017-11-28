<?php
/**
 * Created by IntelliJ IDEA.
 * AdministratorEntity: stopka
 * Date: 12.1.17
 * Time: 0:18
 */

namespace App\Model\Persistence\Entity;

use App\Model\Persistence\Attribute\TEmailAttribute;
use App\Model\Persistence\Attribute\TIdentifierAttribute;
use App\Model\Persistence\Attribute\TPersonNameAttribute;
use App\Model\Persistence\Attribute\TPhoneAttribute;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Objednávka, seskupení naráz vydaných přihlášek
 * @package App\Model\Entities
 * @ORM\Entity
 */
class OrderEntity extends BaseEntity {
    use TIdentifierAttribute, TPersonNameAttribute, TEmailAttribute, TPhoneAttribute;

    const STATE_ORDER = 0;

    /**
     * OrderEntity constructor
     */
    public function __construct() {
        $this->applications = new ArrayCollection();
        $this->created = new \DateTime();
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
     * @ORM\OneToOne(targetEntity="SubstituteEntity", inversedBy="order")
     * @var SubstituteEntity
     */
    private $substitute;

    /**
     * @ORM\Column(type="datetime")
     * @var \DateTime
     */
    private $created;

    /**
     * @return ApplicationEntity[]
     */
    public function getApplications(): array {
        return $this->applications->toArray();
    }

    /**
     * @param ApplicationEntity $application
     */
    public function addApplication(ApplicationEntity $application): void {
        $application->setOrder($this);
    }

    /**
     * @param ApplicationEntity $application
     */
    public function removeApplication(ApplicationEntity $application): void {
        $application->setOrder(NULL);
    }

    /**
     * @param ApplicationEntity $application
     * @internal
     */
    public function addInversedApplication(ApplicationEntity $application): void {
        $this->applications->add($application);
    }

    /**
     * @param ApplicationEntity $application
     * @internal
     */
    public function removeInversedApplication(ApplicationEntity $application): void {
        $this->applications->removeElement($application);
    }

    /**
     * @return EventEntity
     */
    public function getEvent(): ?EventEntity {
        return $this->event;
    }

    /**
     * @param EventEntity|NULL $event
     */
    public function setEvent(?EventEntity $event) {
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
    public function getEarly(): EarlyEntity {
        return $this->early;
    }

    /**
     * @param EarlyEntity $early
     */
    public function setEarly(?EarlyEntity $early): void {
        $this->early = $early;
    }

    /**
     * @return \DateTime
     */
    public function getCreated(): \DateTime {
        return $this->created;
    }

    /**
     * @return int
     */
    public function getState(): int {
        return $this->state;
    }

    /**
     * @param int $state
     */
    public function setState(int $state): void {
        $this->state = $state;
    }

    /**
     * @return SubstituteEntity
     */
    public function getSubstitute(): ?SubstituteEntity {
        return $this->substitute;
    }

    /**
     * @param SubstituteEntity $substitute
     */
    public function setSubstitute(?SubstituteEntity $substitute): void {
        if($this->substitute){
            $this->substitute->setInversedOrder(NULL);
        }
        $this->substitute = $substitute;
        if($this->substitute){
            $this->substitute->setInversedOrder($this);
        }
    }

}