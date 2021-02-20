<?php

declare(strict_types=1);

namespace Ticketer\Modules\AdminModule\Controls\Forms;

use Nette\Application\AbortException;
use Ticketer\Controls\FlashMessageTypeEnum;
use Ticketer\Controls\Forms\Form;
use Ticketer\Controls\Forms\FormWrapperDependencies;
use Ticketer\Model\Database\Entities\CurrencyEntity;
use Ticketer\Model\Database\Managers\CurrencyManager;
use Nette\Forms\Controls\SubmitButton;
use Ticketer\Modules\AdminModule\Controls\Forms\Inputs\CurrencyCodeInput;
use Ticketer\Modules\AdminModule\Controls\Forms\Inputs\CurrencySymbolInput;
use Ticketer\Modules\AdminModule\Controls\Forms\Inputs\NameInput;
use Ticketer\Modules\AdminModule\Controls\Forms\Values\CurrencyFormValue;

class CurrencyFormWrapper extends FormWrapper
{

    private CurrencyManager $currencyManager;

    private ?CurrencyEntity $currencyEntity = null;

    /**
     * EventFormWrapper constructor.
     * @param FormWrapperDependencies $formWrapperDependencies
     * @param CurrencyManager $currencyManager
     */
    public function __construct(FormWrapperDependencies $formWrapperDependencies, CurrencyManager $currencyManager)
    {
        parent::__construct($formWrapperDependencies);
        $this->currencyManager = $currencyManager;
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
        $values = $this->currencyEntity->getValueArray();
        $form->setDefaults($values);
    }

    protected function appendCurrencyControls(Form $form): void
    {
        $form->addComponent(
            (new NameInput())
                ->setRequired(),
            'name'
        );
        $form->addComponent(
            (new CurrencyCodeInput())
                ->setRequired(),
            'code'
        );
        $form->addComponent(
            (new CurrencySymbolInput())
                ->setRequired(),
            'symbol'
        );
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
            $this->currencyManager->editCurrencyFromCurrencyForm($values, $this->currencyEntity);
            $this->getPresenter()->flashTranslatedMessage(
                'Form.Currency.Message.Edit.Success',
                FlashMessageTypeEnum::SUCCESS()
            );
            $this->getPresenter()->redirect('Currency:default');
        } else {
            $this->currencyManager->createCurrencyFromCurrencyForm($values);
            $this->getPresenter()->flashTranslatedMessage(
                'Form.Currency.Message.Create.Success',
                FlashMessageTypeEnum::SUCCESS()
            );
            $this->getPresenter()->redirect('Currency:default');
        }
    }
}
