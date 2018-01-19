<?php

namespace App\Model\Persistence\Manager;

use App\Model\Exception\NotFoundException;
use App\Model\Persistence\Dao\AdditionDao;
use App\Model\Persistence\Dao\ChoiceDao;
use App\Model\Persistence\Dao\OptionDao;
use App\Model\Persistence\Dao\TDoctrineEntityManager;
use App\Model\Persistence\Entity\AdditionEntity;
use App\Model\Persistence\Entity\ApplicationEntity;
use App\Model\Persistence\Entity\ChoiceEntity;
use Kdyby\Doctrine\EntityManager;
use Nette\SmartObject;

/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 26.11.17
 * Time: 17:37
 */
class ChoiceManager {
    use SmartObject, TDoctrineEntityManager;

    /** @var  ChoiceDao */
    private $choiceDao;

    /** @var AdditionDao */
    private $additionDao;

    /** @var OptionDao */
    private $optionDao;

    /**
     * ChoiceManager constructor.
     * @param EntityManager $entityManager
     * @param ChoiceDao $choiceDao
     * @param AdditionDao $additionDao
     * @param OptionDao $optionDao
     */
    public function __construct(
        EntityManager $entityManager,
        ChoiceDao $choiceDao,
        AdditionDao $additionDao,
        OptionDao $optionDao
    ) {
        $this->injectEntityManager($entityManager);
        $this->choiceDao = $choiceDao;
        $this->additionDao = $additionDao;
        $this->optionDao = $optionDao;
    }

    /**
     * @param $choiceId string|null
     * @throws NotFoundException
     * @throws \Exception
     */
    public function inverseChoicePayed(?string $choiceId): void {
        $choice = $this->choiceDao->getChoice($choiceId);
        if (!$choice) {
            throw new NotFoundException("Choice not found");
        }
        $choice->inversePayed();
        $this->getEntityManager()->flush();
    }

    /**
     * @param string $optionId
     * @param ApplicationEntity $application
     * @return ChoiceEntity
     */
    private function addChoice(string $optionId, ApplicationEntity $application): ChoiceEntity {
        $option = $this->optionDao->getOption($optionId);
        if (!$option) {
            throw new NotFoundException("Option was not found.");
        }
        $choice = new ChoiceEntity();
        $choice->setOption($option);
        $choice->setApplication($application);
        $this->getEntityManager()->persist($choice);
        return $choice;
    }

    /**
     * @param AdditionEntity $hiddenAddition
     * @return string[]
     */
    private function selectHiddenAdditionOptionIds(AdditionEntity $hiddenAddition): array {
        $options = $hiddenAddition->getOptions();
        $optionIds = [];
        for ($i = 0; $i < count($options) && $i < $hiddenAddition->getMinimum(); $i++) {
            $option = $options[$i];
            $optionIds[] = $option->getId();
        }
        return $optionIds;
    }

    public function createAdditionChoicesToApplication(array $values, ApplicationEntity $application) {
        foreach ($values as $additionIdAlphaNumeric => $optionIds) {
            //$additionId = AdditionEntity::getIdFromAplhaNumeric($additionIdAlphaNumeric);
            if (!is_array($optionIds)) {
                $optionIds = [$optionIds];
            }
            foreach ($optionIds as $optionId) {
                $this->addChoice($optionId, $application);
            }
        }
        $hiddenAdditions = $this->additionDao->getEventAdditionsHiddenIn($application->getEvent(), AdditionEntity::VISIBLE_REGISTER);
        foreach ($hiddenAdditions as $hiddenAddition) {
            $optionIds = $this->selectHiddenAdditionOptionIds($hiddenAddition);
            foreach ($optionIds as $optionId) {
                $this->addChoice($optionId, $application);
            }
        }
    }

    public function editAdditionChoicesInApplication(array $values, ApplicationEntity $application) {
        $entityManager = $this->getEntityManager();
        // update hidden additions of application
        $hiddenAdditions = $this->additionDao->getEventAdditionsHiddenIn($application->getEvent(), AdditionEntity::VISIBLE_REGISTER);
        foreach ($hiddenAdditions as $hiddenAddition) {
            $optionIds = $this->selectHiddenAdditionOptionIds($hiddenAddition);
            $processedOptionIds = [];
            $choices = $application->getChoices();
            foreach ($choices as $choice) {
                if ($choice->getOption()->getAddition()->getId() != $hiddenAddition->getId()) {
                    continue;
                }
                if (!in_array($choice->getOption()->getId(), $optionIds)) {
                    $entityManager->remove($choice);
                }
                $processedOptionIds[] = $choice->getOption()->getId();
            }
            foreach ($optionIds as $optionId) {
                if (in_array($optionId, $processedOptionIds)) {
                    continue;
                }
                $this->addChoice($optionId, $application);
            }
        }
        // update visible additions
        foreach ($values as $additionIdAlphaNumeric => $optionIds) {
            $additionId = AdditionEntity::getIdFromAplhaNumeric($additionIdAlphaNumeric);
            if (!is_array($optionIds)) {
                $optionIds = [$optionIds];
            }
            $processedOptionIds = [];
            $choices = $application->getChoices();
            foreach ($choices as $choice) {
                if ($choice->getOption()->getAddition()->getId() != $additionId) {
                    continue;
                }
                if (!in_array($choice->getOption()->getId(), $optionIds)) {
                    $entityManager->remove($choice);
                }
                $processedOptionIds[] = $choice->getOption()->getId();
            }
            foreach ($optionIds as $optionId) {
                if (in_array($optionId, $processedOptionIds)) {
                    continue;
                }
                $this->addChoice($optionId, $application);
            }
        }
    }
}