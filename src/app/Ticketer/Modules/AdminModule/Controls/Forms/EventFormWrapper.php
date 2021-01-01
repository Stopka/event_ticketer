<?php

declare(strict_types=1);

namespace Ticketer\Modules\AdminModule\Controls\Forms;

use DateTimeImmutable;
use Nette\Forms\Controls\Checkbox;
use Ticketer\Controls\FlashMessageTypeEnum;
use Ticketer\Controls\Forms\Form;
use Ticketer\Controls\Forms\FormWrapperDependencies;
use Ticketer\Model\OccupancyIcons;
use Ticketer\Model\Database\Entities\EventEntity;
use Ticketer\Model\Database\Managers\EventManager;
use Nette\Forms\Controls\SubmitButton;

class EventFormWrapper extends FormWrapper
{

    private EventManager $eventManager;

    private OccupancyIcons $occupancyIcons;

    private ?EventEntity $event = null;

    /**
     * EventFormWrapper constructor.
     * @param FormWrapperDependencies $formWrapperDependencies
     * @param EventManager $additionManager
     * @param OccupancyIcons $occupancyIcons
     */
    public function __construct(
        FormWrapperDependencies $formWrapperDependencies,
        EventManager $additionManager,
        OccupancyIcons $occupancyIcons
    ) {
        parent::__construct($formWrapperDependencies);
        $this->eventManager = $additionManager;
        $this->occupancyIcons = $occupancyIcons;
    }

    public function setEvent(?EventEntity $event): void
    {
        $this->event = $event;
    }

    /**
     * @param Form $form
     */
    protected function appendFormControls(Form $form): void
    {
        $this->appendEventControls($form);
        $this->appendSubmitControls(
            $form,
            null !== $this->event ? 'Form.Action.Edit' : 'Form.Action.Create',
            [$this, 'submitClicked']
        );
        $this->loadData($form);
    }

    protected function loadData(Form $form): void
    {
        if (null === $this->event) {
            return;
        }
        $values = $this->event->getValueArray();
        $values['limitCapacity'] = null !== $this->event->getCapacity();
        if (null === $this->event->getCapacity()) {
            unset($values['capacity']);
        }
        $values['public'] = null !== $this->event->getStartDate();
        if (null === $this->event->getStartDate()) {
            unset($values['startDate']);
        }
        $form->setDefaults($values);
    }

    /**
     * @param array<mixed> $values
     * @return array<mixed>
     */
    protected function preprocessData(array $values): array
    {
        if (!(bool)$values['limitCapacity']) {
            $values['capacity'] = null;
        }
        if (!(bool)$values['public']) {
            $values['startDate'] = null;
        }

        return $values;
    }

    protected function appendEventControls(Form $form): void
    {
        $form->addText('name', 'Attribute.Name')
            ->setRequired();
        $form->addRadioList(
            'occupancyIcon',
            'Attribute.Event.OccupancyIcon',
            $this->occupancyIcons->getLabeledIcons()
        )
            ->setRequired();
        $form->addExtendedCheckbox('limitCapacity', 'Form.Event.Attribute.LimitCapacity')
            ->setOption($form::OPTION_KEY_DESCRIPTION, "Form.Event.Description.LimitCapacity")
            ->addCondition($form::EQUAL, true)
            ->toggle('capacityControlGroup');
        /** @var Checkbox $limitCapacityControl */
        $limitCapacityControl = $form['limitCapacity'];
        $form->addText('capacity', 'Attribute.Event.Capacity')
            ->setDefaultValue(10)
            ->setOption($form::OPTION_KEY_DESCRIPTION, 'Form.Event.Description.Capacity')
            ->setOption($form::OPTION_KEY_TYPE, 'number')
            ->setOption($form::OPTION_KEY_ID, 'capacityControlGroup')
            ->addConditionOn($limitCapacityControl, $form::EQUAL, true)
            ->addRule($form::FILLED)
            ->addRule($form::INTEGER)
            ->addRule($form::RANGE, null, [1, null]);
        $form->addExtendedCheckbox('public', 'Form.Event.Attribute.Public')
            ->setOption($form::OPTION_KEY_DESCRIPTION, "Form.Event.Description.Public")
            ->addCondition($form::EQUAL, true)
            ->toggle('startDateControlGroup');

        /** @var Checkbox $publicControl */
        $publicControl = $form["public"];
        $form->addDate('startDate', 'Attribute.Event.StartDate')
            ->setOption($form::OPTION_KEY_DESCRIPTION, 'Form.Event.Description.StartDate')
            ->setOption($form::OPTION_KEY_ID, 'startDateControlGroup')
            ->setDefaultValue(new DateTimeImmutable())
            ->setRequired(false)
            //->addRule($form::VALID, 'Form.Rule.Date')
            ->addConditionOn($publicControl, $form::EQUAL, true)
            ->addRule($form::FILLED);
    }

    /**
     * @param SubmitButton $button
     * @throws \Exception
     */
    protected function submitClicked(SubmitButton $button): void
    {
        $form = $button->getForm();
        if (null === $form) {
            return;
        }
        /** @var array<mixed> $values */
        $values = $form->getValues('array');
        $values = $this->preprocessData($values);
        if (null !== $this->event) {
            $this->eventManager->editEventFromEventForm($values, $this->event);
            $this->getPresenter()->flashTranslatedMessage(
                'Form.Event.Message.Edit.Success',
                FlashMessageTypeEnum::SUCCESS()
            );
            $this->getPresenter()->redirect('this');
        } else {
            $event = $this->eventManager->createEventFromEventForm($values);
            $this->getPresenter()->flashTranslatedMessage(
                'Form.Event.Message.Create.Success',
                FlashMessageTypeEnum::SUCCESS()
            );
            $this->getPresenter()->redirect('Addition:default', [$event->getId()->toString()]);
        }
    }
}
