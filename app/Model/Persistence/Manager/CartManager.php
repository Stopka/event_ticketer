<?php

namespace App\Model\Persistence\Manager;

use App\Model\Persistence\Dao\AdditionDao;
use App\Model\Persistence\Dao\InsuranceCompanyDao;
use App\Model\Persistence\Dao\OptionDao;
use App\Model\Persistence\Dao\TDoctrineEntityManager;
use App\Model\Persistence\Entity\ApplicationEntity;
use App\Model\Persistence\Entity\CartEntity;
use App\Model\Persistence\Entity\ChoiceEntity;
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
    use SmartObject, TDoctrineEntityManager;

    /** @var  OptionDao */
    private $optionDao;

    /** @var  AdditionDao */
    private $additionDao;

    /** @var InsuranceCompanyDao */
    private $insuranceCompanyDao;

    /** @var callable[]  */
    public $onCartCreated = array();

    /** @var callable[]  */
    public $onCartUpdated = array();

    /**
     * CartManager constructor.
     * @param EntityManager $entityManager
     * @param AdditionDao $additionDao
     * @param OptionDao $optionDao
     */
    public function __construct(EntityManager $entityManager, AdditionDao $additionDao, OptionDao $optionDao, InsuranceCompanyDao $insuranceCompanyDao) {
        $this->injectEntityManager($entityManager);
        $this->additionDao = $additionDao;
        $this->optionDao = $optionDao;
        $this->insuranceCompanyDao = $insuranceCompanyDao;
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
        $entityManager->persist($cart);
        $commonValues = $values['commons'];
        $hiddenAdditions = $this->additionDao->getHiddenEventAdditions($event);
        foreach ($values['children'] as $childValues) {
            $application = new ApplicationEntity();
            $application->setByValueArray($commonValues);
            $application->setByValueArray($childValues['child']);
            $insuranceCompany = $this->insuranceCompanyDao->getInsuranceCompany($childValues['insuranceCompanyId']);
            $application->setInsuranceCompany($insuranceCompany);
            $application->setCart($cart);
            $entityManager->persist($application);
            foreach ($childValues['addittions'] as $additionId => $optionId) {
                $option = $this->optionDao->getOption($optionId);
                $choice = new ChoiceEntity();
                $choice->setOption($option);
                $choice->setApplication($application);
                $entityManager->persist($choice);
            }
            foreach ($hiddenAdditions as $hiddenAddition) {
                $options = $hiddenAddition->getOptions();
                for ($i = 0; $i < count($options) && $i < $hiddenAddition->getMinimum(); $i++) {
                    $option = $options[$i];
                    $choice = new ChoiceEntity();
                    $choice->setOption($option);
                    $choice->setApplication($application);
                    $entityManager->persist($choice);
                }
            }
        }
        $entityManager->flush();
        $this->onCartCreated($cart);
        return $cart;
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
        $hiddenAdditions = $this->additionDao->getHiddenEventAdditions($event);
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
                foreach ($childValues['addittions'] as $additionId => $optionId) {
                    foreach ($application->getChoices() as $choice) {
                        if ($choice->getOption()->getAddition()->getId() != $additionId) {
                            continue;
                        }
                        $option = $this->optionDao->getOption($optionId);
                        $choice->setOption($option);
                    }
                }
            }
        }
        $entityManager->flush();
        $this->onCartUpdated($cart);
        return $cart;
    }
}