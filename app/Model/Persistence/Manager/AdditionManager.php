<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 20.1.17
 * Time: 22:09
 */

namespace App\Model\Persistence\Manager;


use App\Model\Exception\NotFoundException;
use App\Model\Persistence\Dao\AdditionDao;
use App\Model\Persistence\Dao\CurrencyDao;
use App\Model\Persistence\Dao\TDoctrineEntityManager;
use App\Model\Persistence\Entity\AdditionEntity;
use App\Model\Persistence\Entity\EventEntity;
use App\Model\Persistence\Entity\OptionEntity;
use App\Model\Persistence\Entity\PriceAmountEntity;
use App\Model\Persistence\Entity\PriceEntity;
use Kdyby\Doctrine\EntityManager;
use Nette\Object;

class AdditionManager extends Object {
    use TDoctrineEntityManager;

    /** @var  AdditionDao */
    private $additionDao;

    /** @var  CurrencyDao */
    private $currencyDao;

    /**
     * EventManager constructor.
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager, CurrencyDao $currencyDao) {
        $this->injectEntityManager($entityManager);
        $this->currencyDao = $currencyDao;
    }

    public function editAdditionFromEventForm(array $values, AdditionEntity $additionEntity): AdditionEntity {
        $em = $this->getEntityManager();
        $additionEntity->setByValueArray($values);
        $em->flush();
        return $additionEntity;
    }

    /**
     * @param array $values
     * @return AdditionEntity
     */
    public function createAdditionFromEventForm(array $values, EventEntity $eventEntity): AdditionEntity {
        $em = $this->getEntityManager();
        $additionEntity = new AdditionEntity();
        $additionEntity->setEvent($eventEntity);
        $additionEntity->setByValueArray($values);
        $em->persist($additionEntity);
        foreach ($values['options'] as $optionValues) {
            $optionEntity = new OptionEntity();
            $optionEntity->setByValueArray($optionValues,['price']);
            $additionEntity->addOption($optionEntity);
            $em->persist($optionEntity);
            $priceValues = $optionValues['price'];
            if ($priceValues) {
                $priceEntity = new PriceEntity();
                $optionEntity->setPrice($priceEntity);
                $em->persist($priceEntity);
                foreach ($priceValues as $currencyCode=>$priceAmount){
                    $currency = $this->currencyDao->getCurrencyByCode($currencyCode);
                    if(!$currency){
                        throw new NotFoundException("Currency $currencyCode not found");
                    }
                    $priceAmountEntity = new PriceAmountEntity();
                    $priceAmountEntity->setAmount($priceAmount);
                    $priceAmountEntity->setCurrency($currency);
                    $em->persist($priceAmountEntity);
                    $priceEntity->addPriceAmount($priceAmountEntity);
                }
            }
        }
        $em->flush();
        return $additionEntity;
    }
}