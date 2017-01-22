<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 15.1.17
 * Time: 15:06
 */

namespace App\CompanyModule\Controls\Forms;


use App\Controls\Forms\Form;
use App\Model\Entities\EarlyEntity;
use App\Model\Entities\EventEntity;
use App\Model\Facades\OrderFacade;
use Nette\Forms\Controls\SubmitButton;

class SubstituteFormWrapper extends FormWrapper {

    /** @var  OrderFacade */
    private $orderFacade;

    /** @var  EarlyEntity */
    private $early;

    /** @var  EventEntity */
    private $event;

    public function __construct(OrderFacade $orderFacade) {
        parent::__construct();
        $this->orderFacade = $orderFacade;
    }

    /**
     * @param EarlyEntity $early
     */
    public function setEarly(EarlyEntity $early) {
        $this->early = $early;
        $wave = $early->getEarlyWave();
        if (!$wave)
            return;
        $this->event = $wave->getEvent();
    }

    /**
     * @param EventEntity $event
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
        $form->addText('firstName', 'Jméno', NULL, 255)
            ->setRequired()
            ->addRule($form::MAX_LENGTH, NULL, 255);
        $form->addText('lastName', 'Příjmení', NULL, 255)
            ->setRequired()
            ->addRule($form::MAX_LENGTH, NULL, 255);
        $form->addText('email', 'Email')
            ->setRequired()
            ->addRule($form::EMAIL);
        $form->addText('count', 'Počet přihlášek')
            ->setType('number')
            ->setDefaultValue(1)
            ->setRequired()
            ->addRule($form::INTEGER)
            ->addRule($form::RANGE,NULL,[1,5]);
        $this->appendSubmitControls($form,'Registrovat',[$this,'saveClicked']);
        $this->loadData($form);
    }

    public function saveClicked(SubmitButton $button){
        $form = $button->getForm();
        $values = $form->getValues(true);
        $this->orderFacade->createSubtituteFromOrderForm($values, $this->event, $this->early);
        $this->getPresenter()->flashMessage('Byl(a) jste úspěšně zapsán(a) mezi náhradníky', 'success');
        $this->getPresenter()->redirect('this');
    }
}