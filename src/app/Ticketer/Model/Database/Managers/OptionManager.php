<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Managers;

use Ticketer\Model\Exceptions\NotFoundException;
use Ticketer\Model\Database\Daos\CurrencyDao;
use Ticketer\Model\Database\Daos\TDoctrineEntityManager;
use Ticketer\Model\Database\Entities\AdditionEntity;
use Ticketer\Model\Database\Entities\OptionEntity;
use Ticketer\Model\Database\Entities\PriceAmountEntity;
use Ticketer\Model\Database\Entities\PriceEntity;
use Ticketer\Model\Database\EntityManager as EntityManagerWrapper;
use Nette\SmartObject;
use Ticketer\Model\Exceptions\NotReadyException;

class OptionManager
{
    use SmartObject;
    use TDoctrineEntityManager;

    /** @var  CurrencyDao */
    private $currencyDao;

    /**
     * OptionManager constructor.
     * @param EntityManagerWrapper $entityManager
     * @param CurrencyDao $currencyDao
     */
    public function __construct(EntityManagerWrapper $entityManager, CurrencyDao $currencyDao)
    {
        $this->injectEntityManager($entityManager);
        $this->currencyDao = $currencyDao;
    }

    /**
     * @param array<mixed> $values
     * @param OptionEntity $optionEntity
     * @return OptionEntity
     * @throws \Exception
     */
    public function editOptionFromOptionForm(array $values, OptionEntity $optionEntity): OptionEntity
    {
        $em = $this->getEntityManager();
        $optionEntity->setByValueArray($values, ['price']);
        $priceEntity = $optionEntity->getPrice();
        if (null === $priceEntity) {
            throw new NotReadyException("Option has no price");
        }
        foreach ($values['price'] as $currencyCode => $priceAmount) {
            $currency = $this->currencyDao->getCurrencyByCode($currencyCode);
            if (null === $currency) {
                throw new NotFoundException("Currency $currencyCode not found");
            }
            $priceAmountEntity = $priceEntity->getPriceAmountByCurrency($currency);
            if (null === $priceAmountEntity) {
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
     * @param array<mixed> $values
     * @param AdditionEntity $additionEntity
     * @return OptionEntity
     */
    public function createOptionFromEventForm(array $values, AdditionEntity $additionEntity): OptionEntity
    {
        $em = $this->getEntityManager();
        $optionEntity = new OptionEntity();
        $optionEntity->setByValueArray($values, ['price']);
        $additionEntity->addOption($optionEntity);
        $em->persist($optionEntity);
        $priceValues = $values['price'];
        if (null !== $priceValues && count($priceValues) > 0) {
            $priceEntity = new PriceEntity();
            $optionEntity->setPrice($priceEntity);
            $em->persist($priceEntity);
            foreach ($priceValues as $currencyCode => $priceAmount) {
                $currency = $this->currencyDao->getCurrencyByCode($currencyCode);
                if (null === $currency) {
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
    public function moveOptionUp(OptionEntity $optionEntity): void
    {
        $addition = $optionEntity->getAddition();
        if (null === $addition) {
            throw new NotReadyException('Option is not set to addition');
        }
        $sorter = new PositionSorter();
        $sorter->moveEntityUp($optionEntity, $addition->getOptions());
        $this->getEntityManager()->flush();
    }

    /**
     * @param OptionEntity $optionEntity
     * @throws \Exception
     */
    public function moveOptionDown(OptionEntity $optionEntity): void
    {
        $addition = $optionEntity->getAddition();
        if (null === $addition) {
            throw new NotReadyException('Option is not set to addition');
        }
        $sorter = new PositionSorter();
        $sorter->moveEntityDown($optionEntity, $addition->getOptions());
        $this->getEntityManager()->flush();
    }

    /**
     * @param OptionEntity $optionEntity
     * @throws \Exception
     */
    public function deleteOption(OptionEntity $optionEntity): void
    {
        $em = $this->getEntityManager();
        $em->remove($optionEntity);
        $em->flush();
    }
}
