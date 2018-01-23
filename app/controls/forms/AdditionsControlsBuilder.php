<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 24.1.17
 * Time: 22:14
 */

namespace App\Controls\Forms;

use App\Model\Exception\FormControlException;
use App\Model\Exception\InvalidInputException;
use App\Model\Exception\InvalidStateException;
use App\Model\Exception\TranslatedException;
use App\Model\Persistence\Dao\ApplicationDao;
use App\Model\Persistence\Entity\AdditionEntity;
use App\Model\Persistence\Entity\CurrencyEntity;
use App\Model\Persistence\Entity\EventEntity;
use App\Model\Persistence\Entity\OptionEntity;
use Kdyby\Translation\ITranslator;
use Nette\Forms\Container;
use Nette\Utils\Html;

class AdditionsControlsBuilder {
    use TRecalculateControl;

    const CONTAINER_NAME_ADDITIONS = 'additions';

    /** @var string */
    private $visibilityPlace = AdditionEntity::VISIBLE_RESERVATION;

    /** @var bool */
    private $visiblePrice = false;

    /** @var bool */
    private $visiblePriceTotal = false;

    /** @var bool */
    private $visibleCountLeft = false;

    /** @var EventEntity */
    private $eventEntity;

    /** @var CurrencyEntity */
    private $currencyEntity;

    /** @var ApplicationDao */
    private $applicationDao;

    /** @var ITranslator */
    private $translator;

    /** @var bool */
    private $admin = false;

    /** @var string[] */
    private $predisabledAdditionVisibilities = [];

    /** @var int[] */
    private $preselectedOptionIds = [];

    public function __construct(
        EventEntity $eventEntity,
        CurrencyEntity $currencyEntity,
        ApplicationDao $applicationDao,
        ITranslator $translator
    ) {
        $this->eventEntity = $eventEntity;
        $this->currencyEntity = $currencyEntity;
        $this->applicationDao = $applicationDao;
        $this->translator = $translator;
    }


    protected function getTranslator(): ?ITranslator {
        return $this->translator;
    }

    /**
     * @return EventEntity
     */
    protected function getEvent(): EventEntity {
        return $this->eventEntity;
    }

    /**
     * @return CurrencyEntity
     */
    protected function getCurrency(): CurrencyEntity {
        return $this->currencyEntity;
    }

    /**
     * @param bool $admin
     * @return $this
     */
    public function setAdmin(bool $admin = true): self {
        $this->admin = $admin;
        return $this;
    }

    /**
     * @return ApplicationDao
     */
    protected function getApplicationDao(): ApplicationDao {
        return $this->applicationDao;
    }

    /**
     * @param string $place
     * @return AdditionsControlsBuilder
     */
    public function setVisibilityPlace(string $place = AdditionEntity::VISIBLE_RESERVATION): self {
        $this->visibilityPlace = $place;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getVisibilityPlace(): string {
        return $this->visibilityPlace;
    }

    /**
     * @return bool
     */
    public function isVisiblePrice(): bool {
        return $this->visiblePrice;
    }

    /**
     * @param bool $visiblePrice
     * @return $this
     */
    public function setVisiblePrice(bool $visiblePrice = true): self {
        $this->visiblePrice = $visiblePrice;
        return $this;
    }

    /**
     * @return bool
     */
    public function isVisiblePriceTotal(): bool {
        return $this->visiblePriceTotal;
    }

    /**
     * @param bool $visiblePriceTotal
     * @return $this
     */
    public function setVisiblePriceTotal(bool $visiblePriceTotal = true): self {
        $this->visiblePriceTotal = $visiblePriceTotal;
        return $this;
    }

    /**
     * @return bool
     */
    public function isVisibleCountLeft(): bool {
        return $this->visibleCountLeft;
    }

    /**
     * @param bool $visibleCountLeft
     * @return $this
     */
    public function setVisibleCountLeft(bool $visibleCountLeft = true): self {
        $this->visibleCountLeft = $visibleCountLeft;
        return $this;
    }


    /**
     * @param Container $container
     * @param int $index
     */
    public function appendAdditionsControls(Container $container, int $index = 1) {
        $additionsContainer = $container->addContainer(self::CONTAINER_NAME_ADDITIONS);
        foreach ($this->getEvent()->getAdditions() as $addition) {
            if (!$addition->isVisibleIn($this->visibilityPlace)) {
                continue;
            }
            $this->appendAdditionControls($additionsContainer, $addition, $index);
        }
        if ($this->isVisiblePriceTotal()) {
            $additionsContainer['total'] = new \Stopka\NetteFormRenderer\Forms\Controls\Html('Form.Application.TotalPrice',
                Html::el('div', ['class' => 'price_subtotal'])
                    ->addHtml(Html::el('span', ['class' => 'price_amount'])->setText('…'))
                    ->addHtml(Html::el('span', ['class' => 'price_currency']))->addHtml($this->createRecalculateHtml())
            );
        }
    }

    /**
     * @param $values
     * @param AdditionEntity $addition
     * @param int $index
     * @return array
     * @throws InvalidStateException
     */
    protected function preprocessAdditionValues($values, AdditionEntity $addition, int $index = 1): array {
        if (is_integer($values)) {
            $values = [$values];
        }
        $prices = $this->createAdditionPrices($addition);
        $preselectedOptionIds = $this->getPreselectedOptions($addition, $index);
        $predisabledOptionIds = $this->getPredisabledOptions($addition, $prices);
        foreach ($addition->getOptions() as $option) {
            if (
                !in_array($option->getId(), $values) &&
                in_array($option->getId(), $preselectedOptionIds) &&
                in_array($option->getId(), $predisabledOptionIds)
            ) {
                $values[] = $option->getId();
            }
            if (
                in_array($option->getId(), $values) &&
                !in_array($option->getId(), $preselectedOptionIds) &&
                in_array($option->getId(), $predisabledOptionIds)
            ) {
                $i = array_search($option->getId(), $values);
                unset($values[$i]);
            }
        }
        $count = count($values);
        if ($count < $addition->getMinimum()) {
            throw new InvalidInputException("Error.Addition.Minimum.InvalidInput", $addition->getMinimum());
        }
        if ($count > $addition->getMaximum()) {
            throw new InvalidInputException("Error.Addition.Maximum.InvalidInput", $addition->getMaximum());
        }
        return $values;
    }

    /**
     * @param array $values
     * @param int $index
     * @return array
     * @throws FormControlException
     */
    public function preprocessAdditionsValues(array $values, int $index = 1): array {
        $additionsValues = $values[self::CONTAINER_NAME_ADDITIONS];
        foreach ($this->getEvent()->getAdditions() as $addition) {
            if (!$addition->isVisibleIn($this->visibilityPlace)) {
                continue;
            }
            try {
                $additionsValues[$addition->getId()] = $this->preprocessAdditionValues($additionsValues[$addition->getId()] ?? [], $addition, $index);
            } catch (TranslatedException $e) {
                throw new FormControlException($e, [self::CONTAINER_NAME_ADDITIONS, $addition->getId()]);
            }
        }
        $values[self::CONTAINER_NAME_ADDITIONS] = $additionsValues;
        return $values;
    }

    protected function appendAdditionControls(Container $container, AdditionEntity $addition, int $index) {
        $prices = $this->createAdditionPrices($addition);
        $options = $this->createAdditionOptions($addition, $prices);
        $preselectedOptionIds = $this->getPreselectedOptions($addition, $index);
        $predisabledOptionIds = $this->getPredisabledOptions($addition, $prices);
        if (!count($options)) {
            return;
        }
        if ($addition->getMinimum() !== 1 || $addition->getMaximum() > 1 || $addition->getMaximum() == count($options)) {
            $control = $container->addCheckboxList($addition->getId(), $addition->getName(), $options)
                ->setRequired(false)
                ->setTranslator()
                ->setDefaultValue($preselectedOptionIds);
        } else {
            $control = $container->addRadioList($addition->getId(), $addition->getName(), $options)
                ->setRequired(false)
                ->setTranslator();
            if ($preselectedOptionIds) {
                $control->setDefaultValue($preselectedOptionIds[0]);
            }
        }
        if ($preselectedOptionIds) {
            $control->getControlPrototype()
                ->setAttribute('data-price-prechecked', json_encode($preselectedOptionIds));
        }
        if ($predisabledOptionIds) {
            $control->getControlPrototype()
                ->setAttribute('data-price-predisabled', json_encode($predisabledOptionIds));
        }
        if ($prices) {
            /** @noinspection PhpUndefinedMethodInspection */
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
     * @param int $index
     * @return int[]
     */
    protected function getPreselectedOptions(AdditionEntity $additionEntity, int $index = 1): array {
        $result = $this->preselectedOptionIds;
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
     * @return int[]
     */
    protected function getPredisabledOptions(AdditionEntity $additionEntity, array $prices): array {
        $result = [];
        $isAdditionPredisabled = false;
        foreach ($this->predisabledAdditionVisibilities as $additionVisibility) {
            if (in_array($additionVisibility, $additionEntity->getVisible())) {
                $isAdditionPredisabled = true;
                break;
            }
        }
        foreach ($additionEntity->getOptions() as $option) {
            $isAutoselected = $option->getAutoSelect() != OptionEntity::AUTOSELECT_NONE;
            $isFull = isset($prices[$option->getId()]['countLeft']) && $prices[$option->getId()]['countLeft'] === 0;
            if (!$this->isAdmin() && ($isAutoselected || $isFull || $isAdditionPredisabled)) {
                $result[] = $option->getId();
            }
        }
        return $result;
    }

    protected function isAdmin(): bool {
        return $this->admin;
    }

    /**
     * @param string[] $visibilities
     */
    public function setPredisabledAdditionVisibilities(array $visibilities = []) {
        $this->predisabledAdditionVisibilities = $visibilities;
    }

    /**
     * @param int[] $optionIds
     */
    public function setPreselectedOptions(array $optionIds = []) {
        $this->preselectedOptionIds = $optionIds;
    }

    public function resetPreselectedOptions() {
        $this->preselectedOptionIds = [];
    }

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
        if ($this->isVisiblePrice() && isset($prices[$option->getId()]) &&
            isset($prices[$option->getId()]['amount']) && isset($prices[$option->getId()]['currency'])) {
            $price = $prices[$option->getId()];
            $result->addHtml(
                Html::el('span', ['class' => 'description inline price'])
                    ->setText($price['amount'] . $price['currency'])
            );
        }
        if ($this->isVisibleCountLeft() && isset($prices[$option->getId()]) &&
            isset($prices[$option->getId()]['countLeft'])) {
            $left = $prices[$option->getId()]['countLeft'];
            $result->addHtml(
                Html::el('span', ['class' => 'description inline countLeft'])
                    ->setText($this->getTranslator()->translate('Occupancy.Left.Options', $left))
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