<?php

namespace App\Model\Persistence\Manager;

use App\Model\Exception\NotFoundException;
use App\Model\Persistence\Dao\AdditionDao;
use App\Model\Persistence\Dao\InsuranceCompanyDao;
use App\Model\Persistence\Dao\OptionDao;
use App\Model\Persistence\Dao\TDoctrineEntityManager;
use App\Model\Persistence\Entity\AdditionEntity;
use App\Model\Persistence\Entity\ApplicationEntity;
use App\Model\Persistence\Entity\ChoiceEntity;
use App\Model\Persistence\Entity\EventEntity;
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

    /** @var AdditionDao */
    private $additionDao;

    /** @var InsuranceCompanyDao */
    private $insuranceCompanyDao;

    /** @var OptionDao */
    private $optionDao;

    /**
     * ApplicationManager constructor.
     * @param EntityManager $entityManager
     * @param AdditionDao $additionDao
     * @param InsuranceCompanyDao $insuranceCompanyDao
     * @param OptionDao $optionDao
     */
    public function __construct(
        EntityManager $entityManager,
        AdditionDao $additionDao,
        InsuranceCompanyDao $insuranceCompanyDao,
        OptionDao $optionDao
    ) {
        $this->injectEntityManager($entityManager);
        $this->additionDao = $additionDao;
        $this->insuranceCompanyDao = $insuranceCompanyDao;
        $this->optionDao = $optionDao;
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

    public function createApplicationFromCartForm(array $values, array $commonValues = [], EventEntity $event): ApplicationEntity {
        $entityManager = $this->getEntityManager();
        $hiddenAdditions = $this->additionDao->getEventAdditionsHiddenIn($event, AdditionEntity::VISIBLE_REGISTER);
        $application = new ApplicationEntity();
        $application->setByValueArray($commonValues);
        $application->setByValueArray($values['child']);
        $insuranceCompany = $this->insuranceCompanyDao->getInsuranceCompany($values['insuranceCompanyId']);
        $application->setInsuranceCompany($insuranceCompany);
        $application->setNextNumber($entityManager);
        $entityManager->persist($application);
        foreach ($values['addittions'] as $additionIdAlphaNumeric => $optionIds) {
            //$additionId = AdditionEntity::getIdFromAplhaNumeric($additionIdAlphaNumeric);
            if (!is_array($optionIds)) {
                $optionIds = [$optionIds];
            }
            foreach ($optionIds as $optionId) {
                $this->addChoice($optionId, $application);
            }
        }
        foreach ($hiddenAdditions as $hiddenAddition) {
            $optionIds = $this->selectHiddenAdditionOptionIds($hiddenAddition);
            foreach ($optionIds as $optionId) {
                $this->addChoice($optionId, $application);
            }
        }
        return $application;
    }

    public function editApplicationFromCartForm(array $values, array $commonValues = [], ApplicationEntity $application): ApplicationEntity {
        $entityManager = $this->getEntityManager();
        $hiddenAdditions = $this->additionDao->getEventAdditionsHiddenIn($application->getEvent(), AdditionEntity::VISIBLE_REGISTER);
        //update existing application data
        $application->setByValueArray($commonValues);
        $application->setByValueArray($values['child']);
        $insuranceCompany = $this->insuranceCompanyDao->getInsuranceCompany($values['insuranceCompanyId']);
        $application->setInsuranceCompany($insuranceCompany);
        //$entityManager->persist($application);
        // update hidden additions of application
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
        foreach ($values['addittions'] as $additionIdAlphaNumeric => $optionIds) {
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
        return $application;
    }
}