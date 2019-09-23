<?php

namespace App\Model\Persistence\Manager;

use App\Controls\Forms\CartFormWrapper;
use App\Model\Persistence\Dao\AdditionDao;
use App\Model\Persistence\Dao\ApplicationDao;
use App\Model\Persistence\Dao\InsuranceCompanyDao;
use App\Model\Persistence\Dao\OptionDao;
use App\Model\Persistence\Dao\TDoctrineEntityManager;
use App\Model\Persistence\Entity\CartEntity;
use App\Model\Persistence\Entity\EarlyEntity;
use App\Model\Persistence\Entity\EventEntity;
use App\Model\Persistence\Entity\ReservationEntity;
use App\Model\Persistence\Entity\SubstituteEntity;
use App\Model\Persistence\EntityManagerWrapper;
use Nette\SmartObject;
use Nette\Utils\Strings;

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

    /** @var ApplicationManager */
    private $applicationManager;

    /** @var ApplicationDao */
    private $applicationDao;

    /** @var callable[] */
    public $onCartCreated = array();

    /** @var callable[] */
    public $onCartUpdated = array();

    /**
     * CartManager constructor.
     * @param EntityManagerWrapper $entityManager
     * @param AdditionDao $additionDao
     * @param OptionDao $optionDao
     * @param InsuranceCompanyDao $insuranceCompanyDao
     * @param ApplicationManager $applicationManager
     * @param ApplicationDao $applicationDao
     */
    public function __construct(
        EntityManagerWrapper $entityManager,
        AdditionDao $additionDao,
        OptionDao $optionDao,
        InsuranceCompanyDao $insuranceCompanyDao,
        ApplicationManager $applicationManager,
        ApplicationDao $applicationDao
    ) {
        $this->injectEntityManager($entityManager);
        $this->additionDao = $additionDao;
        $this->optionDao = $optionDao;
        $this->insuranceCompanyDao = $insuranceCompanyDao;
        $this->applicationManager = $applicationManager;
        $this->applicationDao = $applicationDao;
    }

    /**
     * @param array $values
     * @param EventEntity|null $event
     * @param EarlyEntity|null $early
     * @param SubstituteEntity|null $substitute
     * @param ReservationEntity|null $reservation
     * @param CartEntity|null $cart
     * @return CartEntity
     * @throws \Exception
     */
    private function processCartForm(
        array $values,
        ?EventEntity $event = null,
        ?EarlyEntity $early = null,
        ?SubstituteEntity $substitute = null,
        ?ReservationEntity $reservation = null,
        ?CartEntity $cartInput = null
    ): CartEntity {
        $entityManager = $this->getEntityManager();
        if (!$cartInput) {
            $cart = new CartEntity();
            $entityManager->persist($cart);
            $cart->setEarly($early);
            $cart->setEvent($event);
            $cart->setSubstitute($substitute);
            //$cart->setNextNumber($entityManager);
        } else {
            $cart = $cartInput;
        }
        $cart->setByValueArray($values);
        $entityManager->persist($cart);
        $commonValues = $values[CartFormWrapper::CONTAINER_NAME_COMMONS];
        // go through all applications from form
        $processedApplicationIds = [];
        foreach ($values[CartFormWrapper::CONTAINER_NAME_APPLICATIONS] as $applicationId => $applicationValues) {
            $application = null;
            // is existing application?
            if (!Strings::startsWith($applicationId, CartFormWrapper::VALUE_APPLICATION_NEW)) {
                // find application by id
                $application = $this->applicationDao->getApplication($applicationId);
                //TODO zkontrolovat přijátá ID a zrušit nepoužité přihlášky
            }
            // if exisitning application is not matched with event
            if ($application && $application->getEvent()->getId() != $event->getId()) {
                continue;
            }
            // if application exists
            if ($application) {
                //update it
                $application = $this->applicationManager->editApplicationFromCartForm($applicationValues, $commonValues, $application);
            } else {
                // create new onecom
                $application = $this->applicationManager->createApplicationFromCartForm($applicationValues, $commonValues, $event);
            }
            $processedApplicationIds[] = $application->getId();
            $application->setCart($cart);
        }
        foreach ($cart->getApplications() as $application) {
            if (!in_array($application->getId(), $processedApplicationIds)) {
                $application->setCart(null);
            }
        }
        $entityManager->flush();
        return $cart;
    }

    /**
     * @param array $values
     * @param EventEntity|null $event
     * @param EarlyEntity|null $early
     * @param SubstituteEntity|null $substitute
     * @param ReservationEntity|null $reservation
     * @return CartEntity
     * @throws \Exception
     */
    public function createCartFromCartForm(
        array $values,
        ?EventEntity $event = null,
        ?EarlyEntity $early = null,
        ?SubstituteEntity $substitute = null,
        ?ReservationEntity $reservation = null
    ) {
        $cart = $this->processCartForm($values, $event, $early, $substitute, $reservation);
        /** @noinspection PhpUndefinedMethodInspection */
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
    public function editCartFromCartForm(
        array $values,
        ?EventEntity $event = null,
        ?EarlyEntity $early = null,
        ?SubstituteEntity $substitute = null,
        ?CartEntity $cart = null
    ) {
        $cart = $this->processCartForm($values, $event, $early, $substitute, null, $cart);
        /** @noinspection PhpUndefinedMethodInspection */
        $this->onCartUpdated($cart);
        return $cart;
    }
}