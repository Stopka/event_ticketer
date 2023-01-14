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
use Ticketer\Modules\AdminModule\Controls\Forms\Inputs\PrimarySubmitButton;
use Ticketer\Modules\AdminModule\Controls\Forms\Values\CurrencyFormValue;

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

    protected function appendFormControls(Form $form): void
    {
        NameInput::appendToForm($form, 'name')
            ->setRequired();
        CurrencyCodeInput::appendToForm($form, 'code')
            ->setRequired();
        CurrencySymbolInput::appendToForm($form, 'symbol')
            ->setRequired();
        PrimarySubmitButton::appendToForm(
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

    /**
     * @param SubmitButton $button
     * @throws AbortException
     */
    public function submitClicked(SubmitButton $button): void
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
