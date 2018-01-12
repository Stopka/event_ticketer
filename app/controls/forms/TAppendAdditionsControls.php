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

trait TAppendAdditionsControls {
    use TRecalculateControl;

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

    protected function appendAdditionsControls(Form $form, Container $container, int $index = 0) {
        $subcontainer = $container->addContainer('addittions');
        foreach ($this->getEvent()->getAdditions() as $addition) {
            if (!$addition->isVisible()) {
                continue;
            }
            $this->appendAdditionContols($subcontainer, $addition, $index);
        }
        $subcontainer['total'] = new \Stopka\NetteFormRenderer\Forms\Controls\Html('Celkem za přihlášku',
            Html::el('div', ['class' => 'price_subtotal'])
                ->addHtml(Html::el('span', ['class' => 'price_amount'])->setText('…'))
                ->addHtml(Html::el('span', ['class' => 'price_currency']))->addHtml($this->createRecalculateHtml())
        );
    }

    protected function appendAdditionContols(Container $container, AdditionEntity $addition, int $index) {
        $prices = $this->createAdditionPrices($addition);
        $options = $this->createAdditionOptions($addition, $prices);
        $preselectedOptions = $this->getPreselectedOptions($addition, $index);
        $predisabledOptions = $this->getPredisabledOptions($addition, $prices);
        if (!count($options)) {
            return;
        }
        if ($addition->getMinimum() !== 1 || $addition->getMaximum() > 1 || $addition->getMaximum() == count($options)) {
            $control = $container->addCheckboxList($addition->getIdAlphaNumeric(), $addition->getName(), $options)
                ->setRequired($addition->getMinimum() > 0)
                ->setTranslator()
                ->setDefaultValue($preselectedOptions);
        } else {
            $control = $container->addRadioList($addition->getIdAlphaNumeric(), $addition->getName(), $options)
                ->setRequired()
                ->setTranslator();
            if ($preselectedOptions) {
                $control->setDefaultValue($preselectedOptions[0]);
            }
        }
        if ($preselectedOptions) {
            $control->getControlPrototype()
                ->setAttribute('data-price-prechecked', json_encode($preselectedOptions));
        }
        if ($predisabledOptions) {
            $control->getControlPrototype()
                ->setAttribute('data-price-predisabled', json_encode($predisabledOptions));
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
            $result[$option->getId()] = $this->createOptionLabel($option, $prices);
        }
        return $result;
    }

    /**
     * @param AdditionEntity $additionEntity
     * @return array
     */
    protected function getPreselectedOptions(AdditionEntity $additionEntity, int $index = 1): array {
        $result = [];
        foreach ($additionEntity->getOptions() as $option) {
            if ($option->getAutoSelect() == OptionEntity::AUTOSELECT_ALWAYS) {
                $result[] = $option->getId();
            }
            if ($option->getAutoSelect() == OptionEntity::AUTOSELECT_SECONDON && $index >= 2) {
                $result[] = $option->getId();
            }
        }
        return $result;
    }

    /**
     * @param AdditionEntity $additionEntity
     * @param array $prices
     * @return array
     */
    protected function getPredisabledOptions(AdditionEntity $additionEntity, array $prices): array {
        $result = [];
        foreach ($additionEntity->getOptions() as $option) {
            $isAutoselected = $option->getAutoSelect() != OptionEntity::AUTOSELECT_NONE;
            $isFull = isset($prices[$option->getId()]['countLeft']) && $prices[$option->getId()]['countLeft'] === 0;
            if ($isAutoselected || $isFull) {
                $result[] = $option->getId();
            }
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
                Html::el('span', ['class' => 'description inline price'])
                    ->setText($price['amount'] . $price['currency'])
            );
        }
        if (isset($prices[$option->getId()]) && isset($prices[$option->getId()]['countLeft'])) {
            $left = $prices[$option->getId()]['countLeft'];
            $result->addHtml(
                Html::el('span', ['class' => 'description inline countLeft', 'data-price-predisable' => $left == 0 && !$this->isAdmin()])
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