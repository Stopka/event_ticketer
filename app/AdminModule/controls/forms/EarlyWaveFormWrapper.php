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
use App\Model\Persistence\Entity\EarlyWaveEntity;
use App\Model\Persistence\Entity\EventEntity;
use App\Model\Persistence\Manager\EarlyWaveManager;
use Nette\Forms\Controls\SubmitButton;
use Vodacek\Forms\Controls\DateInput;

class EarlyWaveFormWrapper extends FormWrapper {

    /** @var  EarlyWaveManager */
    private $earlyWaveManager;

    /** @var  EventEntity */
    private $eventEntity;

    /** @var  EarlyWaveEntity */
    private $earlyWaveEntity;

    public function __construct(FormWrapperDependencies $formWrapperDependencies, EarlyWaveManager $earlyWaveManager) {
        parent::__construct($formWrapperDependencies);
        $this->earlyWaveManager = $earlyWaveManager;
    }

    public function setEventEntity(?EventEntity $eventEntity): void {
        $this->eventEntity = $eventEntity;
    }

    public function getEventEntity(): ?EventEntity {
        return $this->eventEntity;
    }

    public function setEarlyWaveEntity(?EarlyWaveEntity $earlyWaveEntity): void {
        $this->earlyWaveEntity = $earlyWaveEntity;
        if ($earlyWaveEntity) {
            $this->setEventEntity($earlyWaveEntity->getEvent());
        }
    }

    /**
     * @param Form $form
     */
    protected function appendFormControls(Form $form) {
        $this->appendEarlyWaveControls($form);
        $this->appendSubmitControls($form, $this->earlyWaveEntity ? 'Form.Action.Edit' : 'Form.Action.Create', [$this, 'submitClicked']);
        $this->loadData($form);
    }

    protected function loadData(Form $form) {
        if (!$this->earlyWaveEntity) {
            return;
        }
        $values = $this->earlyWaveEntity->getValueArray();
        $form->setDefaults($values);

    }

    protected function preprocessData(array $values): array {

        return $values;
    }

    protected function appendEarlyWaveControls(Form $form) {
        $form->addGroup("Entity.Singular.EarlyWave")
            ->setOption($form::OPTION_KEY_LOGICAL, true);
        $form->addText('name', 'Attribute.Name')
            ->setRequired(false);
        $form->addDate('startDate', 'Attribute.Event.StartDate', DateInput::TYPE_DATE)
            ->setOption($form::OPTION_KEY_DESCRIPTION, 'Form.EarlyWave.Description.StartDate')
            ->setDefaultValue(new \DateTime())
            ->setRequired(true)
            ->addRule($form::VALID,'Form.Rule.Date');
    }

    /**
     * @param SubmitButton $button
     */
    protected function submitClicked(SubmitButton $button) {
        $form = $button->getForm();
        $values = $form->getValues(true);
        $values = $this->preprocessData($values);
        if ($this->earlyWaveEntity) {
            $this->earlyWaveManager->editEarlyFromEarlyForm($values, $this->earlyWaveEntity, $this->eventEntity);
            $this->getPresenter()->flashTranslatedMessage('Form.EarlyWave.Message.Edit.Success', 'success');
            $this->getPresenter()->redirect('Early:default', [$this->eventEntity->getId()]);
        } else {
            $addition = $this->earlyWaveManager->createEarlyFromEarlyForm($values, $this->eventEntity);
            $this->getPresenter()->flashTranslatedMessage('Form.EarlyWave.Message.Create.Success', 'success');
            $this->getPresenter()->redirect('Early:default', [$this->eventEntity->getId()]);
        }
    }

}