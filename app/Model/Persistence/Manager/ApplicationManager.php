<?php

namespace App\Model\Persistence\Manager;

use App\Model\Persistence\Dao\ChoiceDao;
use App\Model\Persistence\Dao\TDoctrineEntityManager;
use Kdyby\Doctrine\EntityManager;
use Nette\SmartObject;

/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 26.11.17
 * Time: 17:37
 */
class ApplicationManager {
    use SmartObject, TDoctrineEntityManager;

    /**
     * ChoiceManager constructor.
     * @param EntityManager $entityManager
     * @param ChoiceDao $choiceDao
     */
    public function __construct(EntityManager $entityManager) {
        $this->injectEntityManager($entityManager);
    }
}