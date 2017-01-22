<?php
/**
 * Created by IntelliJ IDEA.
 * AdministratorEntity: stopka
 * Date: 12.1.17
 * Time: 0:18
 */

namespace App\Model\Entities;

use App\Model\Entities\Attributes\AddressAttribute;
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
class EarlyEntity extends BaseEntity {
    use IdentifierAttribute, GuidAttribute, PersonNameAttribute, EmailAttribute, PhoneAttribute, AddressAttribute;

    public function __construct() {
        $this->orders = new ArrayCollection();
        $this->generateGuid();
    }


    /**
     * @ORM\ManyToOne(targetEntity="EarlyWaveEntity", inversedBy="earlies")
     * @var EarlyWaveEntity
     */
    private $earlyWave;

    /**
     * @ORM\OneToMany(targetEntity="OrderEntity", mappedBy="early")
     * @var OrderEntity[]|ArrayCollection
     */
    private $orders;

    /**
     * @return EarlyWaveEntity
     */
    public function getEarlyWave() {
        return $this->earlyWave;
    }

    /**
     * @param EarlyWaveEntity $earlyWave
     */
    public function setEarlyWave($earlyWave) {
        if($this->earlyWave){
            $this->earlyWave->removeInversedEarly($this);
        }
        $this->earlyWave = $earlyWave;
        if($earlyWave) {
            $earlyWave->addInversedEarly($this);
        }
    }

    /**
     * @return OrderEntity[]
     */
    public function getOrders() {
        return $this->orders;
    }

    /**
     * @param OrderEntity $order
     */
    public function addOrder($order) {
        $order->setEarly($this);
    }

    /**
     * @param OrderEntity $order
     * @internal
     */
    public function addIversedOrder($order) {
        $this->orders->add($order);
    }

    /**
     * @param OrderEntity $order
     */
    public function removeOrder($order) {
        $order->setEarly(NULL);
    }

    /**
     * @param OrderEntity $order
     * @internal
     */
    public function removeIversedOrder($order) {
        $this->orders->removeElement($order);
    }

    public function __clone() {
        $this->resetId();
        $this->generateGuid();
    }

    /**
     * @return bool
     */
    public function isReadyToRegister(){
        $wave = $this->getEarlyWave();
        if(!$wave){
            return false;
        }
        return $wave->isReadyToRegister();
    }

}