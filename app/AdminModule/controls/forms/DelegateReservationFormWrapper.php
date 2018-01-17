<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 15.1.17
 * Time: 15:06
 */

namespace App\AdminModule\Controls\Forms;


use App\Controls\Forms\Form;
use App\Controls\Forms\FormWrapperDependencies;
use App\Model\Exception\EmptyException;
use App\Model\Exception\InvalidInputException;
use App\Model\Persistence\Dao\ApplicationDao;
use App\Model\Persistence\Dao\ReservationDao;
use App\Model\Persistence\Entity\ApplicationEntity;
use App\Model\Persistence\Entity\EventEntity;
use App\Model\Persistence\Manager\ReservationManager;
use Nette\Forms\Controls\SubmitButton;

class DelegateReservationFormWrapper extends FormWrapper {
    use TAppendDelegateReservation;

    /** @var  ApplicationDao */
    private $applicationDao;

    /** @var  ReservationManager */
    private $reservationManager;

    /** @var ApplicationEntity[] */
    private $applications;

    /** @var ReservationDao */
    private $reservationDao;

    /** @var EventEntity */
    private $event;

    protected function getReservationDao(): ReservationDao {
        return $this->reservationDao;
    }


    public function __construct(
        FormWrapperDependencies $formWrapperDependencies,
        ApplicationDao $applicationDao,
        ReservationManager $reservationManager,
        ReservationDao $reservationDao
    ) {
        parent::__construct($formWrapperDependencies);
        $this->reservationManager = $reservationManager;
        $this->applicationDao = $applicationDao;
        $this->reservationDao = $reservationDao;
    }

    /**
     * @param ApplicationEntity[] $applications
     */
    public function setApplications(array $applications): void {
        if (!count($applications)) {
            throw new EmptyException("Error.Reservation.Application.Empty");
        }
        foreach ($applications as $application) {
            if (!$this->event) {
                $this->event = $application->getEvent();
            }
            if ($this->event->getId() !== $application->getEvent()->getId()) {
                throw new InvalidInputException("Error.Reservation.Application.InvalidInput");
            }
            if (!in_array($application->getState(), ApplicationEntity::getStatesReserved())) {
                throw new InvalidInputException("Error.Reservation.Application.InvalidState");
            }
        }
        $this->applications = $applications;
    }

    /**
     * @param Form $form
     */
    protected function appendFormControls(Form $form) {
        $form->addGroup('Entity.Singular.Reservation')
            ->setOption('visual', false);
        $this->appendApplicationList($form);
        $this->appendDelegateControls($form);
        $this->appendSubmitControls($form, 'Form.Action.Delegate', [$this, 'reserveClicked']);
    }

    protected function appendApplicationList(Form $form) {
        $list = [];
        foreach ($this->applications as $application) {
            $list[$application->getId()] = $application->getNumber();
        }
        $form->addCheckboxList('applications', 'Entity.Plural.Application', $list)
            ->setDefaultValue(array_keys($list))
            ->setRequired(false)
            ->setOmitted()
            ->setDisabled();

    }

    /**
     * @param SubmitButton $button
     * @throws \Nette\Application\AbortException
     * @throws \Exception
     */
    public function reserveClicked(SubmitButton $button) {
        $form = $button->getForm();
        $values = $form->getValues(true);
        $this->reservationManager->delegateNewReservations($this->applications, $values);
        $this->getPresenter()->flashTranslatedMessage('Form.Reservation.Message.Delegate.Success', self::FLASH_MESSAGE_TYPE_SUCCESS);
        $this->getPresenter()->redirect('Application:', $this->event->getId());
    }

}