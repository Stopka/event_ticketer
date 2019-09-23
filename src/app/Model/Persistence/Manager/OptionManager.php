<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 20.1.17
 * Time: 22:09
 */

namespace App\Model\Persistence\Manager;


use App\Model\Exception\NotFoundException;
use App\Model\Persistence\Dao\CurrencyDao;
use App\Model\Persistence\Dao\TDoctrineEntityManager;
use App\Model\Persistence\Entity\AdditionEntity;
use App\Model\Persistence\Entity\OptionEntity;
use App\Model\Persistence\Entity\PriceAmountEntity;
use App\Model\Persistence\Entity\PriceEntity;
use App\Model\Persistence\EntityManagerWrapper;
use Nette\SmartObject;

class OptionManager {
    use SmartObject, TDoctrineEntityManager;

    /** @var  CurrencyDao */
    private $currencyDao;

    /**
     * OptionManager constructor.
     * @param EntityManagerWrapper $entityManager
     * @param CurrencyDao $currencyDao
     */
    public function __construct(EntityManagerWrapper $entityManager, CurrencyDao $currencyDao) {
        $this->injectEntityManager($entityManager);
        $this->currencyDao = $currencyDao;
    }

    /**
     * @param array $values
     * @param OptionEntity $optionEntity
     * @return OptionEntity
     * @throws \Exception
     */
    public function editOptionFromOptionForm(array $values, OptionEntity $optionEntity): OptionEntity {
        $em = $this->getEntityManager();
        $optionEntity->setByValueArray($values,['price']);
        $priceEntity = $optionEntity->getPrice();
        foreach ($values['price'] as $currencyCode=>$priceAmount){
            $currency = $this->currencyDao->getCurrencyByCode($currencyCode);
            if(!$currency){
                throw new NotFoundException("Currency $currencyCode not found");
            }
            $priceAmountEntity = $priceEntity->getPriceAmountByCurrency($currency);
            if(!$priceAmountEntity){
                $priceAmountEntity = new PriceAmountEntity();
                $priceAmountEntity->setCurrency($currency);
                $priceEntity->addPriceAmount($priceAmountEntity);
                $em->persist($priceAmountEntity);
            }
            $priceAmountEntity->setAmount($priceAmount);
        }
        $em->flush();
        return $optionEntity;
    }

    /**
     * @param array $values
     * @param AdditionEntity $additionEntity
     * @return OptionEntity
     */
    public function createOptionFromEventForm(array $values, AdditionEntity $additionEntity): OptionEntity {
        $em = $this->getEntityManager();
        $optionEntity = new OptionEntity();
        $optionEntity->setByValueArray($values,['price']);
        $additionEntity->addOption($optionEntity);
        $em->persist($optionEntity);
        $priceValues = $values['price'];
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
        $em->flush();
        return $optionEntity;
    }

    /**
     * @param OptionEntity $optionEntity
     * @throws \Exception
     */
    public function moveOptionUp(OptionEntity $optionEntity){
        $addition = $optionEntity->getAddition();
        $sorter = new PositionSorter();
        $sorter->moveEntityUp($optionEntity,$addition->getOptions());
        $this->getEntityManager()->flush();
    }

    /**
     * @param OptionEntity $optionEntity
     * @throws \Exception
     */
    public function moveOptionDown(OptionEntity $optionEntity){
        $addition = $optionEntity->getAddition();
        $sorter = new PositionSorter();
        $sorter->moveEntityDown($optionEntity,$addition->getOptions());
        $this->getEntityManager()->flush();
    }

    /**
     * @param OptionEntity $optionEntity
     * @throws \Exception
     */
    public function deleteOption(OptionEntity $optionEntity){
        $em = $this->getEntityManager();
        $em->remove($optionEntity);
        $em->flush();
    }
}