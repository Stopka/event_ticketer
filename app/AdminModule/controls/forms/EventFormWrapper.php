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
use App\Model\OccupancyIcons;
use App\Model\Persistence\Entity\EventEntity;
use App\Model\Persistence\Manager\EventManager;
use Nette\Forms\Controls\SubmitButton;
use Vodacek\Forms\Controls\DateInput;

class EventFormWrapper extends FormWrapper {

    /** @var  EventManager */
    private $eventManager;

    /** @var  OccupancyIcons */
    private $occupancyIcons;

    /** @var  EventEntity */
    private $event;

    /**
     * EventFormWrapper constructor.
     * @param EventManager $additionManager
     * @param $occupancyIcons OccupancyIcons
     */
    public function __construct(FormWrapperDependencies $formWrapperDependencies, EventManager $additionManager, OccupancyIcons $occupancyIcons) {
        parent::__construct($formWrapperDependencies);
        $this->eventManager = $additionManager;
        $this->occupancyIcons = $occupancyIcons;
    }

    public function setEvent(?EventEntity $event): void {
        $this->event = $event;
    }

    /**
     * @param Form $form
     */
    protected function appendFormControls(Form $form) {
        $this->appendEventControls($form);
        $this->appendSubmitControls($form, $this->event ? 'Form.Action.Edit' : 'Form.Action.Create', [$this, 'submitClicked']);
        $this->loadData($form);
    }

    protected function loadData(Form $form) {
        if (!$this->event) {
            return;
        }
        $values = $this->event->getValueArray();
        $values['limitCapacity'] = $this->event->getCapacity() !== null;
        if (!$this->event->getCapacity()) {
            unset($values['capacity']);
        }
        $values['public'] = $this->event->getStartDate() !== null;
        if (!$this->event->getStartDate()) {
            unset($values['startDate']);
        }
        $form->setDefaults($values);
    }

    protected function preprocessData(array $values): array {
        if (!$values['limitCapacity']) {
            $values['capacity'] = null;
        }
        if (!$values['public']) {
            $values['startDate'] = null;
        }
        return $values;
    }

    protected function appendEventControls(Form $form) {
        //$form->addGroup("Událost");
        $form->addText('name', 'Entity.Name')
            ->setRequired();
        $form->addRadioList('occupancyIcon', 'Entity.Event.OccupancyIcon', $this->occupancyIcons->getLabeledIcons())
            ->setRequired();
        $form->addCheckbox('limitCapacity', 'Form.Event.Attribute.LimitCapacity')
            ->setOption($form::OPTION_KEY_DESCRIPTION, "Form.Event.Description.LimitCapacity")
            ->addCondition($form::EQUAL, true)
            ->toggle('capacityControlGroup');
        $form->addText('capacity', 'Entity.Event.Capacity')
            ->setDefaultValue(10)
            ->setOption($form::OPTION_KEY_DESCRIPTION, 'Form.Event.Description.Capacity')
            ->setOption($form::OPTION_KEY_TYPE, 'number')
            ->setOption($form::OPTION_KEY_ID, 'capacityControlGroup')
            ->addConditionOn($form['limitCapacity'], $form::EQUAL, true)
            ->addRule($form::FILLED)
            ->addRule($form::INTEGER)
            ->addRule($form::RANGE, null, [1, null]);
        $form->addCheckbox('public', 'Form.Event.Attribute.Public')
            ->setOption($form::OPTION_KEY_DESCRIPTION, "Form.Event.Description.Public")
            ->addCondition($form::EQUAL, true)
            ->toggle('startDateControlGroup');
        $form->addDate('startDate', 'Entity.Event.StartDate', DateInput::TYPE_DATE)
            ->setOption($form::OPTION_KEY_DESCRIPTION, 'Form.Event.Description.StartDate')
            ->setOption($form::OPTION_KEY_ID, 'startDateControlGroup')
            ->setDefaultValue(new \DateTime())
            ->setRequired(false)
            ->addRule($form::VALID,'Form.Rule.Date')
            ->addConditionOn($form["public"], $form::EQUAL, true)
            ->addRule($form::FILLED);
    }

    /**
     * @param SubmitButton $button
     */
    protected function submitClicked(SubmitButton $button) {
        $form = $button->getForm();
        $values = $form->getValues(true);
        $values = $this->preprocessData($values);
        if ($this->event) {
            $this->eventManager->editEventFromEventForm($values, $this->event);
            $this->getPresenter()->flashTranslatedMessage('Form.Event.Message.Edit.Success', 'success');
            $this->getPresenter()->redirect('this');
        } else {
            $event = $this->eventManager->createEventFromEventForm($values);
            $this->getPresenter()->flashTranslatedMessage('Form.Event.Message.Create.Success', 'success');
            $this->getPresenter()->redirect('Addition:default', [$event->getId()]);
        }
    }

}