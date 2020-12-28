<?php

declare(strict_types=1);

namespace Ticketer\Modules\AdminModule\Controls\Forms;

use Ticketer\Controls\FlashMessageTypeEnum;
use Ticketer\Controls\Forms\Form;
use Ticketer\Controls\Forms\FormWrapperDependencies;
use Ticketer\Model\Database\Enums\ApplicationStateEnum;
use Ticketer\Model\OccupancyIcons;
use Ticketer\Model\Database\Daos\CurrencyDao;
use Ticketer\Model\Database\Entities\AdditionEntity;
use Ticketer\Model\Database\Entities\ApplicationEntity;
use Ticketer\Model\Database\Entities\EventEntity;
use Ticketer\Model\Database\Managers\AdditionManager;
use Nette\Forms\Controls\SubmitButton;

class AdditionFormWrapper extends FormWrapper
{

    private AdditionManager $additionManager;

    private ?EventEntity $eventEntity = null;

    private ?AdditionEntity $additionEntity = null;

    private OccupancyIcons $occupancyIcons;

    private CurrencyDao $currecyDao;

    /**
     * AdditionFormWrapper constructor.
     * @param FormWrapperDependencies $formWrapperDependencies
     * @param AdditionManager $additionManager
     * @param OccupancyIcons $occupancyIcons
     * @param CurrencyDao $currencyDao
     */
    public function __construct(
        FormWrapperDependencies $formWrapperDependencies,
        AdditionManager $additionManager,
        OccupancyIcons $occupancyIcons,
        CurrencyDao $currencyDao
    ) {
        parent::__construct($formWrapperDependencies);
        $this->additionManager = $additionManager;
        $this->occupancyIcons = $occupancyIcons;
        $this->currecyDao = $currencyDao;
    }

    public function setEventEntity(?EventEntity $eventEntity): void
    {
        $this->eventEntity = $eventEntity;
    }

    public function setAdditionEntity(?AdditionEntity $additionEntity): void
    {
        $this->additionEntity = $additionEntity;
        if (null !== $additionEntity) {
            $this->setEventEntity($additionEntity->getEvent());
        }
    }

    /**
     * @param Form $form
     */
    protected function appendFormControls(Form $form): void
    {
        $this->appendAdditionControls($form);
        $this->appendSubmitControls(
            $form,
            null !== $this->additionEntity ? 'Form.Action.Edit' : 'Form.Action.Create',
            [$this, 'submitClicked']
        );
        $this->loadData($form);
    }

    protected function loadData(Form $form): void
    {
        if (null === $this->additionEntity) {
            return;
        }
        $values = $this->additionEntity->getValueArray();
        $form->setDefaults($values);
    }

    /**
     * @param array<mixed> $values
     * @return array<mixed>
     */
    protected function preprocessData(array $values): array
    {
        if (!(bool)$values['requiredForState']) {
            $values['requiredForState'] = null;
        }
        if (!(bool)$values['enoughForState']) {
            $values['enoughForState'] = null;
        }

        return $values;
    }

    protected function appendAdditionControls(Form $form): void
    {
        $form->addGroup("Entity.Singular.Addition")
            ->setOption($form::OPTION_KEY_LOGICAL, true);
        $form->addText('name', 'Attribute.Name')
            ->setRequired();
        $form->addSelect(
            'requiredForState',
            'Attribute.Addition.RequiredForState',
            [
                null => 'Value.ForState.None',
                ApplicationStateEnum::OCCUPIED => 'Value.ForState.Occupied',
                ApplicationStateEnum::FULFILLED => 'Value.ForState.Fulfilled',
            ]
        )
            ->setOption($form::OPTION_KEY_DESCRIPTION, "Form.Addition.Description.RequiredForState")
            ->setDefaultValue(null)
            ->setRequired(false);
        $form->addSelect(
            'enoughForState',
            'Attribute.Addition.EnoughForState',
            [
                null => 'Value.ForState.None',
                ApplicationStateEnum::OCCUPIED => 'Value.ForState.Occupied',
                ApplicationStateEnum::FULFILLED => 'Value.ForState.Fulfilled',
            ]
        )
            ->setOption($form::OPTION_KEY_DESCRIPTION, "Form.Addition.Description.EnoughForState")
            ->setDefaultValue(null)
            ->setRequired(false);
        $form->addCheckboxList('visible', 'Attribute.Addition.Visible', AdditionEntity::getVisiblePlaces())
            ->setDefaultValue(array_keys(AdditionEntity::getVisiblePlaces()))
            ->setOption($form::OPTION_KEY_DESCRIPTION, "Form.Addition.Description.Visible");
        $form->addText('minimum', 'Attribute.Addition.Minimum')
            ->setOption($form::OPTION_KEY_DESCRIPTION, "Form.Addition.Description.Minimum")
            ->setOption($form::MIME_TYPE, "number")
            ->setDefaultValue(0)
            ->setRequired()
            ->addRule($form::INTEGER)
            ->addRule($form::RANGE, null, [0, null]);
        $form->addText('maximum', 'Attribute.Addition.Maximum')
            ->setOption($form::OPTION_KEY_DESCRIPTION, "Form.Addition.Description.Maximum")
            ->setOption($form::MIME_TYPE, "number")
            ->setDefaultValue(1)
            ->setRequired()
            ->addRule($form::INTEGER)
            ->addRule($form::RANGE, null, [1, null]);
    }

    /**
     * @param SubmitButton $button
     * @throws \Exception
     * @throws \Nette\Application\AbortException
     */
    protected function submitClicked(SubmitButton $button): void
    {
        $form = $button->getForm();
        if (null === $form || null === $this->eventEntity) {
            return;
        }
        /** @var array<mixed> $values */
        $values = $form->getValues('array');
        $values = $this->preprocessData($values);
        if (null !== $this->additionEntity) {
            $this->additionManager->editAdditionFromEventForm($values, $this->additionEntity);
            $this->getPresenter()->flashTranslatedMessage(
                'Form.Addition.Message.Edit.Success',
                FlashMessageTypeEnum::SUCCESS()
            );
            $this->getPresenter()->redirect('Addition:default', [$this->eventEntity->getId()]);
        } else {
            $addition = $this->additionManager->createAdditionFromEventForm($values, $this->eventEntity);
            $this->getPresenter()->flashTranslatedMessage(
                'Form.Addition.Message.Create.Success',
                FlashMessageTypeEnum::SUCCESS()
            );
            $this->getPresenter()->redirect('Option:default', [$addition->getId()]);
        }
    }
}
