<?php
/**
 * Created by IntelliJ IDEA.
 * AdministratorEntity: stopka
 * Date: 12.1.17
 * Time: 0:18
 */

namespace App\Model\Persistence\Entity;

use App\Model\Persistence\Attribute\TAddressAttribute;
use App\Model\Persistence\Attribute\TEmailAttribute;
use App\Model\Persistence\Attribute\TIdentifierAttribute;
use App\Model\Persistence\Attribute\TPersonNameAttribute;
use App\Model\Persistence\Attribute\TPhoneAttribute;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Uživatel s přednostním právem přihlášky
 * @package App\Model\Entities
 * @ORM\Entity
 */
class EarlyEntity extends BaseEntity {
    use TIdentifierAttribute, TPersonNameAttribute, TEmailAttribute, TPhoneAttribute, TAddressAttribute;

    public function __construct() {
        $this->orders = new ArrayCollection();
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
    public function getEarlyWave(): ?EarlyWaveEntity {
        return $this->earlyWave;
    }

    /**
     * @param EarlyWaveEntity $earlyWave
     */
    public function setEarlyWave(?EarlyWaveEntity $earlyWave): void {
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
    public function getOrders(): ArrayCollection {
        return $this->orders;
    }

    /**
     * @param OrderEntity $order
     */
    public function addOrder(OrderEntity $order): void {
        $order->setEarly($this);
    }

    /**
     * @param OrderEntity $order
     * @internal
     */
    public function addIversedOrder(OrderEntity $order): void {
        $this->orders->add($order);
    }

    /**
     * @param OrderEntity $order
     */
    public function removeOrder(OrderEntity $order): void {
        $order->setEarly(NULL);
    }

    /**
     * @param OrderEntity $order
     * @internal
     */
    public function removeIversedOrder(OrderEntity $order): void {
        $this->orders->removeElement($order);
    }

    public function __clone() {
        $this->resetId();
    }

    /**
     * @return bool
     */
    public function isReadyToRegister(): bool {
        $wave = $this->getEarlyWave();
        if(!$wave){
            return false;
        }
        return $wave->isReadyToRegister();
    }

}