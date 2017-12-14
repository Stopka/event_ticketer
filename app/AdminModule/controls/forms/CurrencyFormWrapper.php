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
use App\Model\Persistence\Entity\CurrencyEntity;
use App\Model\Persistence\Manager\CurrencyManager;
use App\Model\Persistence\Manager\EventManager;
use Nette\Forms\Controls\SubmitButton;

class CurrencyFormWrapper extends FormWrapper {

    /** @var  CurrencyManager */
    private $currencyManager;

    /** @var  CurrencyEntity */
    private $currencyEntity;

    /**
     * EventFormWrapper constructor.
     * @param EventManager $additionManager
     * @param $occupancyIcons OccupancyIcons
     */
    public function __construct(FormWrapperDependencies $formWrapperDependencies, CurrencyManager $currencyManager) {
        parent::__construct($formWrapperDependencies);
        $this->currencyManager = $currencyManager;
    }

    public function setCurrencyEntity(?CurrencyEntity $currencyEntity): void {
        $this->currencyEntity = $currencyEntity;
    }

    /**
     * @param Form $form
     */
    protected function appendFormControls(Form $form) {
        $this->appendCurrencyControls($form);
        $this->appendSubmitControls($form, $this->currencyEntity ? 'Upravit' : 'Vytvořit', [$this, 'submitClicked']);
        $this->loadData($form);
    }

    protected function loadData(Form $form) {
        if (!$this->currencyEntity) {
            return;
        }
        $values = $this->currencyEntity->getValueArray();
        $form->setDefaults($values);
    }

    protected function preprocessData(array $values): array {
        return $values;
    }

    protected function appendCurrencyControls(Form $form) {
        //$form->addGroup("Událost");
        $form->addText('name', 'Název')
            ->setRequired();
        $form->addText('code', 'Kód')
            ->setOption($form::OPTION_KEY_DESCRIPTION,"Třípísmenný kód měny podle standardu ISO 4217")
            ->setRequired()
            ->addRule($form::PATTERN,  "Kód musí sestávat ze 3 velkých písmen",'[A-Z]{3}');
        $form->addText('symbol', 'Symbol')
            ->setOption($form::OPTION_KEY_DESCRIPTION,"Běžně užívaný symbol měny")
            ->setRequired();
    }

    /**
     * @param SubmitButton $button
     */
    protected function submitClicked(SubmitButton $button) {
        $form = $button->getForm();
        $values = $form->getValues(true);
        $values = $this->preprocessData($values);
        if ($this->currencyEntity) {
            $this->currencyManager->editCurrencyFromCurrencyForm($values, $this->currencyEntity);
            $this->getPresenter()->flashMessage('Měna byla upravena', 'success');
            $this->getPresenter()->redirect('Currency:default');
        } else {
            $currency = $this->currencyManager->createCurrencyFromCurrencyForm($values);
            $this->getPresenter()->flashMessage('Měna byla vytvořena', 'success');
            $this->getPresenter()->redirect('Currency:default');
        }
    }

}