<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 12.1.17
 * Time: 22:42
 */

namespace App\Model\Persistence\Attribute;

use App\Model\Persistence\Dao\IOrder;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping as ORM;

trait TNumberAttribute {

    /**
     * @ORM\Column(type="integer")
     * @var integer
     */
    private $number;

    private static $nextNumber;

    abstract function getLastNumberSearchCriteria(): array;

    function setNextNumber(EntityManager $entityManager): int {
        $repository = $entityManager->getRepository(self::class);
        if(!self::$nextNumber){
            $entity = $repository->findOneBy($this->getLastNumberSearchCriteria(), ['number'=>IOrder::ORDER_DESC]);
            if (!$entity) {
                self::$nextNumber = 1;
            } else {
                self::$nextNumber = $entity->getNumber() + 1;
            }
        }
        $this->setNumber(self::$nextNumber);
        self::$nextNumber++;
        return $this->getNumber();
    }

    /**
     * @return int
     */
    public function getNumber(): int {
        return $this->number;
    }

    /**
     * @param int $number
     */
    protected function setNumber(int $number): void {
        $this->number = $number;
    }
}