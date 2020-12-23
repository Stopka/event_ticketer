<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Managers;

use Ticketer\Controls\Forms\CartFormWrapper;
use Ticketer\Model\Database\Daos\AdditionDao;
use Ticketer\Model\Database\Daos\ApplicationDao;
use Ticketer\Model\Database\Daos\InsuranceCompanyDao;
use Ticketer\Model\Database\Daos\OptionDao;
use Ticketer\Model\Database\Daos\TDoctrineEntityManager;
use Ticketer\Model\Database\Entities\CartEntity;
use Ticketer\Model\Database\Entities\EarlyEntity;
use Ticketer\Model\Database\Entities\EventEntity;
use Ticketer\Model\Database\Entities\ReservationEntity;
use Ticketer\Model\Database\Entities\SubstituteEntity;
use Ticketer\Model\Database\EntityManager as EntityManagerWrapper;
use Nette\SmartObject;
use Nette\Utils\Strings;

class CartManager
{
    use SmartObject;
    use TDoctrineEntityManager;

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
    public $onCartCreated = [];

    /** @var callable[] */
    public $onCartUpdated = [];

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
     * @param array<mixed> $values
     * @param EventEntity|null $event
     * @param EarlyEntity|null $early
     * @param SubstituteEntity|null $substitute
     * @param ReservationEntity|null $reservation
     * @param CartEntity|null $cartInput
     * @return CartEntity
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
        if (null === $cartInput) {
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
            $applicationEvent = null !== $application ? $application->getEvent() : null;
            if (
                null === $applicationEvent ||
                null === $event ||
                (null !== $application && $applicationEvent->getId() != $event->getId())
            ) {
                continue;
            }
            // if application exists
            if (null !== $application) {
                //update it
                $application = $this->applicationManager->editApplicationFromCartForm(
                    $applicationValues,
                    $commonValues,
                    $application
                );
            } else {
                // create new onecom
                $application = $this->applicationManager->createApplicationFromCartForm(
                    $applicationValues,
                    $commonValues,
                    $event
                );
            }
            $processedApplicationIds[] = $application->getId();
            $application->setCart($cart);
        }
        foreach ($cart->getApplications() as $application) {
            if (!in_array($application->getId(), $processedApplicationIds, true)) {
                $application->setCart(null);
            }
        }
        $entityManager->flush();

        return $cart;
    }

    /**
     * @param array<mixed> $values
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
    ): CartEntity {
        $cart = $this->processCartForm($values, $event, $early, $substitute, $reservation);
        /** @noinspection PhpUndefinedMethodInspection */
        $this->onCartCreated($cart);

        return $cart;
    }

    /**
     * @param array<mixed> $values
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
    ): ?CartEntity {
        $cart = $this->processCartForm($values, $event, $early, $substitute, null, $cart);
        /** @noinspection PhpUndefinedMethodInspection */
        $this->onCartUpdated($cart);

        return $cart;
    }
}
