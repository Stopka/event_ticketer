<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 24.1.17
 * Time: 22:14
 */

namespace App\Controls\Forms;

use App\Model\Persistence\Dao\ApplicationDao;
use App\Model\Persistence\Entity\AdditionEntity;
use App\Model\Persistence\Entity\CurrencyEntity;
use App\Model\Persistence\Entity\OptionEntity;
use Nette\Forms\Container;
use Nette\Utils\Html;

trait AppendAdditionsControls {
    use RecalculateControl;

    /**
     * @return \App\Model\Persistence\Entity\EventEntity
     */
    abstract protected function getEvent();

    /**
     * @return CurrencyEntity
     */
    abstract protected function getCurrency();

    /**
     * @return ApplicationDao
     */
    abstract protected function getApplicationDao();

    protected function appendAdditionsControls(Form $form, Container $container) {
        $subcontainer = $container->addContainer('addittions');
        foreach ($this->getEvent()->getAdditions() as $addition) {
            if (!$addition->isVisible()) {
                continue;
            }
            $this->appendAdditionContols($subcontainer, $addition);
        }
        $subcontainer['total'] = new \Stopka\NetteFormRenderer\Forms\Controls\Html('Celkem za přihlášku',
            Html::el('div', ['class' => 'price_subtotal'])
                ->addHtml(Html::el('span', ['class' => 'price_amount'])->setText('…'))
                ->addHtml(Html::el('span', ['class' => 'price_currency']))->addHtml($this->createRecalculateHtml())
        );
    }

    protected function appendAdditionContols(Container $container, AdditionEntity $addition) {
        $prices = $this->createAdditionPrices($addition);
        $options = $this->createAdditionOptions($addition, $prices);
        if (!count($options)) {
            return;
        }
        if ($addition->getMinimum() !== 1 || $addition->getMaximum() > 1 || $addition->getMaximum() == count($options)) {
            $control = $container->addCheckboxList($addition->getIdAlphaNumeric(), $addition->getName(), $options)
                ->setRequired($addition->getMinimum() > 0)
                ->setTranslator()
                ->setDefaultValue($keys);
        } else {
            $control = $container->addRadioList($addition->getIdAlphaNumeric(), $addition->getName(), $options)
                ->setRequired()
                ->setTranslator();
            if($keys) {
                $control->setDefaultValue($keys[0]);
            }
        }
        if($keys){
            $control->getContainerPrototype()
                ->setAttribute('data-price-precheck',json_encode($keys))
                ->setAttribute('data-price-predisabled',json_encode($keys));
        }
        if ($prices) {
            $control->getControlPrototype()
                ->addClass('price_item')
                ->setAttribute('data-price-value', json_encode($prices));
        }
    }

    /**
     * @param AdditionEntity $addition
     * @return array
     */
    protected function createAdditionPrices(AdditionEntity $addition) {
        $result = [];
        foreach ($addition->getOptions() as $option) {
            $price = $option->getPrice();
            if (!$price) {
                continue;
            }
            $amount = $option->getPrice()->getPriceAmountByCurrency($this->getCurrency());
            $result[$option->getIdAlphaNumeric()] = [
                'amount' => $amount->getAmount(),
                'currency' => $amount->getCurrency()->getSymbol(),
                'countLeft' => $option->getCapacityLeft($this->getApplicationDao()->countIssuedApplicationsWithOption($option))
            ];
        }
        return $result;
    }

    /**
     * @param AdditionEntity $addition
     * @param $prices array
     * @return array id=>Html
     */
    protected function createAdditionOptions(AdditionEntity $addition, $prices) {
        $result = [];
        foreach ($addition->getOptions() as $option) {
            $result[$option->getIdAlphaNumeric()] = $this->createOptionLabel($option, $prices);
        }
        return $result;
    }

    abstract protected function isAdmin();

    /**
     * @param \App\Model\Persistence\Entity\OptionEntity $option
     * @param $prices array
     * @return string
     */
    protected function createOptionLabel(OptionEntity $option, $prices) {
        $result = Html::el();
        if ($option->getName()) {
            $result->addHtml(
                Html::el('span', ['class' => 'name'])
                    ->setText($option->getName())
            );
        }
        if (isset($prices[$option->getIdAlphaNumeric()]) && isset($prices[$option->getIdAlphaNumeric()]['amount']) && isset($prices[$option->getIdAlphaNumeric()]['currency'])) {
            $price = $prices[$option->getIdAlphaNumeric()];
            $result->addHtml(
                Html::el('span', ['class' => 'description inline'])
                    ->setText($price['amount'] . $price['currency'])
            );
        }
        if (isset($prices[$option->getIdAlphaNumeric()]) && isset($prices[$option->getIdAlphaNumeric()]['countLeft'])) {
            $left = $prices[$option->getIdAlphaNumeric()]['countLeft'];
            $result->addHtml(
                Html::el('span', ['class' => 'description inline', 'data-price-predisable' => $left == 0&&!$this->isAdmin()])
                    ->setText("Zbývá $left míst")
            );
        }
        if ($option->getDescription()) {
            $result->addHtml(
                Html::el('span', ['class' => 'description'])
                    ->setHtml($option->getDescription())
            );
        }
        return $result;
    }

}