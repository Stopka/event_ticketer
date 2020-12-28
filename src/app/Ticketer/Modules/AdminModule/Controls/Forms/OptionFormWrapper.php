<?php

declare(strict_types=1);

namespace Ticketer\Modules\AdminModule\Controls\Forms;

use Exception;
use Nette\Application\AbortException;
use Ticketer\Controls\FlashMessageTypeEnum;
use Ticketer\Controls\Forms\Form;
use Ticketer\Controls\Forms\FormWrapperDependencies;
use Ticketer\Model\Database\Enums\OptionAutoselectEnum;
use Ticketer\Model\OccupancyIcons;
use Ticketer\Model\Database\Daos\CurrencyDao;
use Ticketer\Model\Database\Entities\AdditionEntity;
use Ticketer\Model\Database\Entities\OptionEntity;
use Ticketer\Model\Database\Managers\OptionManager;
use Nette\Forms\Controls\SubmitButton;
use Nette\Utils\Html;

class OptionFormWrapper extends FormWrapper
{

    private OptionManager $optionManager;

    private ?AdditionEntity $additionEntity = null;

    private ?OptionEntity $optionEntity = null;

    private OccupancyIcons $occupancyIcons;

    private CurrencyDao $currecyDao;

    /**
     * OptionFormWrapper constructor.
     * @param FormWrapperDependencies $formWrapperDependencies
     * @param OptionManager $optionManager
     * @param OccupancyIcons $occupancyIcons
     * @param CurrencyDao $currencyDao
     */
    public function __construct(
        FormWrapperDependencies $formWrapperDependencies,
        OptionManager $optionManager,
        OccupancyIcons $occupancyIcons,
        CurrencyDao $currencyDao
    ) {
        parent::__construct($formWrapperDependencies);
        $this->optionManager = $optionManager;
        $this->occupancyIcons = $occupancyIcons;
        $this->currecyDao = $currencyDao;
    }

    public function setAdditionEntity(?AdditionEntity $additionEntity): void
    {
        $this->additionEntity = $additionEntity;
    }

    public function setOptionEntity(?OptionEntity $optionEntity): void
    {
        $this->optionEntity = $optionEntity;
        if (null !== $optionEntity) {
            $this->setAdditionEntity($optionEntity->getAddition());
        }
    }

    /**
     * @param Form $form
     */
    protected function appendFormControls(Form $form): void
    {
        $this->appendOptionControls($form);
        $this->appendPriceControls($form);
        $this->appendSubmitControls(
            $form,
            null !== $this->optionEntity ? 'Form.Action.Edit' : 'Form.Action.Create',
            [$this, 'submitClicked']
        );
        $this->loadData($form);
    }

    protected function loadData(Form $form): void
    {
        if (null === $this->optionEntity) {
            return;
        }
        $values = $this->optionEntity->getValueArray(null, ['price']);
        $values['limitCapacity'] = null !== $values['capacity'];
        $price = $this->optionEntity->getPrice();
        $values['setPrice'] = null !== $price;
        if (null !== $price) {
            $priceValues = [];
            foreach ($price->getPriceAmounts() as $priceAmount) {
                $currency = $priceAmount->getCurrency();
                if (null === $currency) {
                    continue;
                }
                $priceValues[$currency->getCode()] = $priceAmount->getAmount();
            }
            $values['price'] = $priceValues;
        }
        $form->setDefaults($values);
    }

    /**
     * @param array<mixed> $values
     * @return array<mixed>
     */
    protected function preprocessData(array $values): array
    {
        if (!(bool)$values['limitCapacity']) {
            $values['capacity'] = null;
            $values['occupancyIcon'] = null;
        }
        if (!(bool)$values['occupancyIcon']) {
            $values['occupancyIcon'] = null;
        }
        if (!(bool)$values['setPrice']) {
            $values['price'] = null;
        }
        unset($values['limitCapacity'], $values['setPrice']);

        return $values;
    }

    protected function appendOptionControls(Form $form): void
    {
        $form->addGroup('Entity.Singular.Option')
            ->setOption($form::OPTION_KEY_LOGICAL, true);
        $form->addText('name', 'Attribute.Name')
            ->setRequired();
        $form->addTextArea('description', 'Attribute.Description')
            ->setRequired(false);
        $form->addSelect(
            'autoSelect',
            'Attribute.Addition.AutoSelect',
            [
                OptionAutoselectEnum::NONE => "Value.Addition.AutoSelect.None",
                OptionAutoselectEnum::ALWAYS => "Value.Addition.AutoSelect.Always",
                OptionAutoselectEnum::SECOND_ON => "Value.Addition.AutoSelect.SecondOn",
            ]
        )
            ->setRequired()
            ->setDefaultValue(OptionAutoselectEnum::NONE);
        $limitCapacity = $form->addExtendedCheckbox('limitCapacity', 'Form.Option.Attribute.LimitCapacity')
            ->setOption($form::OPTION_KEY_DESCRIPTION, "Form.Option.Description.LimitCapacity");
        $limitCapacity->addCondition($form::EQUAL, true)
            ->toggle("capacityControlGroup");
        $form->addGroup('Form.Option.Attribute.LimitCapacity')
            ->setOption($form::OPTION_KEY_ID, "capacityControlGroup");
        $form->addText('capacity', 'Attribute.Event.Capacity')
            ->setDefaultValue(10)
            ->setOption($form::OPTION_KEY_DESCRIPTION, 'Form.Option.Description.Capacity')
            ->setOption($form::OPTION_KEY_TYPE, 'number')
            ->setRequired(false)
            ->addRule($form::INTEGER)
            ->addRule($form::RANGE, null, [1, null])
            ->addConditionOn($limitCapacity, $form::EQUAL, true)
            ->addRule($form::FILLED);
        $form->addRadioList('occupancyIcon', 'Attribute.Event.OccupancyIcon', $this->occupancyIcons->getLabeledIcons())
            ->setRequired(false)
            ->addConditionOn($limitCapacity, $form::EQUAL, true)
            ->addRule($form::FILLED);
    }

    public function appendPriceControls(Form $form): void
    {
        $form->addGroup('Form.Option.Group.PriceSetting')
            ->setOption($form::OPTION_KEY_LOGICAL, true)
            ->setOption($form::OPTION_KEY_EMBED_NEXT, 1);
        $setPriceControl = $form->addExtendedCheckbox('setPrice', 'Form.Option.Attribute.SetPrice')
            ->setOption($form::OPTION_KEY_DESCRIPTION, "Form.Option.Description.SetPrice");
        $setPriceControl->addCondition($form::EQUAL, true)
            ->toggle("priceControlGroup");
        $subgroup = $form->addGroup(
            (string)Html::el()
                ->addHtml(
                    Html::el('i', ['class' => 'fa fa-money'])
                )
                ->addText(
                    ' ' . $this->getTranslator()->translate('Entity.Singular.Price')
                )
        )
            ->setOption($form::OPTION_KEY_ID, "priceControlGroup");

        $container = $form->addContainer('price');
        $container->setCurrentGroup($subgroup);
        foreach ($this->currecyDao->getAllCurrecies() as $currency) {
            $container->addText($currency->getCode(), $currency->getCode())
                ->setDefaultValue(0)
                ->setOption($form::OPTION_KEY_TYPE, 'number')
                ->setOption(
                    $form::OPTION_KEY_DESCRIPTION,
                    $this->getTranslator()->translate(
                        "Form.Option.Description.Amount",
                        ['currency' => $currency->getName()]
                    )
                )
                //->setOption($form::OPTION_KEY_ID, "priceControlGroup_$number")
                ->setRequired(false)
                ->addRule($form::FLOAT, null)
                ->addConditionOn($setPriceControl, $form::EQUAL, true)
                ->addRule($form::FILLED);
        }
    }

    /**
     * @param SubmitButton $button
     * @throws Exception
     * @throws AbortException
     */
    protected function submitClicked(SubmitButton $button): void
    {
        $form = $button->getForm();
        if (null === $form || null === $this->additionEntity) {
            return;
        }
        /** @var array<mixed> $values */
        $values = $form->getValues('array');
        $values = $this->preprocessData($values);
        if (null !== $this->optionEntity) {
            $this->optionManager->editOptionFromOptionForm($values, $this->optionEntity);
            $this->getPresenter()->flashTranslatedMessage(
                'Form.Option.Message.Edit.Success',
                FlashMessageTypeEnum::SUCCESS()
            );
            $this->getPresenter()->redirect('Option:default', [$this->additionEntity->getId()]);
        } else {
            $this->optionManager->createOptionFromEventForm($values, $this->additionEntity);
            $this->getPresenter()->flashTranslatedMessage(
                'Form.Option.Message.Create.Success',
                FlashMessageTypeEnum::SUCCESS()
            );
            $this->getPresenter()->redirect('Option:default', [$this->additionEntity->getId()]);
        }
    }
}
