<?php
/**
 * Created by IntelliJ IDEA.
 * AdministratorEntity: stopka
 * Date: 12.1.17
 * Time: 0:18
 */

namespace App\Model\Entities;

use App\Model\Entities\Attributes\AddressAttribute;
use App\Model\Entities\Attributes\BirthCode;
use App\Model\Entities\Attributes\BirthDateAttribute;
use App\Model\Entities\Attributes\GenderAttribute;
use App\Model\Entities\Attributes\IdentifierAttribute;
use App\Model\Entities\Attributes\PersonNameAttribute;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Administrátor systému
 * @package App\Model\Entities
 * @ORM\Entity
 */
class ApplicationEntity extends BaseEntity {
    use IdentifierAttribute, PersonNameAttribute, BirthDateAttribute, BirthCode, AddressAttribute, GenderAttribute;

    const STATE_WAITING = 1;
    const STATE_RESERVED = 2;
    const STATE_FULFILLED = 3;
    const STATE_CANCELLED = 4;

    const GENDER_MALE = 0;
    const GENDER_FEMALE = 1;

    /**
     * @ORM\OneToMany(targetEntity="ChoiceEntity", mappedBy="application"))
     * @var ChoiceEntity[]
     */
    private $choices;

    /**
     * @ORM\ManyToOne(targetEntity="OrderEntity", inversedBy="applications")
     * @var OrderEntity
     */
    private $order;

    /**
     * @ORM\Column(type="integer")
     * @var integer
     */
    private $state = self::STATE_WAITING;

    public function __construct() {
        $this->choices = new ArrayCollection();
    }

    /**
     * @return ChoiceEntity[]
     */
    public function getChoices() {
        return $this->choices;
    }

    /**
     * @param ChoiceEntity $choice
     */
    public function addChoice($choice) {
        $choice->setApplication($this);
    }

    /**
     * @param ChoiceEntity $choice
     */
    public function removeChoice($choice) {
        $choice->setApplication(NULL);
    }

    /**
     * @param ChoiceEntity $choice
     * @internal
     */
    public function addInversedChoice($choice) {
        $this->choices->add($choice);
    }

    /**
     * @param ChoiceEntity $choice
     * @internal
     */
    public function removeInversedChoice($choice) {
        $this->choices->removeElement($choice);
    }

    /**
     * @return OrderEntity
     */
    public function getOrder() {
        return $this->order;
    }

    /**
     * @param OrderEntity $order
     */
    public function setOrder($order) {
        if ($this->order) {
            $this->order->removeInversedApplication($this);
        }
        $this->order = $order;
        if ($order) {
            $order->addInversedApplication($this);
        }
    }

    /**
     * @return int
     */
    public function getState() {
        return $this->state;
    }

    public static function getStatesReserved() {
        return [self::STATE_RESERVED, self::STATE_FULFILLED];
    }

    public static function getStatesNotIssued() {
        return [self::STATE_CANCELLED];
    }

    public function cancelApplication() {
        $this->state = self::STATE_CANCELLED;
    }

    public function updateState() {
        if (in_array($this->state, self::getStatesNotIssued())) {
            return;
        }
        $this->state = self::STATE_WAITING;
        $event = $this->getOrder()->getEvent();
        $required_states = [];
        $required_additions = [];
        $enough = [];
        foreach ($event->getAdditions() as $addition) {
            if ($state = $addition->getEnoughForState()) {
                $enough[$addition->getId()] = $state;
            }
            if ($min_state = $addition->getRequiredForState()) {
                for ($state = $min_state; $state <= self::STATE_FULFILLED; $state++) {
                    if (!isset($required_states[$state])) {
                        $required_states[$state] = [];
                    }
                    array_push($required_states[$state], $addition->getId());
                    if (!isset($required_additions[$addition->getId()])) {
                        $required_additions[$addition->getId()] = [];
                    }
                    array_push($required_additions[$addition->getId()], $state);
                }
            }
        }
        foreach ($this->getChoices() as $choice) {
            $additionId = $choice->getOption()->getAddition()->getId();
            if (isset($enough[$additionId]) && $choice->isPayed() && $enough[$additionId] > $this->state) {
                $this->state = $enough[$additionId];
            }
            if (isset($required_additions[$additionId]) && $choice->isPayed()) {
                foreach ($required_additions[$additionId] as $state) {
                    $index = array_search($additionId, $required_states[$state]);
                    unset($required_states[$state][$index]);
                }
            }
        }
        for ($state = self::STATE_WAITING; $state <= self::STATE_FULFILLED; $state++) {
            if ($this->state > $state) {
                continue;
            }
            if (!isset($required_states[$state])) {
                continue;
            }
            if (count($required_states[$state]) > 0) {
                continue;
            }
            $this->state = $state;
        }
    }
}