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
use App\Model\DateFormatter;
use App\Model\Persistence\Dao\EarlyWaveDao;
use App\Model\Persistence\Entity\EarlyEntity;
use App\Model\Persistence\Entity\EventEntity;
use App\Model\Persistence\Manager\EarlyManager;
use Nette\Forms\Controls\SubmitButton;
use Vodacek\Forms\Controls\DateInput;

class EarlyFormWrapper extends FormWrapper {

    /** @var  EarlyManager */
    private $earlyManager;

    /** @var  EventEntity */
    private $eventEntity;

    /** @var  EarlyEntity */
    private $earlyEntity;

    /** @var  EarlyWaveDao */
    private $earlyWaveDao;

    /** @var DateFormatter */
    private $dateFormatter;


    public function __construct(FormWrapperDependencies $formWrapperDependencies, EarlyManager $earlyManager, EarlyWaveDao $earlyWaveDao, DateFormatter $dateFormater) {
        parent::__construct($formWrapperDependencies);
        $this->earlyManager = $earlyManager;
        $this->earlyWaveDao = $earlyWaveDao;
        $this->dateFormatter = $dateFormater;
    }

    public function setEventEntity(?EventEntity $eventEntity): void {
        $this->eventEntity = $eventEntity;
    }

    public function getEventEntity(): ?EventEntity{
        return $this->eventEntity;
    }

    public function setEarlyEntity(?EarlyEntity $earlyEntity): void {
        $this->earlyEntity = $earlyEntity;
        if ($earlyEntity) {
            $wave = $earlyEntity->getEarlyWave();
            if($wave) {
                $this->setEventEntity($wave->getEvent());
            }
        }
    }

    /**
     * @param Form $form
     */
    protected function appendFormControls(Form $form) {
        $this->appendEarlyControls($form);
        $this->appendSubmitControls($form, $this->earlyEntity ? 'Form.Action.Edit' : 'Form.Action.Create', [$this, 'submitClicked']);
        $this->loadData($form);
    }

    protected function loadData(Form $form) {
        if (!$this->earlyEntity) {
            return;
        }
        $values = $this->earlyEntity->getValueArray(null,['earlyWave']);
        $wave = $this->earlyEntity->getEarlyWave();
        $values['earlyWaveId'] = $wave?$wave->getId():null;
        $form->setDefaults($values);

    }

    protected function preprocessData(array $values): array {

        return $values;
    }

    /**
     * @param EventEntity $eventEntity
     * @return string[] earlyID => earlyTitle
     */
    protected function getEarlyFormSelectArray(): array{
    $result = [null => 'Form.Early.EarlyWave.CreateNew'];
    $waves = $this->earlyWaveDao->getEventEearlyWaves($this->eventEntity);
    foreach ($waves as $wave){
        $result[$wave->getId()] = $wave->getName().' - '.$this->dateFormatter->getDateString($wave->getStartDate());
    }
    return $result;
}

    protected function appendEarlyControls(Form $form) {
        $form->addGroup("Entity.Singular.Early")
            ->setOption($form::OPTION_KEY_LOGICAL, true);
        $form->addText('firstName', 'Attribute.Person.FirstName')
            ->setRequired(false);
        $form->addText('lastName', 'Attribute.Person.LastName')
            ->setRequired(false);
        $form->addEmail('email', 'Attribute.Person.Email')
            ->setRequired(true);
        $form->addSelect('earlyWaveId', 'Entity.Singular.EarlyWave', $this->getEarlyFormSelectArray($this->eventEntity))
            ->setOption($form::OPTION_KEY_DESCRIPTION, "Form.Early.Description.EarlyWave")
            ->setDefaultValue(null)
            ->setRequired(false)
            ->addCondition($form::FILLED)
            ->toggle('earlyWaveControlGroup', false);
        $form->addGroup("Form.Early.Group.NewEarlyWave")
            ->setOption($form::OPTION_KEY_ID, 'earlyWaveControlGroup');
        $wave = $form->addContainer('earlyWave');
        $wave->addText('name', 'Attribute.Name')
            ->setRequired(false);
        $wave->addDate('startDate', 'Attribute.Event.StartDate', DateInput::TYPE_DATE)
            ->setOption($form::OPTION_KEY_DESCRIPTION, 'Form.Early.Description.StartDate')
            ->setDefaultValue(new \DateTime())
            ->setRequired(false)
            ->addRule($form::VALID,'Form.Rule.Date')
            ->addConditionOn($form["earlyWaveId"], $form::FILLED)
            ->elseCondition()
            ->addRule($form::FILLED);
    }

    /**
     * @param SubmitButton $button
     */
    protected function submitClicked(SubmitButton $button) {
        $form = $button->getForm();
        $values = $form->getValues(true);
        $values = $this->preprocessData($values);
        if ($this->earlyEntity) {
            $this->earlyManager->editEarlyFromEarlyForm($values, $this->earlyEntity, $this->eventEntity);
            $this->getPresenter()->flashTranslatedMessage('Form.Early.Message.Edit.Success', self::FLASH_MESSAGE_TYPE_SUCCESS);
            $this->getPresenter()->redirect('Early:default', [$this->eventEntity->getId()]);
        } else {
            $addition = $this->earlyManager->createEarlyFromEarlyForm($values,$this->eventEntity);
            $this->getPresenter()->flashTranslatedMessage('Form.Early.Message.Create.Success', self::FLASH_MESSAGE_TYPE_SUCCESS);
            $this->getPresenter()->redirect('Early:default', [$this->eventEntity->getId()]);
        }
    }

}