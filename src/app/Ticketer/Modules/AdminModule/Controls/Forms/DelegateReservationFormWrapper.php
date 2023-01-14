<?php

declare(strict_types=1);

namespace Ticketer\Modules\AdminModule\Controls\Forms;

use Exception;
use Nette\Application\AbortException;
use RuntimeException;
use Ticketer\Controls\FlashMessageTypeEnum;
use Ticketer\Controls\Forms\Form;
use Ticketer\Controls\Forms\FormWrapperDependencies;
use Ticketer\Model\Exceptions\EmptyException;
use Ticketer\Model\Exceptions\InvalidInputException;
use Ticketer\Model\Database\Entities\ApplicationEntity;
use Ticketer\Model\Database\Entities\EventEntity;
use Ticketer\Model\Database\Managers\ReservationManager;
use Nette\Forms\Controls\SubmitButton;

class DelegateReservationFormWrapper extends FormWrapper
{
    private ReservationManager $reservationManager;

    /** @var array<ApplicationEntity> */
    private array $applications;

    private ?EventEntity $event = null;

    private IDelegateReservationControlsBuilderFactory $delegateReservationControlsBuilderFactory;

    private ?DelegateReservationControlsBuilder $delegateReservationControlsBuilder = null;

    public function __construct(
        FormWrapperDependencies $formWrapperDependencies,
        ReservationManager $reservationManager,
        IDelegateReservationControlsBuilderFactory $delegateReservationControlsBuilderFactory
    ) {
        parent::__construct($formWrapperDependencies);
        $this->reservationManager = $reservationManager;
        $this->delegateReservationControlsBuilderFactory = $delegateReservationControlsBuilderFactory;
    }

    /**
     * @return DelegateReservationControlsBuilder
     */
    public function getDelegateReservationControlsBuilder(): DelegateReservationControlsBuilder
    {
        if (null === $this->event) {
            throw new RuntimeException('Missing event');
        }
        if (null === $this->delegateReservationControlsBuilder) {
            $builder = $this->delegateReservationControlsBuilderFactory->create($this->event);
            $this->delegateReservationControlsBuilder = $builder;
        }

        return $this->delegateReservationControlsBuilder;
    }


    /**
     * @param ApplicationEntity[] $applications
     * @throws EmptyException
     */
    public function setApplications(array $applications): void
    {
        if (0 === count($applications)) {
            throw new EmptyException("Error.Reservation.Empty");
        }
        foreach ($applications as $application) {
            if (null === $this->event) {
                $this->event = $application->getEvent();
            }
            $applicationEvent = $application->getEvent();
            if (
                null === $this->event
                || null === $applicationEvent
                || $this->event->getId() !== $applicationEvent->getId()
            ) {
                throw new InvalidInputException("Error.Reservation.Application.InvalidInput");
            }
            if (!$application->getState()->isReserved()) {
                throw new InvalidInputException("Error.Reservation.Application.InvalidState");
            }
        }
        $this->applications = $applications;
    }

    /**
     * @param Form $form
     */
    protected function appendFormControls(Form $form): void
    {
        $form->addGroup('Entity.Singular.Reservation')
            ->setOption('visual', false);
        $this->appendApplicationList($form);
        $this->getDelegateReservationControlsBuilder()
            ->appendDelegateControls($form);
        $this->appendSubmitControls($form, 'Form.Action.Delegate', [$this, 'reserveClicked']);
    }

    protected function appendApplicationList(Form $form): void
    {
        $list = [];
        foreach ($this->applications as $application) {
            $applicationId = $application->getId()->toString();
            $list[$applicationId] = $applicationId;
        }
        $form->addCheckboxList('applications', 'Entity.Plural.Application', $list)
            ->setDefaultValue(array_keys($list))
            ->setRequired(false)
            ->setOmitted()
            ->setDisabled();
    }

    /**
     * @param SubmitButton $button
     * @throws AbortException
     * @throws Exception
     */
    public function reserveClicked(SubmitButton $button): void
    {
        $form = $button->getForm();
        if (null === $form || null === $this->event) {
            return;
        }
        /** @var array<mixed> $values */
        $values = $form->getValues('array');
        $this->reservationManager->delegateNewReservations($this->applications, $values);
        $this->getPresenter()->flashTranslatedMessage(
            'Form.Reservation.Message.Delegate.Success',
            FlashMessageTypeEnum::SUCCESS()
        );
        $this->getPresenter()->redirect('Application:', $this->event->getId()->toString());
    }
}
