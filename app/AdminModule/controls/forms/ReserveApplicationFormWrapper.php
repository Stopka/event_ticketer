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
use App\Model\Persistence\Dao\ApplicationDao;
use App\Model\Persistence\Dao\CurrencyDao;
use App\Model\Persistence\Entity\CurrencyEntity;
use App\Model\Persistence\Entity\EventEntity;
use App\Model\Persistence\Manager\OrderManager;
use Nette\Forms\Controls\SubmitButton;

class ReserveApplicationFormWrapper extends FormWrapper {
    use AppendAdditionsControls;

    /** @var  \App\Model\Persistence\Entity\EventEntity */
    private $event;

    /** @var  CurrencyEntity */
    private $currency;

    /** @var  CurrencyDao */
    private $currencyDao;

    /** @var  \App\Model\Persistence\Dao\ApplicationDao */
    private $applicationDao;

    /** @var  OrderManager */
    private $orderFactory;

    public function __construct(ApplicationDao $applicationFacade, CurrencyDao $currencyFacade, OrderManager $orderFactory) {
        parent::__construct();
        $this->applicationDao = $applicationFacade;
        $this->currencyDao = $currencyFacade;
        $this->currency = $this->currencyDao->getDefaultCurrency();
        $this->orderFactory = $orderFactory;

    }

    public function setEvent(EventEntity $event){
        $this->event = $event;
    }

    public function isAdmin() {
        return true;
    }


    protected function getEvent() {
        return $this->event;
    }

    protected function getCurrency() {
        return $this->currency;
    }

    protected function getApplicationDao() {
        return $this->applicationDao;
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
        $this->orderFactory->createOrderFromOrderForm($values,$this->event);
        $this->getPresenter()->flashMessage('Přihlášky byly vytvořeny','success');
        $this->getPresenter()->redirect('Application:',$this->event->getId());
    }

}