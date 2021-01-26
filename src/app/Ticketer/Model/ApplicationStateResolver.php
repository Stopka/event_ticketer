<?php

declare(strict_types=1);

namespace Ticketer\Model;

use Ticketer\Model\Database\Entities\AdditionEntity;
use Ticketer\Model\Database\Entities\ApplicationEntity;
use Ticketer\Model\Database\Entities\ChoiceEntity;
use Ticketer\Model\Database\Enums\ApplicationStateEnum;

class ApplicationStateResolver
{
    public function resolveState(ApplicationEntity $application): ApplicationStateEnum
    {
        $applicationState = $application->getState();
        if (!$this->canStateBeChanged($applicationState)) {
            // Application is canceled, that cannot be changed
            return $applicationState;
        }
        $applicationState = $this->resolveReservations($applicationState, $application);
        $applicationState = $this->resolveOccupation($applicationState, $application);

        return $applicationState;
    }

    private function resolveOccupation(
        ApplicationStateEnum $applicationState,
        ApplicationEntity $application
    ): ApplicationStateEnum {
        $cart = $application->getCart();
        $event = $application->getEvent();

        if (null === $cart || null === $event) {
            return $applicationState;
        }

        $applicationState = ApplicationStateEnum::WAITING();

        $unmetStateRequirements = array_map(static fn(): array => [], ApplicationStateEnum::getLabels());
        foreach ($event->getAdditions() as $addition) {
            if (!$this->areAllAdditionChoicesPayed($application, $addition)) {
                $requiredForState = $addition->getRequiredForState();
                if (null !== $requiredForState) {
                    $unmetStateRequirements[$requiredForState->getValue()][] = $addition;
                }
                continue;
            }
            $enoughForState = $addition->getEnoughForState();
            if (null !== $enoughForState) {
                $applicationState = ApplicationStateEnum::getMax($applicationState, $enoughForState);
            }
        }
        $applicationState = $this->raiseStateToMaximalMetRequirements($applicationState, $unmetStateRequirements);

        return $applicationState;
    }

    /**
     * @param ApplicationStateEnum $applicationState
     * @param array<int,array<AdditionEntity>> $unmetStateRequirements
     * @return ApplicationStateEnum
     */
    private function raiseStateToMaximalMetRequirements(
        ApplicationStateEnum $applicationState,
        array $unmetStateRequirements
    ): ApplicationStateEnum {
        for (
            $stateValue = ApplicationStateEnum::WAITING()->getValue();
            $stateValue <= ApplicationStateEnum::FULFILLED()->getValue();
            $stateValue++
        ) {
            if (count($unmetStateRequirements[$stateValue]) > 0) {
                return $applicationState;
            }
            $applicationState = ApplicationStateEnum::getMax($applicationState, new ApplicationStateEnum($stateValue));
        }

        return $applicationState;
    }

    private function areAllAdditionChoicesPayed(ApplicationEntity $application, AdditionEntity $addition): bool
    {
        $notPayedChoices = array_filter(
            $application->getAdditionChoices($addition->getId()),
            static fn(ChoiceEntity $choice): bool => !$choice->isPayed()
        );

        return 0 === count($notPayedChoices);
    }

    private function resolveReservations(
        ApplicationStateEnum $applicationState,
        ApplicationEntity $application
    ): ApplicationStateEnum {
        if (!$applicationState->isReserved()) {
            return $applicationState;
        }
        if (null !== $application->getReservation()) {
            return ApplicationStateEnum::DELEGATED();
        }

        return ApplicationStateEnum::RESERVED();
    }

    public function canStateBeChanged(
        ApplicationStateEnum $applicationState
    ): bool {
        return $applicationState->isIssued();
    }
}
