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
use App\Model\Persistence\Dao\CurrencyDao;
use App\Model\Persistence\Entity\AdditionEntity;
use App\Model\Persistence\Entity\OptionEntity;
use App\Model\Persistence\Manager\OptionManager;
use Nette\Forms\Controls\SubmitButton;
use Nette\Utils\Html;
use Tracy\Debugger;

class OptionFormWrapper extends FormWrapper {

    /** @var  OptionManager */
    private $optionManager;

    /** @var  AdditionEntity */
    private $additionEntity;

    /** @var  OptionEntity */
    private $optionEntity;

    /** @var  OccupancyIcons */
    private $occupancyIcons;

    /** @var  CurrencyDao */
    private $currecyDao;

    /**
     * OptionFormWrapper constructor.
     * @param FormWrapperDependencies $formWrapperDependencies
     * @param OptionManager $optionManager
     * @param OccupancyIcons $occupancyIcons
     * @param CurrencyDao $currencyDao
     */
    public function __construct(FormWrapperDependencies $formWrapperDependencies, OptionManager $optionManager, OccupancyIcons $occupancyIcons, CurrencyDao $currencyDao) {
        parent::__construct($formWrapperDependencies);
        $this->optionManager = $optionManager;
        $this->occupancyIcons = $occupancyIcons;
        $this->currecyDao = $currencyDao;
    }

    public function setAdditionEntity(?AdditionEntity $additionEntity): void {
        $this->additionEntity = $additionEntity;
    }

    public function setOptionEntity(?OptionEntity $optionEntity): void {
        $this->optionEntity = $optionEntity;
        if ($optionEntity) {
            $this->setAdditionEntity($optionEntity->getAddition());
        }
    }

    /**
     * @param Form $form
     */
    protected function appendFormControls(Form $form) {
        $this->appendOptionControls($form);
        $this->appendPriceControls($form);
        $this->appendSubmitControls($form, $this->optionEntity ? 'Form.Action.Edit' : 'Form.Action.Create', [$this, 'submitClicked']);
        $this->loadData($form);
    }

    protected function loadData(Form $form) {
        if (!$this->optionEntity) {
            return;
        }
        $values = $this->optionEntity->getValueArray(null, ['price']);
        $values['limitCapacity'] = $values['capacity'] !== null;
        $price = $this->optionEntity->getPrice();
        $values['setPrice'] = $price !== null;
        if ($price) {
            $priceValues = [];
            foreach ($price->getPriceAmounts() as $priceAmount) {
                $priceValues[$priceAmount->getCurrency()->getCode()] = $priceAmount->getAmount();
            }
            $values['price'] = $priceValues;
        }
        Debugger::barDump($values);
        $form->setDefaults($values);

    }

    protected function preprocessData(array $values): array {
        if (!$values['limitCapacity']) {
            $values['capacity'] = null;
            $values['occupancyIcon'] = null;
        }
        if (!$values['occupancyIcon']) {
            $values['occupancyIcon'] = null;
        }
        if (!$values['setPrice']) {
            $values['price'] = null;
        }
        unset($values['limitCapacity'], $values['setPrice']);
        return $values;
    }

    protected function appendOptionControls(Form $form) {
        $form->addGroup('Entity.Singular.Option')
            ->setOption($form::OPTION_KEY_LOGICAL, true);
        $form->addText('name', 'Attribute.Name')
            ->setRequired();
        $form->addTextArea('description', 'Attribute.Description')
            ->setRequired(false);
        $form->addSelect('autoSelect', 'Attribute.Addition.AutoSelect',[
            OptionEntity::AUTOSELECT_NONE => "Value.Addition.AutoSelect.None",
            OptionEntity::AUTOSELECT_ALWAYS => "Value.Addition.AutoSelect.Always",
            OptionEntity::AUTOSELECT_SECONDON => "Value.Addition.AutoSelect.SecondOn",
        ])
            ->setRequired()
            ->setDefaultValue(OptionEntity::AUTOSELECT_NONE);
        $limitCapacity = $form->addCheckbox('limitCapacity', 'Form.Option.Attribute.LimitCapacity')
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

    public function appendPriceControls(Form $form) {
        $form->addGroup('Form.Option.Group.PriceSetting')
            ->setOption($form::OPTION_KEY_LOGICAL, true)
            ->setOption($form::OPTION_KEY_EMBED_NEXT, 1);
        $setPriceControl = $form->addCheckbox('setPrice', 'Form.Option.Attribute.SetPrice')
            ->setOption($form::OPTION_KEY_DESCRIPTION, "Form.Option.Description.SetPrice");
        $setPriceControl->addCondition($form::EQUAL, true)
            ->toggle("priceControlGroup");
        $subgroup = $form->addGroup(Html::el()->addHtml(Html::el('i', ['class' => 'fa fa-money']))->addText(' '.$this->getTranslator()->translate('Entity.Singular.Price')))
            //->setOption($form::OPTION_KEY_LOGICAL,true)
            ->setOption($form::OPTION_KEY_ID, "priceControlGroup");

        $container = $form->addContainer('price');
        $container->setCurrentGroup($subgroup);
        foreach ($this->currecyDao->getAllCurrecies() as $currecy) {
            $container->addText($currecy->getCode(), $currecy->getCode())
                ->setDefaultValue(0)
                ->setOption($form::OPTION_KEY_TYPE, 'number')
                ->setOption(
                    $form::OPTION_KEY_DESCRIPTION,
                    $this->getTranslator()->translate("Form.Option.Description.Amount", ['currency' => $currecy->getName()])
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
     * @throws \Exception
     * @throws \Nette\Application\AbortException
     */
    protected function submitClicked(SubmitButton $button) {
        $form = $button->getForm();
        $values = $form->getValues(true);
        $values = $this->preprocessData($values);
        if ($this->optionEntity) {
            $this->optionManager->editOptionFromOptionForm($values, $this->optionEntity);
            $this->getPresenter()->flashTranslatedMessage('Form.Option.Message.Edit.Success', self::FLASH_MESSAGE_TYPE_SUCCESS);
            $this->getPresenter()->redirect('Option:default', [$this->additionEntity->getId()]);
        } else {
            $this->optionManager->createOptionFromEventForm($values, $this->additionEntity);
            $this->getPresenter()->flashTranslatedMessage('Form.Option.Message.Create.Success', self::FLASH_MESSAGE_TYPE_SUCCESS);
            $this->getPresenter()->redirect('Option:default', [$this->additionEntity->getId()]);
        }
    }

}