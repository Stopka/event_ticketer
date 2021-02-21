<?php

declare(strict_types=1);

namespace Ticketer\Modules\AdminModule\Controls\Forms;

use Nette\Application\AbortException;
use Ticketer\Controls\FlashMessageTypeEnum;
use Ticketer\Controls\Forms\Form;
use Ticketer\Controls\Forms\FormWrapperDependencies;
use Ticketer\Model\Database\Entities\CurrencyEntity;
use Ticketer\Model\Database\Handlers\CreateCurrencyByFormHandler;
use Ticketer\Model\Database\Handlers\EditCurrencyByFormHandler;
use Nette\Forms\Controls\SubmitButton;
use Ticketer\Modules\AdminModule\Controls\Forms\Inputs\CurrencyCodeInput;
use Ticketer\Modules\AdminModule\Controls\Forms\Inputs\CurrencySymbolInput;
use Ticketer\Modules\AdminModule\Controls\Forms\Inputs\NameInput;
use Ticketer\Modules\AdminModule\Controls\Forms\Values\CurrencyFormValue;

use function _HumbugBoxfac515c46e83\RingCentral\Psr7\str;

class CurrencyFormWrapper extends FormWrapper
{

    private ?CurrencyEntity $currencyEntity = null;

    private CreateCurrencyByFormHandler $createHandler;

    private EditCurrencyByFormHandler $editHandler;

    /**
     * EventFormWrapper constructor.
     * @param FormWrapperDependencies $formWrapperDependencies
     * @param CreateCurrencyByFormHandler $createHandler
     * @param EditCurrencyByFormHandler $editHandler
     */
    public function __construct(
        FormWrapperDependencies $formWrapperDependencies,
        CreateCurrencyByFormHandler $createHandler,
        EditCurrencyByFormHandler $editHandler
    ) {
        parent::__construct($formWrapperDependencies);
        $this->editHandler = $editHandler;
        $this->createHandler = $createHandler;
    }

    public function setCurrencyEntity(?CurrencyEntity $currencyEntity): void
    {
        $this->currencyEntity = $currencyEntity;
    }

    /**
     * @param Form $form
     */
    protected function appendFormControls(Form $form): void
    {
        $this->appendCurrencyControls($form);
        $this->appendSubmitControls(
            $form,
            null !== $this->currencyEntity ? 'Form.Action.Edit' : 'Form.Action.Create',
            [$this, 'submitClicked']
        );
        $this->loadData($form);
    }

    protected function loadData(Form $form): void
    {
        if (null === $this->currencyEntity) {
            return;
        }
        $values = new CurrencyFormValue();
        $values->name = (string)$this->currencyEntity->getName();
        $values->code = $this->currencyEntity->getCode();
        $values->symbol = $this->currencyEntity->getSymbol();
        $form->setDefaults($values);
    }

    protected function appendCurrencyControls(Form $form): void
    {
        NameInput::appendToForm($form, 'name')
            ->setRequired();
        CurrencyCodeInput::appendToForm($form, 'code')
            ->setRequired();
        CurrencySymbolInput::appendToForm($form, 'symbol')
            ->setRequired();
    }

    /**
     * @param SubmitButton $button
     * @throws AbortException
     */
    protected function submitClicked(SubmitButton $button): void
    {
        $form = $button->getForm();
        if (null === $form) {
            return;
        }
        /** @var CurrencyFormValue $values */
        $values = $form->getValues(CurrencyFormValue::class);
        if (null !== $this->currencyEntity) {
            $this->editHandler->handle($values, $this->currencyEntity);
            $this->getPresenter()->flashTranslatedMessage(
                'Form.Currency.Message.Edit.Success',
                FlashMessageTypeEnum::SUCCESS()
            );
            $this->getPresenter()->redirect('Currency:default');
        } else {
            $this->createHandler->handle($values);
            $this->getPresenter()->flashTranslatedMessage(
                'Form.Currency.Message.Create.Success',
                FlashMessageTypeEnum::SUCCESS()
            );
            $this->getPresenter()->redirect('Currency:default');
        }
    }
}
