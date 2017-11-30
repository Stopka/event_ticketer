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
use App\Model\Persistence\Entity\AdditionEntity;
use App\Model\Persistence\Entity\ApplicationEntity;
use App\Model\Persistence\Entity\EventEntity;
use App\Model\Persistence\Manager\AdditionManager;
use Nette\Forms\Container;
use Nette\Forms\Controls\SubmitButton;
use Nette\Utils\Html;

class AdditionFormWrapper extends FormWrapper {

    /** @var  AdditionManager */
    private $additionManager;

    /** @var  EventEntity */
    private $eventEntity;

    /** @var  AdditionEntity */
    private $additionEntity;

    /** @var int */
    private $counter = 0;

    /** @var  OccupancyIcons */
    private $occupancyIcons;

    /**
     * EventFormWrapper constructor.
     * @param AdditionManager $additionManager
     * @param $occupancyIcons OccupancyIcons
     */
    public function __construct(AdditionManager $additionManager, OccupancyIcons $occupancyIcons) {
        parent::__construct();
        $this->additionManager = $additionManager;
        $this->occupancyIcons = $occupancyIcons;
    }

    public function setEventEntity(?EventEntity $eventEntity): void {
        $this->eventEntity = $eventEntity;
    }

    public function setAdditionEntity(?AdditionEntity $additionEntity): void {
        $this->additionEntity = $additionEntity;
        if ($additionEntity) {
            $this->setEventEntity($additionEntity->getEvent());
        }
    }

    /**
     * @param Form $form
     */
    protected function appendFormControls(Form $form) {
        $this->appendAdditionControls($form);
        $this->appendOptionsControls($form);
        $this->appendSubmitControls($form, $this->additionEntity ? 'Upravit' : 'Vytvořit', [$this, 'submitClicked']);
        $this->loadData($form);
    }

    protected function loadData(Form $form) {
        if (!$this->additionEntity) {
            return;
        }
        $values = $this->additionEntity->getValueArray();
        $form->setDefaults($values);
    }

    protected function preprocessData(array $values): array {
        if(!$values['requiredForState']){
            $values['requiredForState']=null;
        }
        if(!$values['enoughForState']){
            $values['enoughForState']=null;
        }
        foreach ($values['options'] as $key=>$value){
            if(!$value['limitCapacity']){
                $values['options'][$key]['capacity']=null;
            }
            if(!$value['occupancyIcon']){
                $values['options'][$key]['occupancyIcon']=null;
            }
            if(!$value['setPrice']){
                $values['options'][$key]['price']=null;
            }
            unset($values['options'][$key]['limitCapacity'], $values['options'][$key]['setPrice']);
        }
        return $values;
    }

    protected function appendAdditionControls(Form $form) {
        $form->addGroup("Přídavek")
            ->setOption($form::OPTION_KEY_LOGICAL, true);
        $form->addText('name', 'Název')
            ->setRequired();
        $form->addSelect('requiredForState', 'Vyžadováno pro', [
            null => 'Nic',
            ApplicationEntity::STATE_RESERVED => 'Závaznou rezervaci',
            ApplicationEntity::STATE_FULFILLED => 'Celkové dokončení'
        ])
            ->setOption($form::OPTION_KEY_DESCRIPTION, "Splnění tohoto přídavku je povinné pro přechod přihlášky do zvoleného stavu")
            ->setDefaultValue(null)
            ->setRequired(false);
        $form->addSelect('enoughForState', 'Dostatečné pro', [
            null => 'Nic',
            ApplicationEntity::STATE_RESERVED => 'Závaznou rezervaci',
            ApplicationEntity::STATE_FULFILLED => 'Celkové dokončení'
        ])
            ->setOption($form::OPTION_KEY_DESCRIPTION, "Splnění tohoto přídavku je dostatečné pro přechod přihlášky do zvoleného stavu")
            ->setDefaultValue(null)
            ->setRequired(false);
        $form->addCheckbox('visible', 'Viditelné v registraci')
            ->setDefaultValue(true)
            ->setOption($form::OPTION_KEY_DESCRIPTION, "Má být přídavek vidět ve veřejném registračním formuláři?");
        $form->addCheckbox('hidden', 'Schovat v náhledu')
            ->setDefaultValue(false)
            ->setOption($form::OPTION_KEY_DESCRIPTION, "Má být přídavek schován ve uživatelském náhledu objednávky?");
        $form->addText('minimum', 'Minimum')
            ->setOption($form::OPTION_KEY_DESCRIPTION, "Kolik následujícíh možností musí uživatel při registraci minimálně zvolit")
            ->setOption($form::MIME_TYPE, "number")
            ->setDefaultValue(0)
            ->setRequired()
            ->addRule($form::INTEGER)
            ->addRule($form::RANGE, null, [0, null]);
        $form->addText('maximum', 'Maximum')
            ->setOption($form::OPTION_KEY_DESCRIPTION, "Kolik následujícíh možností může uživatel při registraci maximálně zvolit")
            ->setOption($form::MIME_TYPE, "number")
            ->setDefaultValue(1)
            ->setRequired()
            ->addRule($form::INTEGER)
            ->addRule($form::RANGE, null, [1, null]);
    }

    protected function appendOptionsControls(Form $form) {
        $optionsGroup = $form->addGroup(Html::el()->addHtml(Html::el('i',['class'=>'fa fa-list-ul']))->addText(' Možnosti'));
        $removeEvent = [$this, 'removeOption'];
        $add_button = $form->addSubmit('add', 'Přidat další možnost')
            ->setValidationScope(FALSE);
        $add_button->getControlPrototype()->class = 'ajax';
        $add_button->onClick[] = [$this, 'addOption'];
        $options = $form->addDynamic('options', function (Container $option) use ($removeEvent, $form, $optionsGroup) {
            $group = $form->addGroup();
            $parent_group = $optionsGroup;//$form->getGroup('Možnosti');
            $count = $parent_group->getOption($form::OPTION_KEY_EMBED_NEXT);
            $parent_group->setOption($form::OPTION_KEY_EMBED_NEXT, $count ? $count + 1 : 1);
            $option->setCurrentGroup($group);

            $this->appendOptionControls($form, $option);

            $subgroup = $form->addGroup()
                ->setOption($form::OPTION_KEY_LOGICAL,true);
            $count = $group->getOption($form::OPTION_KEY_EMBED_NEXT);
            $group->setOption($form::OPTION_KEY_EMBED_NEXT,$count+1);
            $option->setCurrentGroup($subgroup);
            $remove_button = $option->addSubmit('remove', 'Zrušit tuto možnost')
                ->setValidationScope(FALSE); # disables validation
            $remove_button->onClick[] = $removeEvent;
            $remove_button->getControlPrototype()->class = 'ajax';

        }, $this->getOptionCount(), $this->isOptionCountFixed());
    }

    private function getOptionCount() {
        return 0;
        if ($this->order) {
            return 0;
        }
        if ($this->substitute) {
            return $this->substitute->getCount();
        }
        return 1;
    }

    private function isOptionCountFixed() {
        return false;
        if ($this->order) {
            return false;
        }
        if ($this->substitute) {
            return false;
        }
        return true;
    }

    private function getCounterNumber(): int {
        return $this->counter++;
    }

    protected function appendOptionControls(Form $form, Container $container) {
        $number = $this->getCounterNumber();
        if($this->additionEntity) {
            $container->addHidden('id');
        }
        $container->addText('name', 'Název')
            ->setRequired();
        $container->addCheckbox('limitCapacity', 'Omezit kapacitu')
            ->setOption($form::OPTION_KEY_DESCRIPTION, "Možnost bude dostupná veřejné registraci do vyčerpání kapacity")
            ->addCondition($form::EQUAL, true)
            ->toggle("capacityControlGroup_$number");
        $container->addText('capacity', 'Kapacita')
            ->setDefaultValue(10)
            ->setOption($form::OPTION_KEY_DESCRIPTION, 'Kolik příhlášek může mít zvolenou tuto možnost')
            ->setOption($form::OPTION_KEY_TYPE, 'number')
            ->setOption($form::OPTION_KEY_ID, "capacityControlGroup_$number")
            ->setRequired(false)
            ->addRule($form::INTEGER)
            ->addRule($form::RANGE, null, [1, null])
            ->addConditionOn($container['limitCapacity'], $form::EQUAL, true)
            ->addRule($form::FILLED);
        $container->addRadioList('occupancyIcon', 'Ikona obsazenosti', $this->occupancyIcons->getLabeledIcons('Žádná'))
            ->setRequired(false)
            ->setDefaultValue(null);
        $container->addCheckbox('setPrice', 'Nastavit cenu')
            ->setOption($form::OPTION_KEY_DESCRIPTION, "Pokud je tato cena za příplatek, zvolte tuto možnost")
            ->addCondition($form::EQUAL, true)
            ->toggle("priceControlGroup_$number");
        $price = $container->addContainer('price');
        $this->appendPriceControls($form,$container,$price, $number);
    }

    public function appendPriceControls(Form $form, Container $parent, Container $container, int $number) {
        $group = $container->getCurrentGroup();
        $embed = $group->getOption($form::OPTION_KEY_EMBED_NEXT);
        $group->setOption($form::OPTION_KEY_EMBED_NEXT,$embed+1);
        $subgroup = $form->addGroup(Html::el()->addHtml(Html::el('i',['class'=>'fa fa-money']))->addText(' Cena'))
            //->setOption($form::OPTION_KEY_LOGICAL,true)
            ->setOption($form::OPTION_KEY_ID,"priceControlGroup_$number");
        $container->setCurrentGroup($subgroup);
        $amount = $container->addText('priceAmount', 'Částka')
            ->setDefaultValue(0)
            ->setOption($form::OPTION_KEY_TYPE, 'number')
            //->setOption($form::OPTION_KEY_DESCRIPTION, "Měna: CZK")
            //->setOption($form::OPTION_KEY_ID, "priceControlGroup_$number")
            ->setRequired(false)
            ->addRule($form::FLOAT,null)
            ->addRule($form::RANGE, null, [0, null])
            ->addConditionOn($parent['setPrice'], $form::EQUAL, true)
            ->addRule($form::FILLED);
        $container->addSelect('priceCurrency', 'Měna', [1=>"CZK"])
            ->setDefaultValue(1)
            ->setRequired(false)
            ->addConditionOn($parent['setPrice'], $form::EQUAL, true)
            ->addRule($form::FILLED);
    }


    public function addOption(SubmitButton $button) {
        $form = $button->getForm();
        $form['options']->createOne();
        $this->redrawControl('form');
    }

    public function removeOption(SubmitButton $button) {
        $child = $button->getParent();
        $children = $child->getParent();
        $children->remove($child, TRUE);
        $this->redrawControl('form');
    }

    /**
     * @param SubmitButton $button
     */
    protected function submitClicked(SubmitButton $button) {
        $form = $button->getForm();
        $values = $form->getValues(true);
        $values = $this->preprocessData($values);
        if ($this->additionEntity) {
            $this->additionManager->editAdditionFromEventForm($values, $this->additionEntity);
            $this->getPresenter()->flashMessage('Přídavek byl upraven', 'success');
            $this->getPresenter()->redirect('Addition:default', [$this->event->getId()]);
        } else {
            $event = $this->additionManager->createAdditionFromEventForm($values);
            $this->getPresenter()->flashMessage('Přídavek byl vytvořen', 'success');
            $this->getPresenter()->redirect('Addition:default', [$event->getId()]);
        }
    }

}