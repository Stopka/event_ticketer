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
    public function __construct(AdditionManager $additionManager, OccupancyIcons $occupancyIcons,CurrencyDao $currencyDao) {
        parent::__construct();
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
        $this->appendSubmitControls($form, $this->additionEntity ? 'Upravit' : 'Vytvořit', [$this, 'submitClicked']);
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
        $form->addGroup("Přídavek")
            ->setOption($form::OPTION_KEY_LOGICAL, true);
        $form->addText('name', 'Název')
            ->setRequired();
        $form->addSelect('requiredForState', 'Vyžadováno pro', [
            null => 'Nic',
            ApplicationEntity::STATE_RESERVED => 'Závaznou rezervaci',
            ApplicationEntity::STATE_FULFILLED => 'Celkové dokončení'
        ])
            ->setOption($form::OPTION_KEY_DESCRIPTION, "Splnění tohoto přídavku je povinné pro přechod přihlášky do zvoleného stavu")
            ->setDefaultValue(null)
            ->setRequired(false);
        $form->addSelect('enoughForState', 'Dostatečné pro', [
            null => 'Nic',
            ApplicationEntity::STATE_RESERVED => 'Závaznou rezervaci',
            ApplicationEntity::STATE_FULFILLED => 'Celkové dokončení'
        ])
            ->setOption($form::OPTION_KEY_DESCRIPTION, "Splnění tohoto přídavku je dostatečné pro přechod přihlášky do zvoleného stavu")
            ->setDefaultValue(null)
            ->setRequired(false);
        $form->addCheckbox('visible', 'Viditelné v registraci')
            ->setDefaultValue(true)
            ->setOption($form::OPTION_KEY_DESCRIPTION, "Má být přídavek vidět ve veřejném registračním formuláři?");
        $form->addCheckbox('hidden', 'Schovat v náhledu')
            ->setDefaultValue(false)
            ->setOption($form::OPTION_KEY_DESCRIPTION, "Má být přídavek schován ve uživatelském náhledu objednávky?");
        $form->addText('minimum', 'Minimum')
            ->setOption($form::OPTION_KEY_DESCRIPTION, "Kolik následujícíh možností musí uživatel při registraci minimálně zvolit")
            ->setOption($form::MIME_TYPE, "number")
            ->setDefaultValue(0)
            ->setRequired()
            ->addRule($form::INTEGER)
            ->addRule($form::RANGE, null, [0, null]);
        $form->addText('maximum', 'Maximum')
            ->setOption($form::OPTION_KEY_DESCRIPTION, "Kolik následujícíh možností může uživatel při registraci maximálně zvolit")
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
            $this->getPresenter()->flashMessage('Přídavek byl upraven', 'success');
            $this->getPresenter()->redirect('Addition:default', [$this->event->getId()]);
        } else {
            $addition = $this->additionManager->createAdditionFromEventForm($values,$this->eventEntity);
            $this->getPresenter()->flashMessage('Přídavek byl vytvořen', 'success');
            $this->getPresenter()->redirect('Addition:default', [$this->eventEntity->getId()]);
        }
    }

}