<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 24.1.17
 * Time: 22:14
 */

namespace App\Controls\Forms;


use App\Model\Entities\AdditionEntity;
use App\Model\Facades\ApplicationFacade;
use App\Model\Persistence\Entity\CurrencyEntity;
use App\Model\Persistence\Entity\EventEntity;
use App\Model\Persistence\Entity\OptionEntity;
use Nette\Forms\Container;
use Nette\Utils\Html;
use Stopka\NetteFormRenderer\HtmlFormComponent;

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
     * @return ApplicationFacade
     */
    abstract protected function getApplicationFacade();

    protected function appendAdditionsControls(Form $form, Container $container) {
        $subcontainer = $container->addContainer('addittions');
        foreach ($this->getEvent()->getAdditions() as $addition) {
            if (!$addition->isVisible()) {
                continue;
            }
            $this->appendAdditionContols($subcontainer, $addition);
        }
        $subcontainer['total'] = new HtmlFormComponent('Celkem za přihlášku',
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
        if ($addition->getMaximum() > 1 && count($options) > 1) {
            $control = $container->addCheckboxList($addition->getId(), $addition->getName(), $options)
                ->setRequired($addition->getMinimum() == 0)
                ->setTranslator();
        } else {
            $control = $container->addRadioList($addition->getId(), $addition->getName(), $options)
                ->setRequired()
                ->setTranslator();
            if (count($options) == 1) {
                $keys = array_keys($options);
                $key = array_pop($keys);
                $control->getControlPrototype()->setAttribute('data-price-precheck', $key);
                $control->setDefaultValue($key);
            }
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
            $result[$option->getId()] = [
                'amount' => $amount->getAmount(),
                'currency' => $amount->getCurrency()->getSymbol(),
                'countLeft' => $option->getCapacityLeft($this->getApplicationFacade()->countIssuedApplicationsWithOption($option))
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
            $result[$option->getId()] = $this->createOptionLabel($option, $prices);
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
        if (isset($prices[$option->getId()]) && isset($prices[$option->getId()]['amount']) && isset($prices[$option->getId()]['currency'])) {
            $price = $prices[$option->getId()];
            $result->addHtml(
                Html::el('span', ['class' => 'description inline'])
                    ->setText($price['amount'] . $price['currency'])
            );
        }
        if (isset($prices[$option->getId()]) && isset($prices[$option->getId()]['countLeft'])) {
            $left = $prices[$option->getId()]['countLeft'];
            $result->addHtml(
                Html::el('span', ['class' => 'description inline', 'data-price-predisable' => $left == 0&&!$this->isAdmin()])
                    ->setText("Zbývá $left míst")
            );
        }
        return $result;
    }

}