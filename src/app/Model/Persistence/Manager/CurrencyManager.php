<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 20.1.17
 * Time: 22:09
 */

namespace App\Model\Persistence\Manager;


use App\Model\Persistence\Dao\CurrencyDao;
use App\Model\Persistence\Dao\TDoctrineEntityManager;
use App\Model\Persistence\Entity\CurrencyEntity;
use App\Model\Persistence\EntityManagerWrapper;
use Kdyby\Doctrine\EntityManager;
use Nette\SmartObject;

class CurrencyManager {
    use SmartObject, TDoctrineEntityManager;

    /** @var  CurrencyDao */
    private $currencyDao;

    /**
     * CurrencyManager constructor.
     * @param EntityManager $entityManager
     * @param CurrencyDao $currencyDao
     */
    public function __construct(EntityManagerWrapper $entityManager, CurrencyDao $currencyDao) {
        $this->injectEntityManager($entityManager);
        $this->currencyDao = $currencyDao;
    }

    /**
     * @param array $values
     * @param CurrencyEntity $currencyEntity
     * @return CurrencyEntity
     */
    public function editCurrencyFromCurrencyForm(array $values, CurrencyEntity $currencyEntity): CurrencyEntity {
        $em = $this->getEntityManager();
        $currencyEntity->setByValueArray($values);
        $em->flush();
        return $currencyEntity;
    }

    /**
     * @param array $values
     * @return CurrencyEntity
     */
    public function createCurrencyFromCurrencyForm(array $values): CurrencyEntity {
        $em = $this->getEntityManager();
        $currencyEntity = new CurrencyEntity();
        $em->persist($currencyEntity);
        return $this->editCurrencyFromCurrencyForm($values, $currencyEntity);
    }

    /**
     * @param CurrencyEntity|null $currencyEntity
     * @return CurrencyEntity
     */
    public function setDefaultCurrency(?CurrencyEntity $currencyEntity): CurrencyEntity {
        return $this->currencyDao->setDefaultCurrency($currencyEntity);
    }
}