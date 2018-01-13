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
        $this->appendSubmitControls($form, $this->currencyEntity ? 'Form.Action.Edit' : 'Form.Action.Create', [$this, 'submitClicked']);
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
        $form->addText('name', 'Attribute.Name')
            ->setRequired();
        $form->addText('code', 'Attribute.Currency.Code')
            ->setOption($form::OPTION_KEY_DESCRIPTION,"Form.Currency.Description.Code")
            ->setRequired()
            ->addRule($form::PATTERN,  "Form.Currency.Rule.Code.Pattern",'[A-Z]{3}');
        $form->addText('symbol', 'Attribute.Currency.Symbol')
            ->setOption($form::OPTION_KEY_DESCRIPTION,"Form.Currency.Description.Symbol")
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
            $this->getPresenter()->flashTranslatedMessage('Form.Currency.Message.Edit.Success', self::FLASH_MESSAGE_TYPE_SUCCESS);
            $this->getPresenter()->redirect('Currency:default');
        } else {
            $currency = $this->currencyManager->createCurrencyFromCurrencyForm($values);
            $this->getPresenter()->flashTranslatedMessage('Form.Currency.Message.Create.Success', self::FLASH_MESSAGE_TYPE_SUCCESS);
            $this->getPresenter()->redirect('Currency:default');
        }
    }

}