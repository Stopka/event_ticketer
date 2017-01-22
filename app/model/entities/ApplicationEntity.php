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
use App\Model\Entities\Attributes\PersonNameAttribute;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\Attributes\Identifier;

/**
 * Administrátor systému
 * @package App\Model\Entities
 * @ORM\Entity
 */
class ApplicationEntity extends BaseEntity {
    use Identifier, PersonNameAttribute, BirthDateAttribute, BirthCode, AddressAttribute, GenderAttribute;

    const STATE_WAITING = 0;
    const STATE_RESERVED = 1;
    const STATE_FULFILLED = 2;
    const STATE_CANCELLED = 3;

    const GENDER_MALE = 0;
    const GENDER_FEMALE = 1;

    /**
     * @ORM\ManyToMany(targetEntity="OptionEntity"))
     * @var OptionEntity[]
     */
    private $options;

    /**
     * @ORM\ManyToOne(targetEntity="OrderEntity", inversedBy="applications")
     * @var OrderEntity
     */
    private $order;

    /**
     * @ORM\Column(type="boolean")
     * @var boolean
     */
    private $deposited = false;

    /**
     * @ORM\Column(type="boolean")
     * @var boolean
     */
    private $payed = false;

    /**
     * @ORM\Column(type="boolean")
     * @var boolean
     */
    private $signed = false;

    /**
     * @ORM\Column(type="boolean")
     * @var boolean
     */
    private $invoiced = false;

    /**
     * @ORM\Column(type="integer")
     * @var integer
     */
    private $state = self::STATE_WAITING;

    public function __construct() {
        $this->options = new ArrayCollection();
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
        $this->options->add($option);
    }

    /**
     * @param OptionEntity $option
     */
    public function removeOption($option) {
        $this->options->removeElement($option);
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
     * @return bool
     */
    public function isDeposited() {
        return $this->deposited;
    }

    /**
     * @param bool $deposited
     */
    public function setDeposited($deposited) {
        $this->deposited = $deposited;
        $this->updateState();
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
    public function setPayed($payed) {
        $this->payed = $payed;
        $this->updateState();
    }

    /**
     * @return bool
     */
    public function isSigned() {
        return $this->signed;
    }

    /**
     * @param bool $signed
     */
    public function setSigned($signed) {
        $this->signed = $signed;
        $this->updateState();
    }

    /**
     * @return bool
     */
    public function isInvoiced() {
        return $this->invoiced;
    }

    /**
     * @param bool $invoiced
     */
    public function setInvoiced($invoiced) {
        $this->invoiced = $invoiced;
        $this->updateState();
    }

    /**
     * @return int
     */
    public function getState() {
        return $this->state;
    }
    
    public static function getStatesReserved(){
        return [self::STATE_RESERVED,self::STATE_FULFILLED];
    }

    public static function getStatesNotIssued(){
        return [self::STATE_CANCELLED];
    }
    
    public function cancelApplication(){
        $this->state = self::STATE_CANCELLED;
    }
    
    public function updateState() {
        if($this->state == self::STATE_CANCELLED){
            return;
        }
        if(!$this->isPayed()&&(
            ($this->isSigned()&&$this->isDeposited()&&!$this->isInvoiced())||
            $this->isInvoiced()
        )){
            $this->state = self::STATE_RESERVED;
            return;
        }
        if(($this->isSigned()&&$this->isDeposited()&&$this->isPayed())||
            ($this->isInvoiced()&&$this->isPayed())){
            $this->state = self::STATE_FULFILLED;
            return;
        }
        $this->state = self::STATE_WAITING;
    }
}