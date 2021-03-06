<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Managers;

use Psr\EventDispatcher\EventDispatcherInterface;
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
use Ticketer\Model\Database\Enums\ReservationStateEnum;
use Ticketer\Model\Database\Managers\Events\CartCreatedEvent;
use Ticketer\Model\Database\Managers\Events\CartUpdatedEvent;
use Ticketer\Model\Dtos\Uuid;

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

    private EventDispatcherInterface $eventDispatcher;

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
        ApplicationDao $applicationDao,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->injectEntityManager($entityManager);
        $this->additionDao = $additionDao;
        $this->optionDao = $optionDao;
        $this->insuranceCompanyDao = $insuranceCompanyDao;
        $this->applicationManager = $applicationManager;
        $this->applicationDao = $applicationDao;
        $this->eventDispatcher = $eventDispatcher;
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
        if (null !== $reservation) {
            $reservation->setState(ReservationStateEnum::ORDERED());
            $entityManager->persist($reservation);
        }
        $cart->setByValueArray($values);
        $entityManager->persist($cart);
        $commonValues = $values[CartFormWrapper::CONTAINER_NAME_COMMONS];
        // go through all applications from form
        $processedApplicationIds = [];
        foreach ($values[CartFormWrapper::CONTAINER_NAME_APPLICATIONS] as $applicationIdString => $applicationValues) {
            $application = null;
            // is existing application?
            if (
                is_string($applicationIdString)
                && !Strings::startsWith($applicationIdString, CartFormWrapper::VALUE_APPLICATION_NEW)
            ) {
                // find application by id
                $application = $this->applicationDao->getApplication(
                    Uuid::fromString($applicationIdString)
                );
                //TODO zkontrolovat přijátá ID a zrušit nepoužité přihlášky
            }
            // if exisitning application is not matched with event
            $applicationEvent = null !== $application ? $application->getEvent() : null;
            if (
                null === $event ||
                (null !== $applicationEvent && !$applicationEvent->getId()->equals($event->getId()))
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
            $processedApplicationIds[] = $application->getId()->toString();
            $application->setCart($cart);
        }
        foreach ($cart->getApplications() as $application) {
            if (!in_array($application->getId()->toString(), $processedApplicationIds, true)) {
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
        $this->eventDispatcher->dispatch(new CartCreatedEvent($cart));

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
        $this->eventDispatcher->dispatch(new CartUpdatedEvent($cart));

        return $cart;
    }
}
