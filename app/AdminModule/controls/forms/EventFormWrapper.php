<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 15.1.17
 * Time: 15:06
 */

namespace App\AdminModule\Controls\Forms;


use App\Controls\Forms\Form;
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
    public function __construct(EventManager $additionManager, OccupancyIcons $occupancyIcons) {
        parent::__construct();
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
        $this->appendSubmitControls($form, $this->event ? 'Upravit' : 'Vytvořit', [$this, 'submitClicked']);
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
        $form->addText('name', 'Název')
            ->setRequired();
        $form->addRadioList('occupancyIcon', 'Ikona obsazenosti', $this->occupancyIcons->getLabeledIcons())
            ->setRequired();
        $form->addCheckbox('limitCapacity', 'Omezit kapacitu')
            ->setOption($form::OPTION_KEY_DESCRIPTION, "Vydat jen určitý počet lístků")
            ->addCondition($form::EQUAL, true)
            ->toggle('capacityControlGroup');
        $form->addText('capacity', 'Kapacita')
            ->setDefaultValue(10)
            ->setOption($form::OPTION_KEY_DESCRIPTION, 'Celkové maximum míst v události')
            ->setOption($form::OPTION_KEY_TYPE, 'number')
            ->setOption($form::OPTION_KEY_ID, 'capacityControlGroup')
            ->addConditionOn($form['limitCapacity'], $form::EQUAL, true)
            ->addRule($form::FILLED)
            ->addRule($form::INTEGER)
            ->addRule($form::RANGE, null, [1, null]);
        $form->addCheckbox('public', 'Veřejný výdej')
            ->setOption($form::OPTION_KEY_DESCRIPTION, "Vydávat přihlášky široké veřejnosti")
            ->addCondition($form::EQUAL, true)
            ->toggle('startDateControlGroup');
        $form->addDate('startDate', 'Začátek', DateInput::TYPE_DATE)
            ->setOption($form::OPTION_KEY_DESCRIPTION, 'Od kdy budou přihlášky dostupné široké veřejnosti')
            ->setOption($form::OPTION_KEY_ID, 'startDateControlGroup')
            ->setDefaultValue(new \DateTime())
            ->setRequired(false)
            ->addRule($form::VALID)
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
            $this->getPresenter()->flashMessage('Událost byla upravena', 'success');
            $this->getPresenter()->redirect('this');
        } else {
            $event = $this->eventManager->createEventFromEventForm($values);
            $this->getPresenter()->flashMessage('Událost byla vytvořena', 'success');
            $this->getPresenter()->redirect('Addition:default', [$event->getId()]);
        }
    }

}