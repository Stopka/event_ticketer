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
use App\Model\Persistence\Entity\AdditionEntity;
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

    /**
     * EventFormWrapper constructor.
     * @param AdditionManager $additionManager
     * @param $occupancyIcons OccupancyIcons
     */
    public function __construct(AdditionManager $additionManager) {
        parent::__construct();
        $this->additionManager = $additionManager;
    }

    public function setEventEntity(?EventEntity $eventEntity): void{
        $this->eventEntity = $eventEntity;
    }

    public function setAdditionEntity(?AdditionEntity $additionEntity): void{
        $this->additionEntity = $additionEntity;
        if($additionEntity) {
            $this->setEventEntity($additionEntity->getEvent());
        }
    }

    /**
     * @param Form $form
     */
    protected function appendFormControls(Form $form) {
        $this->appendEventControls($form);
        $this->appendSubmitControls($form, $this->additionEntity?'Upravit':'Vytvořit', [$this, 'submitClicked']);
        $this->loadData($form);
    }

    protected function loadData(Form $form){
        if(!$this->additionEntity){
            return;
        }
        $values=$this->additionEntity->getValueArray();
        $form->setDefaults($values);
    }

    protected function preprocessData(array $values):array{

        return $values;
    }

    protected function appendEventControls(Form $form) {
        //$form->addGroup("Přídavek");
        $form->addText('name','Název')
            ->setRequired();
    }

    /**
     * @param SubmitButton $button
     */
    protected function submitClicked(SubmitButton $button) {
        $form = $button->getForm();
        $values = $form->getValues(true);
        $values = $this->preprocessData($values);
        if($this->additionEntity) {
            $this->additionManager->editAdditionFromEventForm($values, $this->additionEntity);
            $this->getPresenter()->flashMessage('Přídavek byl upraven', 'success');
            $this->getPresenter()->redirect('Addition:default',[$this->event->getId()]);
        }else{
            $event = $this->additionManager->createAdditionFromEventForm($values);
            $this->getPresenter()->flashMessage('Přídavek byl vytvořen', 'success');
            $this->getPresenter()->redirect('Addition:default',[$event->getId()]);
        }
    }

}