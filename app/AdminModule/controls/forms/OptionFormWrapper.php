<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 15.1.17
 * Time: 15:06
 */

namespace App\AdminModule\Controls\Forms;


use App\Controls\Forms\Form;
use App\Model\OccupancyIcons;
use App\Model\Persistence\Dao\CurrencyDao;
use App\Model\Persistence\Entity\AdditionEntity;
use App\Model\Persistence\Entity\OptionEntity;
use App\Model\Persistence\Manager\OptionManager;
use Nette\Forms\Controls\SubmitButton;
use Nette\Utils\Html;

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
     * @param OptionManager $optionManager
     * @param OccupancyIcons $occupancyIcons
     * @param CurrencyDao $currencyDao
     */
    public function __construct(OptionManager $optionManager, OccupancyIcons $occupancyIcons, CurrencyDao $currencyDao) {
        parent::__construct();
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
        $this->appendSubmitControls($form, $this->optionEntity ? 'Upravit' : 'Vytvořit', [$this, 'submitClicked']);
        $this->loadData($form);
    }

    protected function loadData(Form $form) {
        if (!$this->optionEntity) {
            return;
        }
        $values = $this->optionEntity->getValueArray();
        $values['limitCapacity'] = $values['capacity'] !== null;
        $price = $this->optionEntity->getPrice();
        $values['setPrice'] = $price !== null;
        $priceValues = [];
        foreach ($price->getPriceAmounts() as $priceAmount) {
            $priceValues[$priceAmount->getCurrency()->getCode()] = $priceAmount->getAmount();
        }
        $values['price'] = $priceValues;
        $form->setDefaults($values);

    }

    protected function preprocessData(array $values): array {
        if (!$values['limitCapacity']) {
            $values['capacity'] = null;
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
        $form->addGroup('Možnost')
            ->setOption($form::OPTION_KEY_LOGICAL, true);
        $form->addText('name', 'Název')
            ->setRequired();
        $form->addCheckbox('limitCapacity', 'Omezit kapacitu')
            ->setOption($form::OPTION_KEY_DESCRIPTION, "Možnost bude dostupná veřejné registraci do vyčerpání kapacity")
            ->addCondition($form::EQUAL, true)
            ->toggle("capacityControlGroup");
        $form->addText('capacity', 'Kapacita')
            ->setDefaultValue(10)
            ->setOption($form::OPTION_KEY_DESCRIPTION, 'Kolik příhlášek může mít zvolenou tuto možnost')
            ->setOption($form::OPTION_KEY_TYPE, 'number')
            ->setOption($form::OPTION_KEY_ID, "capacityControlGroup")
            ->setRequired(false)
            ->addRule($form::INTEGER)
            ->addRule($form::RANGE, null, [1, null])
            ->addConditionOn($form['limitCapacity'], $form::EQUAL, true)
            ->addRule($form::FILLED);
        $form->addRadioList('occupancyIcon', 'Ikona obsazenosti', $this->occupancyIcons->getLabeledIcons('Žádná'))
            ->setRequired(false)
            ->setDefaultValue(null);
    }

    public function appendPriceControls(Form $form) {
        $group = $form->addGroup('Nastavení ceny')
            ->setOption($form::OPTION_KEY_LOGICAL, true)
            ->setOption($form::OPTION_KEY_EMBED_NEXT,1);
        $form->addCheckbox('setPrice', 'Nastavit cenu')
            ->setOption($form::OPTION_KEY_DESCRIPTION, "Pokud je tato cena za příplatek, zvolte tuto možnost")
            ->addCondition($form::EQUAL, true)
            ->toggle("priceControlGroup");
        $subgroup = $form->addGroup(Html::el()->addHtml(Html::el('i', ['class' => 'fa fa-money']))->addText(' Cena'))
            //->setOption($form::OPTION_KEY_LOGICAL,true)
            ->setOption($form::OPTION_KEY_ID, "priceControlGroup");

        $container = $form->addContainer('price');
        $container->setCurrentGroup($subgroup);
        foreach ($this->currecyDao->getAllCurrecies() as $currecy) {
            $container->addText($currecy->getCode(), $currecy->getCode())
                ->setDefaultValue(0)
                ->setOption($form::OPTION_KEY_TYPE, 'number')
                ->setOption($form::OPTION_KEY_DESCRIPTION, "Částka v měně " . $currecy->getName())
                //->setOption($form::OPTION_KEY_ID, "priceControlGroup_$number")
                ->setRequired(false)
                ->addRule($form::FLOAT, null)
                ->addRule($form::RANGE, null, [0, null])
                ->addConditionOn($form['setPrice'], $form::EQUAL, true)
                ->addRule($form::FILLED);
        }
    }

    /**
     * @param SubmitButton $button
     */
    protected function submitClicked(SubmitButton $button) {
        $form = $button->getForm();
        $values = $form->getValues(true);
        $values = $this->preprocessData($values);
        if ($this->optionEntity) {
            $this->optionManager->editOptionFromOptionForm($values, $this->optionEntity);
            $this->getPresenter()->flashMessage('Možnost byla upravena', 'success');
            $this->getPresenter()->redirect('Option:default', [$this->additionEntity->getId()]);
        } else {
            $addition = $this->optionManager->createOptionFromEventForm($values, $this->additionEntity);
            $this->getPresenter()->flashMessage('Možnost byla vytvořena', 'success');
            $this->getPresenter()->redirect('Option:default', [$this->additionEntity->getId()]);
        }
    }

}