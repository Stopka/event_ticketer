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
use App\Model\Persistence\Dao\CurrencyDao;
use App\Model\Persistence\Entity\AdditionEntity;
use App\Model\Persistence\Entity\ApplicationEntity;
use App\Model\Persistence\Entity\EventEntity;
use App\Model\Persistence\Manager\AdditionManager;
use Nette\Forms\Controls\SubmitButton;

class AdditionFormWrapper extends FormWrapper {

    /** @var  AdditionManager */
    private $additionManager;

    /** @var  EventEntity */
    private $eventEntity;

    /** @var  AdditionEntity */
    private $additionEntity;

    /** @var int */
    private $counter = 0;

    /** @var  OccupancyIcons */
    private $occupancyIcons;

    /** @var  CurrencyDao */
    private $currecyDao;

    /**
     * EventFormWrapper constructor.
     * @param AdditionManager $additionManager
     * @param $occupancyIcons OccupancyIcons
     */
    public function __construct(FormWrapperDependencies $formWrapperDependencies, AdditionManager $additionManager, OccupancyIcons $occupancyIcons,CurrencyDao $currencyDao) {
        parent::__construct($formWrapperDependencies);
        $this->additionManager = $additionManager;
        $this->occupancyIcons = $occupancyIcons;
        $this->currecyDao = $currencyDao;
    }

    public function setEventEntity(?EventEntity $eventEntity): void {
        $this->eventEntity = $eventEntity;
    }

    public function setAdditionEntity(?AdditionEntity $additionEntity): void {
        $this->additionEntity = $additionEntity;
        if ($additionEntity) {
            $this->setEventEntity($additionEntity->getEvent());
        }
    }

    /**
     * @param Form $form
     */
    protected function appendFormControls(Form $form) {
        $this->appendAdditionControls($form);
        $this->appendSubmitControls($form, $this->additionEntity ? 'Form.Action.Edit' : 'Form.Action.Create', [$this, 'submitClicked']);
        $this->loadData($form);
    }

    protected function loadData(Form $form) {
        if (!$this->additionEntity) {
            return;
        }
        $values = $this->additionEntity->getValueArray();
        $form->setDefaults($values);

    }

    protected function preprocessData(array $values): array {
        if(!$values['requiredForState']){
            $values['requiredForState']=null;
        }
        if(!$values['enoughForState']){
            $values['enoughForState']=null;
        }
        return $values;
    }

    protected function appendAdditionControls(Form $form) {
        $form->addGroup("Entity.Singular.Addition")
            ->setOption($form::OPTION_KEY_LOGICAL, true);
        $form->addText('name', 'Attribute.Name')
            ->setRequired();
        $form->addSelect('requiredForState', 'Attribute.Addition.RequiredForState', [
            null => 'Nic',
            ApplicationEntity::STATE_RESERVED => 'Value.ForState.Reserved',
            ApplicationEntity::STATE_FULFILLED => 'Value.ForState.Fulfilled'
        ])
            ->setOption($form::OPTION_KEY_DESCRIPTION, "Form.Addition.Description.RequiredForState")
            ->setDefaultValue(null)
            ->setRequired(false);
        $form->addSelect('enoughForState', 'Attribute.Addition.EnoughForState', [
            null => 'Nic',
            ApplicationEntity::STATE_RESERVED => 'Value.ForState.Reserved',
            ApplicationEntity::STATE_FULFILLED => 'Value.ForState.Fulfilled'
        ])
            ->setOption($form::OPTION_KEY_DESCRIPTION, "Form.Addition.Description.EnoughForState")
            ->setDefaultValue(null)
            ->setRequired(false);
        $form->addCheckboxList('visible', 'Attribute.Addition.Visible',AdditionEntity::getVisiblePlaces())
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
     */
    protected function submitClicked(SubmitButton $button) {
        $form = $button->getForm();
        $values = $form->getValues(true);
        $values = $this->preprocessData($values);
        if ($this->additionEntity) {
            $this->additionManager->editAdditionFromEventForm($values, $this->additionEntity);
            $this->getPresenter()->flashTranslatedMessage('Form.Addition.Message.Edit.Success', self::FLASH_MESSAGE_TYPE_SUCCESS);
            $this->getPresenter()->redirect('Addition:default', [$this->eventEntity->getId()]);
        } else {
            $addition = $this->additionManager->createAdditionFromEventForm($values,$this->eventEntity);
            $this->getPresenter()->flashTranslatedMessage('Form.Addition.Message.Create.Success', self::FLASH_MESSAGE_TYPE_SUCCESS);
            $this->getPresenter()->redirect('Addition:default', [$this->eventEntity->getId()]);
        }
    }

}