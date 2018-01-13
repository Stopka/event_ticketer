<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 15.1.17
 * Time: 15:06
 */

namespace App\FrontModule\Controls\Forms;


use App\Controls\Forms\Form;
use App\Controls\Forms\FormWrapperDependencies;
use App\Model\Persistence\Entity\EarlyEntity;
use App\Model\Persistence\Entity\EventEntity;
use App\Model\Persistence\Manager\SubstituteManager;
use Nette\Forms\Controls\SubmitButton;

class SubstituteFormWrapper extends FormWrapper {

    /** @var  SubstituteManager */
    private $substituteManager;

    /** @var  EarlyEntity */
    private $early;

    /** @var  EventEntity */
    private $event;

    public function __construct(FormWrapperDependencies $formWrapperDependencies, SubstituteManager $substituteDao) {
        parent::__construct($formWrapperDependencies);
        $this->substituteManager = $substituteDao;
    }

    /**
     * @param \App\Model\Persistence\Entity\EarlyEntity $early
     */
    public function setEarly(EarlyEntity $early) {
        $this->early = $early;
        $wave = $early->getEarlyWave();
        if (!$wave)
            return;
        $this->event = $wave->getEvent();
    }

    /**
     * @param \App\Model\Persistence\Entity\EventEntity $event
     */
    public function setEvent(EventEntity $event) {
        $this->early = null;
        $this->event = $event;
    }

    protected function loadData(Form $form) {
        if ($this->early) {
            $form->setDefaults($this->early->getValueArray());
        }
    }

    /**
     * @param Form $form
     */
    protected function appendFormControls(Form $form) {
        $form->addText('firstName', 'Attribute.Person.FirstName', NULL, 255)
            ->setRequired()
            ->addRule($form::MAX_LENGTH, NULL, 255);
        $form->addText('lastName', 'Attribute.Person.LastName', NULL, 255)
            ->setRequired()
            ->addRule($form::MAX_LENGTH, NULL, 255);
        $form->addText('email', 'Attribute.Person.Email')
            ->setRequired()
            ->addRule($form::EMAIL);
        $form->addText('count', 'Attribute.Count')
            ->setType('number')
            ->setDefaultValue(1)
            ->setRequired()
            ->addRule($form::INTEGER)
            ->addRule($form::RANGE,NULL,[1,5]);
        $this->appendSubmitControls($form,'Form.Action.Register',[$this,'saveClicked']);
        $this->loadData($form);
    }

    public function saveClicked(SubmitButton $button){
        $form = $button->getForm();
        $values = $form->getValues(true);
        $this->substituteManager->createSubtituteFromForm($values, $this->event, $this->early);
        $this->getPresenter()->flashTranslatedMessage('Form.Substitute.Message.Create.Success', self::FLASH_MESSAGE_TYPE_SUCCESS);
        $this->getPresenter()->redirect('Homepage:');
    }
}