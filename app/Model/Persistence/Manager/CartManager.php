<?php

namespace App\Model\Persistence\Manager;

use App\Model\Persistence\Dao\AdditionDao;
use App\Model\Persistence\Dao\InsuranceCompanyDao;
use App\Model\Persistence\Dao\OptionDao;
use App\Model\Persistence\Dao\TDoctrineEntityManager;
use App\Model\Persistence\Entity\AdditionEntity;
use App\Model\Persistence\Entity\ApplicationEntity;
use App\Model\Persistence\Entity\CartEntity;
use App\Model\Persistence\Entity\EarlyEntity;
use App\Model\Persistence\Entity\EventEntity;
use App\Model\Persistence\Entity\SubstituteEntity;
use Kdyby\Doctrine\EntityManager;
use Nette\SmartObject;

/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 26.11.17
 * Time: 17:37
 */
class CartManager {
    use SmartObject, TDoctrineEntityManager, TUpdateNumber;

    /** @var  OptionDao */
    private $optionDao;

    /** @var  AdditionDao */
    private $additionDao;

    /** @var InsuranceCompanyDao */
    private $insuranceCompanyDao;

    /** @var ReservationManager */
    private $reservationManager;

    /** @var callable[] */
    public $onCartCreated = array();

    /** @var callable[] */
    public $onCartUpdated = array();

    /**
     * CartManager constructor.
     * @param EntityManager $entityManager
     * @param AdditionDao $additionDao
     * @param OptionDao $optionDao
     */
    public function __construct(EntityManager $entityManager, AdditionDao $additionDao, OptionDao $optionDao,
                                InsuranceCompanyDao $insuranceCompanyDao, ReservationManager $reservationManager) {
        $this->injectEntityManager($entityManager);
        $this->additionDao = $additionDao;
        $this->optionDao = $optionDao;
        $this->insuranceCompanyDao = $insuranceCompanyDao;
        $this->reservationManager = $reservationManager;
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
     * @param array $values
     * @param EventEntity|null $event
     * @param EarlyEntity|null $early
     * @param SubstituteEntity|null $substitute
     * @return CartEntity
     * @throws \Exception
     */
    public function createCartFromCartForm(array $values, ?EventEntity $event = null, ?EarlyEntity $early = null, ?SubstituteEntity $substitute = null) {
        $entityManager = $this->getEntityManager();
        $cart = new CartEntity();
        $cart->setByValueArray($values);
        $cart->setEarly($early);
        $cart->setEvent($event);
        $cart->setSubstitute($substitute);
        $cart->setNextNumber($entityManager);
        $entityManager->persist($cart);
        $commonValues = $values['commons'];
        $hiddenAdditions = $this->additionDao->getEventAdditionsHiddenIn($event, AdditionEntity::VISIBLE_REGISTER);
        foreach ($values['children'] as $childValues) {
            $application = new ApplicationEntity();
            $application->setByValueArray($commonValues);
            $application->setByValueArray($childValues['child']);
            $insuranceCompany = $this->insuranceCompanyDao->getInsuranceCompany($childValues['insuranceCompanyId']);
            $application->setInsuranceCompany($insuranceCompany);
            $application->setCart($cart);
            $application->setNextNumber($entityManager);
            $entityManager->persist($application);
            foreach ($childValues['addittions'] as $additionIdAlphaNumeric => $optionIds) {
                //$additionId = AdditionEntity::getIdFromAplhaNumeric($additionIdAlphaNumeric);
                if (!is_array($optionIds)) {
                    $optionIds = [$optionIds];
                }
                foreach ($optionIds as $optionId) {
                    $choice = $this->addChoice($optionId, $application);
                }
            }
            foreach ($hiddenAdditions as $hiddenAddition) {
                $optionIds = $this->selectHiddenAdditionOptionIds($hiddenAddition);
                foreach ($optionIds as $optionId) {
                    $choice = $this->addChoice($optionId, $application);
                }
            }
        }
        $entityManager->flush();
        $this->onCartCreated($cart);
        return $cart;
    }

    /**
     * @param array $values
     * @param EventEntity|null $event
     * @return CartEntity
     * @throws \Exception
     */
    public function createCartFromReservationForm(array $values, EventEntity $event): void {
        $entityManager = $this->getEntityManager();
        $cart = new CartEntity(true);
        $cart->setEvent($event);
        $cart->setNextNumber($entityManager);
        $entityManager->persist($cart);
        for ($i = 0; $i < $values['count']; $i++) {
            $application = new ApplicationEntity(true);
            $cart->addApplication($application);
            $application->setByValueArray($values);
            $application->setNextNumber($entityManager);
            $entityManager->persist($application);
            foreach ($values['addittions'] as $additionIdAlphaNumeric => $optionIds) {
                //$additionId = AdditionEntity::getIdFromAplhaNumeric($additionIdAlphaNumeric);
                if (!is_array($optionIds)) {
                    $optionIds = [$optionIds];
                }
                foreach ($optionIds as $optionId) {
                    $choice = $this->addChoice($optionId, $application);
                }
            }
        }
        $entityManager->flush();
        $this->onReservationCreated($cart);
        if ($values['delegated']) {
            $this->reservationManager->delegateNewReservations($cart->getApplications(), $values['reservation']);
        }
    }

    /**
     * @param $values
     * @param EventEntity|null $event
     * @param EarlyEntity|null $early
     * @param SubstituteEntity|null $substitute
     * @param CartEntity|null $cart
     * @return CartEntity|null
     * @throws \Exception
     */
    public function editCartFromCartForm($values, ?EventEntity $event = null, ?EarlyEntity $early = null, ?SubstituteEntity $substitute = null, ?CartEntity $cart = null) {
        $entityManager = $this->getEntityManager();
        //$cart = new CartEntity();
        $cart->setByValueArray($values);
        $entityManager->persist($cart);
        $commonValues = $values['commons'];
        $hiddenAdditions = $this->additionDao->getEventAdditionsHiddenIn($event, AdditionEntity::VISIBLE_REGISTER);
        foreach ($values['children'] as $id => $childValues) {
            foreach ($cart->getApplications() as $application) {
                if ($application->getId() != $id) {
                    continue;
                }
                $application->setByValueArray($commonValues);
                $application->setByValueArray($childValues['child']);
                $insuranceCompany = $this->insuranceCompanyDao->getInsuranceCompany($childValues['insuranceCompanyId']);
                $application->setInsuranceCompany($insuranceCompany);
                //$entityManager->persist($application);
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
                        $choice = $this->addChoice($optionId, $application);
                    }
                }
                foreach ($childValues['addittions'] as $additionIdAlphaNumeric => $optionIds) {
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
                        $choice = $this->addChoice($optionId, $application);
                    }
                }
            }
        }
        $entityManager->flush();
        $this->onCartUpdated($cart);
        return $cart;
    }
}