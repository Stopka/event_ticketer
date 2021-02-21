<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Entities;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query\Parameter;
use Ticketer\Model\ApplicationStateResolver;
use Ticketer\Model\Database\Enums\ApplicationStateEnum;
use Ticketer\Model\Dtos\Uuid;
use Ticketer\Model\Exceptions\InvalidInputException;
use Ticketer\Model\Exceptions\InvalidStateException;
use Ticketer\Model\Database\Attributes\TAddressAttribute;
use Ticketer\Model\Database\Attributes\TBirthDateAttribute;
use Ticketer\Model\Database\Attributes\TCreatedAttribute;
use Ticketer\Model\Database\Attributes\TGenderAttribute;
use Ticketer\Model\Database\Attributes\TIdentifierAttribute;
use Ticketer\Model\Database\Attributes\TPersonNameAttribute;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Jedna konkrétní vydaná přihláška
 * @package App\Model\Entities
 * @ORM\Entity
 */
class ApplicationEntity extends BaseEntity implements NumberableInterface
{
    use TIdentifierAttribute;
    use TPersonNameAttribute;
    use TGenderAttribute;
    use TAddressAttribute;
    use TBirthDateAttribute;
    use TCreatedAttribute;

    /**
     * @ORM\OneToOne(targetEntity="ApplicationNumberEntity", cascade={"persist","remove"})
     * @var ApplicationNumberEntity
     */
    private ApplicationNumberEntity $number;

    /**
     * @ORM\OneToMany(targetEntity="ChoiceEntity", mappedBy="application", cascade={"persist","remove"}))
     * @var ArrayCollection<int,ChoiceEntity>
     */
    private $choices;

    /**
     * @ORM\ManyToOne(targetEntity="CartEntity", inversedBy="applications")
     * @var CartEntity|null
     */
    private $cart;

    /**
     * @ORM\ManyToOne(targetEntity="EventEntity", inversedBy="applications")
     * @var EventEntity|null
     */
    private $event;

    /**
     * @ORM\ManyToOne(targetEntity="ReservationEntity", inversedBy="applications")
     * @var ReservationEntity|null
     */
    private $reservation;

    /**
     * @ORM\Column(type="application_state_enum")
     * @var ApplicationStateEnum
     */
    private ApplicationStateEnum $state;

    /**
     * @ORM\ManyToOne(targetEntity="InsuranceCompanyEntity")
     * @var InsuranceCompanyEntity
     */
    private $insuranceCompany;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @var string|null
     */
    private $friend;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @var string|null
     */
    private $info;

    private static ?ApplicationStateResolver $stateResolver = null;

    public function __construct(bool $reserved = false)
    {
        parent::__construct();

        $this->state = $reserved
            ? ApplicationStateEnum::RESERVED()
            : ApplicationStateEnum::WAITING();
        $this->choices = new ArrayCollection();
        $this->number = new ApplicationNumberEntity();
        $this->setCreated();
    }

    public function getNumber(): int
    {
        return $this->number->getId();
    }

    /**
     * @return null|string
     */
    public function getFriend(): ?string
    {
        return $this->friend;
    }

    /**
     * @param null|string $friend
     */
    public function setFriend(?string $friend): void
    {
        $this->friend = $friend;
    }

    /**
     * @return null|string
     */
    public function getInfo(): ?string
    {
        return $this->info;
    }

    /**
     * @param null|string $info
     */
    public function setInfo(?string $info): void
    {
        $this->info = $info;
    }

    /**
     * @return InsuranceCompanyEntity|null
     */
    public function getInsuranceCompany(): ?InsuranceCompanyEntity
    {
        return $this->insuranceCompany;
    }

    /**
     * @param InsuranceCompanyEntity $insuranceCompany
     */
    public function setInsuranceCompany(InsuranceCompanyEntity $insuranceCompany): void
    {
        $this->insuranceCompany = $insuranceCompany;
    }

    /**
     * @return ChoiceEntity[]
     */
    public function getChoices(): array
    {
        return $this->choices->toArray();
    }

    /**
     * @param Uuid $additionId
     * @return ChoiceEntity[]
     */
    public function getAdditionChoices(Uuid $additionId): array
    {
        /* TODO rewrite to criteria
        $criteria = Criteria::create();
        $criteria->where(
            $criteria::expr()->in('option', $addition->getOptions())
        );

        return $this->choices->matching($criteria)->toArray();
        */
        return array_filter(
            $this->choices->toArray(),
            static function (ChoiceEntity $choice) use ($additionId): bool {
                $option = $choice->getOption();
                if (null === $option) {
                    return false;
                }
                $addition = $option->getAddition();

                return null !== $addition
                    && $addition->getId()->equals($additionId);
            }
        );
    }

    /**
     * @return ChoiceEntity[][]
     */
    public function getChoicesByAdditionId(): array
    {
        $result = [];
        foreach ($this->getChoices() as $choice) {
            $option = $choice->getOption();
            if (null === $option) {
                continue;
            }
            $addition = $option->getAddition();
            if (null === $addition) {
                continue;
            }
            $additionId = $addition->getId()->toString();
            if (!isset($result[$additionId])) {
                $result[$additionId] = [];
            }
            $result[$additionId][] = $choice;
        }

        return $result;
    }

    /**
     * @param ChoiceEntity $choice
     */
    public function addChoice(ChoiceEntity $choice): void
    {
        $choice->setApplication($this);
    }

    /**
     * @param ChoiceEntity $choice
     */
    public function removeChoice(ChoiceEntity $choice): void
    {
        $choice->setApplication(null);
    }

    /**
     * @param ChoiceEntity $choice
     * @internal
     */
    public function addInversedChoice(ChoiceEntity $choice): void
    {
        $this->choices->add($choice);
    }

    /**
     * @param ChoiceEntity $choice
     * @internal
     */
    public function removeInversedChoice(ChoiceEntity $choice): void
    {
        $this->choices->removeElement($choice);
    }

    /**
     * @return CartEntity
     */
    public function getCart(): ?CartEntity
    {
        return $this->cart;
    }

    /**
     * @param CartEntity|null $cart
     */
    public function setCart(?CartEntity $cart): void
    {
        if (null !== $cart) {
            $cartEvent = $cart->getEvent();
            $event = $this->getEvent();
            if (null !== $cartEvent && null === $event) {
                $this->setEvent($cartEvent);
            } elseif (null !== $event && null === $cartEvent) {
                $cart->setEvent($event);
            }
            $cartEvent = $cart->getEvent();
            $event = $this->getEvent();
            if (
                null === $event
                || null === $cartEvent
                || $event->getId() !== $cartEvent->getId()
            ) {
                throw new InvalidInputException("Error.Application.InvalidInput");
            }
        }
        if (null !== $this->cart) {
            /** @noinspection PhpInternalEntityUsedInspection */
            $this->cart->removeInversedApplication($this);
        }
        $this->cart = $cart;
        if (null !== $cart) {
            /** @noinspection PhpInternalEntityUsedInspection */
            $cart->addInversedApplication($this);
        }
        $this->updateState();
    }

    /**
     * @return ApplicationStateEnum
     */
    public function getState(): ApplicationStateEnum
    {
        return $this->state;
    }

    public function cancelApplication(): void
    {
        $this->state = ApplicationStateEnum::CANCELLED();
    }

    public function updateState(): void
    {
        $this->state = self::getStateResolver()->resolveState($this);

        $event = $this->getEvent();
        if (null !== $event) {
            $event->updateCapacityFull();
        }
    }

    /**
     * @param EventEntity|NULL $event
     */
    public function setEvent(?EventEntity $event): void
    {
        if (null !== $this->event) {
            /** @noinspection PhpInternalEntityUsedInspection */
            $this->event->removeInversedApplication($this);
        }
        $this->event = $event;
        if (null !== $event) {
            /** @noinspection PhpInternalEntityUsedInspection */
            $event->addInversedApplication($this);
        }
    }

    /**
     * @return EventEntity|NULL
     */
    public function getEvent(): ?EventEntity
    {
        return $this->event;
    }

    /**
     * @return ReservationEntity
     */
    public function getReservation(): ?ReservationEntity
    {
        return $this->reservation;
    }

    /**
     * @param ReservationEntity $reservation
     * @throws InvalidInputException
     * @throws InvalidStateException
     */
    public function setReservation(?ReservationEntity $reservation): void
    {
        if (!$this->getState()->isReserved()) {
            throw new InvalidStateException("Error.Reservation.Application.InvalidState");
        }
        if (null !== $reservation) {
            $reservationEvent = $reservation->getEvent();
            $event = $this->getEvent();
            if (null !== $reservationEvent && null === $event) {
                $this->setEvent($reservationEvent);
            } elseif (null !== $event && null === $reservationEvent) {
                $reservation->setEvent($event);
            }
            $reservationEvent = $reservation->getEvent();
            $event = $this->getEvent();
            if (
                null === $event
                || null === $reservationEvent
                || $event->getId() !== $reservationEvent->getId()
            ) {
                throw new InvalidInputException("Error.Application.InvalidInput");
            }
        }
        if (null !== $this->reservation) {
            /** @noinspection PhpInternalEntityUsedInspection */
            $this->reservation->removeInversedApplication($this);
        }
        $this->reservation = $reservation;
        if (null !== $this->reservation) {
            /** @noinspection PhpInternalEntityUsedInspection */
            $this->reservation->addInversedApplication($this);
        }
        $this->updateState();
    }

    /**
     * @param EntityRepository<ApplicationEntity> $repository
     * @return int
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    protected function findLastNumber(EntityRepository $repository): int
    {
        $qb = $repository->createQueryBuilder('a');
        $qb->select('MAX(a.number)');
        $qb->where(
            $qb->expr()->eq('a.event', ':event')
        );
        $qb->setParameters(
            new ArrayCollection(
                [
                    new Parameter('event', $this->getEvent()),
                ]
            )
        );

        return (int)$qb->getQuery()->getSingleScalarResult();
    }

    protected static function getStateResolver(): ApplicationStateResolver
    {
        if (null === self::$stateResolver) {
            self::$stateResolver = new ApplicationStateResolver();
        }

        return self::$stateResolver;
    }
}
