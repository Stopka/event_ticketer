<?php

namespace App\Model\Persistence\Manager;

use App\Model\Exception\NotFoundException;
use App\Model\Persistence\Dao\ChoiceDao;
use App\Model\Persistence\Dao\TDoctrineEntityManager;
use Kdyby\Doctrine\EntityManager;
use Nette\Object;

/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 26.11.17
 * Time: 17:37
 */
class ChoiceManager extends Object {
    use TDoctrineEntityManager;

    /** @var  ChoiceDao */
    private $choiceDao;

    /**
     * ChoiceManager constructor.
     * @param EntityManager $entityManager
     * @param ChoiceDao $choiceDao
     */
    public function __construct(EntityManager $entityManager, ChoiceDao $choiceDao) {
        $this->injectEntityManager($entityManager);
        $this->choiceDao = $choiceDao;
    }

    /**
     * @param $choiceId string|null
     * @throws NotFoundException
     */
    public function inverseChoicePayed(?string $choiceId): void {
        $choice = $this->choiceDao->getChoice($choiceId);
        if (!$choice) {
            throw new NotFoundException("Choice not found");
        }
        $choice->inversePayed();
        $this->getEntityManager()->flush();
    }
}