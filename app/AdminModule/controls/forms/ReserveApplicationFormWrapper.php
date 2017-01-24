<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 15.1.17
 * Time: 15:06
 */

namespace App\AdminModule\Controls\Forms;


use App\Controls\Forms\AppendAdditionsControls;
use App\Controls\Forms\Form;
use App\Model\Entities\CurrencyEntity;
use App\Model\Entities\EventEntity;
use App\Model\Facades\ApplicationFacade;
use App\Model\Facades\CurrencyFacade;
use App\Model\Facades\OrderFacade;
use Nette\Forms\Controls\SubmitButton;

class ReserveApplicationFormWrapper extends FormWrapper {
    use AppendAdditionsControls;

    /** @var  EventEntity */
    private $event;

    /** @var  CurrencyEntity */
    private $currency;

    /** @var  CurrencyFacade */
    private $currencyFacade;

    /** @var  ApplicationFacade */
    private $applicationFacade;

    /** @var  OrderFacade */
    private $orderFacade;

    public function __construct(ApplicationFacade $applicationFacade,CurrencyFacade $currencyFacade, OrderFacade $orderFacade) {
        parent::__construct();
        $this->applicationFacade = $applicationFacade;
        $this->orderFacade = $orderFacade;
        $this->currencyFacade = $currencyFacade;
        $this->currency = $this->currencyFacade->getDefaultCurrency();

    }

    public function setEvent(EventEntity $event){
        $this->event = $event;
    }


    protected function getEvent() {
        return $this->event;
    }

    protected function getCurrency() {
        return $this->currency;
    }

    protected function getApplicationFacade() {
        return $this->applicationFacade;
    }


    /**
     * @param Form $form
     */
    protected function appendFormControls(Form $form) {
        $form->elementPrototype->setAttribute('data-price-currency', $this->currency->getSymbol());
        $form->addGroup('General')
            ->setOption('visual',false);
        $form->addText('count', 'Počet', null, 255)
            ->setType('number')
            ->setDefaultValue(1)
            ->setRequired()
            ->addRule($form::INTEGER)
            ->addRule($form::RANGE, NULL, [1, 100]);
        $form->addGroup('Volby')
            ->setOption('class', 'price_subspace');
        $this->appendAdditionsControls($form,$form);
        $this->appendSubmitControls($form, 'Rezervovat', [$this, 'reserveClicked']);
    }

    public function reserveClicked(SubmitButton $button){
        $form = $button->getForm();
        $values = $form->getValues(true);
        $this->orderFacade->createOrdersFromReserveForm($values,$this->event);
        $this->getPresenter()->flashMessage('Přihlášky byly vytvořeny','success');
        $this->getPresenter()->redirect('Application:',$this->event->getId());
    }

}