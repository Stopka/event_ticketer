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
        $this->appendSubmitControls($form, $this->earlyEntity ? 'Upravit' : 'Vytvořit', [$this, 'submitClicked']);
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
    $result = [null => 'Vytvořit novou'];
    $waves = $this->earlyWaveDao->getEventEearlyWaves($this->eventEntity);
    foreach ($waves as $wave){
        $result[$wave->getId()] = $wave->getName().' - '.$this->dateFormatter->getDateString($wave->getStartDate());
    }
    return $result;
}

    protected function appendEarlyControls(Form $form) {
        $form->addGroup("Přednostník")
            ->setOption($form::OPTION_KEY_LOGICAL, true);
        $form->addText('firstName', 'Jméno')
            ->setRequired(false);
        $form->addText('lastName', 'Příjmení')
            ->setRequired(false);
        $form->addEmail('email', 'Email')
            ->setRequired(true);
        $form->addSelect('earlyWaveId', 'Vlna přednostníků', $this->getEarlyFormSelectArray($this->eventEntity))
            ->setOption($form::OPTION_KEY_DESCRIPTION, "Přidejte přednostníka do již existující vlny, nebo vytvořte vlnu novou")
            ->setDefaultValue(null)
            ->setRequired(false)
            ->addCondition($form::FILLED)
            ->toggle('earlyWaveControlGroup', false);
        $form->addGroup("Nová vlna přednostníků")
            ->setOption($form::OPTION_KEY_ID, 'earlyWaveControlGroup');
        $wave = $form->addContainer('earlyWave');
        $wave->addText('name', 'Název')
            ->setOption($form::OPTION_KEY_DESCRIPTION, 'Kdy se rozešlou přihlášky')
            ->setRequired(false);
        $wave->addDate('startDate', 'Začátek', DateInput::TYPE_DATE)
            ->setOption($form::OPTION_KEY_DESCRIPTION, 'Kdy se rozešlou přihlášky a uživatelé se vpustí do registrace')
            ->setDefaultValue(new \DateTime())
            ->setRequired(false)
            ->addRule($form::VALID)
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
            $this->getPresenter()->flashMessage('Přídavek byl upraven', 'success');
            $this->getPresenter()->redirect('Early:default', [$this->eventEntity->getId()]);
        } else {
            $addition = $this->earlyManager->createEarlyFromEarlyForm($values,$this->eventEntity);
            $this->getPresenter()->flashMessage('Přídavek byl vytvořen', 'success');
            $this->getPresenter()->redirect('Early:default', [$this->eventEntity->getId()]);
        }
    }

}