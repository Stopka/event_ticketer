<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 26.11.17
 * Time: 19:13
 */

namespace App\Model\Persistence\Manager;

use App\Model\Exception\InvalidInputException;
use App\Model\Persistence\Dao\EarlyWaveDao;
use App\Model\Persistence\Dao\TDoctrineEntityManager;
use App\Model\Persistence\Entity\EarlyEntity;
use App\Model\Persistence\Entity\EventEntity;
use Kdyby\Doctrine\EntityManager;
use Nette\SmartObject;

class EarlyManager {
    use SmartObject, TDoctrineEntityManager;

    /** @var EarlyWaveDao */
    private $earlyWaveDao;

    /** @var EarlyWaveManager */
    private $earlyWaveManager;

    /**
     * EarlyWaveManager constructor.
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager, EarlyWaveDao $earlyWaveDao, EarlyWaveManager $earlyWaveManager) {
        $this->injectEntityManager($entityManager);
        $this->earlyWaveDao = $earlyWaveDao;
        $this->earlyWaveManager = $earlyWaveManager;
    }

    /**
     * @param array $values
     * @param EarlyEntity $earlyEntity
     * @param EventEntity $eventEntity
     * @return EarlyEntity
     * @throws \Exception
     */
    public function editEarlyFromEarlyForm(array $values, EarlyEntity $earlyEntity, EventEntity $eventEntity): EarlyEntity {
        $em = $this->getEntityManager();
        $earlyEntity->setByValueArray($values,['earlyWave']);
        if(!$values['earlyWaveId']){
            $earlyWave = $this->earlyWaveManager->createWaveFromWaveForm($values['earlyWave'], $eventEntity);
        }else{
            $earlyWave = $this->earlyWaveDao->getEarlyWave($values['earlyWaveId']);
            if($earlyWave->getEvent()->getId() != $eventEntity->getId()){
                throw new InvalidInputException('Wave is from different event');
            }
        }
        $earlyEntity->setEarlyWave($earlyWave);
        $em->flush();
        return $earlyEntity;
    }

    /**
     * @param array $values
     * @param EventEntity $eventEntity
     * @return EarlyEntity
     * @throws \Exception
     */
    public function createEarlyFromEarlyForm(array $values, EventEntity $eventEntity): EarlyEntity {
        $em = $this->getEntityManager();
        $earlyEntity = new EarlyEntity();
        $em->persist($earlyEntity);
        return $this->editEarlyFromEarlyForm($values, $earlyEntity, $eventEntity);
    }
}