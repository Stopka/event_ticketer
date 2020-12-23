<?php

declare(strict_types=1);

namespace Ticketer\Controls\Forms;

use Contributte\Translation\Wrappers\NotTranslate;
use Nette\Localization\ITranslator;
use Ticketer\Model\Exceptions\FormControlException;
use Ticketer\Model\Exceptions\InvalidInputException;
use Ticketer\Model\Exceptions\InvalidStateException;
use Ticketer\Model\Exceptions\TranslatedException;
use Ticketer\Model\Database\Daos\ApplicationDao;
use Ticketer\Model\Database\Entities\AdditionEntity;
use Ticketer\Model\Database\Entities\CurrencyEntity;
use Ticketer\Model\Database\Entities\EventEntity;
use Ticketer\Model\Database\Entities\OptionEntity;
use Nette\Forms\Container;
use Nette\SmartObject;
use Nette\Utils\Html;

use function _HumbugBox39a196d4601e\RingCentral\Psr7\str;

class AdditionsControlsBuilder
{
    use TRecalculateControl;
    use SmartObject;

    public const CONTAINER_NAME_ADDITIONS = 'additions';

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

    /** @var bool */
    private $disabledMinimum = false;

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

    public function disableMinimum(bool $value = true): self
    {
        $this->disabledMinimum = $value;

        return $this;
    }

    protected function getTranslator(): ITranslator
    {
        return $this->translator;
    }

    /**
     * @return EventEntity
     */
    protected function getEvent(): EventEntity
    {
        return $this->eventEntity;
    }

    /**
     * @return CurrencyEntity
     */
    protected function getCurrency(): CurrencyEntity
    {
        return $this->currencyEntity;
    }

    /**
     * @param bool $admin
     * @return $this
     */
    public function setAdmin(bool $admin = true): self
    {
        $this->admin = $admin;

        return $this;
    }

    /**
     * @return ApplicationDao
     */
    protected function getApplicationDao(): ApplicationDao
    {
        return $this->applicationDao;
    }

    /**
     * @param string $place
     * @return AdditionsControlsBuilder
     */
    public function setVisibilityPlace(string $place = AdditionEntity::VISIBLE_RESERVATION): self
    {
        $this->visibilityPlace = $place;

        return $this;
    }

    /**
     * @return string
     */
    public function getVisibilityPlace(): string
    {
        return $this->visibilityPlace;
    }

    /**
     * @return bool
     */
    public function isVisiblePrice(): bool
    {
        return $this->visiblePrice;
    }

    /**
     * @param bool $visiblePrice
     * @return $this
     */
    public function setVisiblePrice(bool $visiblePrice = true): self
    {
        $this->visiblePrice = $visiblePrice;

        return $this;
    }

    /**
     * @return bool
     */
    public function isVisiblePriceTotal(): bool
    {
        return $this->visiblePriceTotal;
    }

    /**
     * @param bool $visiblePriceTotal
     * @return $this
     */
    public function setVisiblePriceTotal(bool $visiblePriceTotal = true): self
    {
        $this->visiblePriceTotal = $visiblePriceTotal;

        return $this;
    }

    /**
     * @return bool
     */
    public function isVisibleCountLeft(): bool
    {
        return $this->visibleCountLeft;
    }

    /**
     * @param bool $visibleCountLeft
     * @return $this
     */
    public function setVisibleCountLeft(bool $visibleCountLeft = true): self
    {
        $this->visibleCountLeft = $visibleCountLeft;

        return $this;
    }


    /**
     * @param Container $container
     * @param int $index
     */
    public function appendAdditionsControls(Container $container, int $index = 1): void
    {
        $additionsContainer = $container->addContainer(self::CONTAINER_NAME_ADDITIONS);
        foreach ($this->getEvent()->getAdditions() as $addition) {
            if (!$addition->isVisibleIn($this->visibilityPlace)) {
                continue;
            }
            $this->appendAdditionControls($additionsContainer, $addition, $index);
        }
        if ($this->isVisiblePriceTotal()) {
            $additionsContainer['total'] = new \Stopka\NetteFormRenderer\Forms\Controls\Html(
                'Form.Application.TotalPrice',
                Html::el('div', ['class' => 'price_subtotal'])
                    ->addHtml(
                        Html::el('span', ['class' => 'price_amount'])
                            ->setText('…')
                    )
                    ->addHtml(Html::el('span', ['class' => 'price_currency']))
                    ->addHtml($this->createRecalculateHtml())
            );
        }
    }

    /**
     * @param mixed $values
     * @param AdditionEntity $addition
     * @param int $index
     * @return array<mixed>
     * @throws InvalidStateException
     */
    protected function preprocessAdditionValues($values, AdditionEntity $addition, int $index = 1): array
    {
        if (is_int($values)) {
            $values = [$values];
        }
        $prices = $this->createAdditionPrices($addition);
        $preselectedOptionIds = $this->getPreselectedOptions($addition, $index);
        $predisabledOptionIds = $this->getPredisabledOptions($addition, $prices);
        foreach ($addition->getOptions() as $option) {
            if (
                !in_array($option->getId(), $values, true) &&
                in_array($option->getId(), $preselectedOptionIds, true) &&
                in_array($option->getId(), $predisabledOptionIds, true)
            ) {
                $values[] = $option->getId();
            }
            if (
                in_array($option->getId(), $values, true) &&
                !in_array($option->getId(), $preselectedOptionIds, true) &&
                in_array($option->getId(), $predisabledOptionIds, true)
            ) {
                $i = array_search($option->getId(), $values, true);
                unset($values[$i]);
            }
        }
        $count = count($values);
        if (!$this->disabledMinimum && $count < $addition->getMinimum()) {
            throw new InvalidInputException("Error.Addition.Minimum.InvalidInput", $addition->getMinimum());
        }
        if ($count > $addition->getMaximum()) {
            throw new InvalidInputException("Error.Addition.Maximum.InvalidInput", $addition->getMaximum());
        }

        return $values;
    }

    /**
     * @param array<mixed> $values
     * @param int $index
     * @return array<mixed>
     * @throws FormControlException
     */
    public function preprocessAdditionsValues(array $values, int $index = 1): array
    {
        $additionsValues = $values[self::CONTAINER_NAME_ADDITIONS];
        foreach ($this->getEvent()->getAdditions() as $addition) {
            if (!$addition->isVisibleIn($this->visibilityPlace)) {
                continue;
            }
            try {
                $additionsValues[$addition->getId()] = $this->preprocessAdditionValues(
                    $additionsValues[$addition->getId()] ?? [],
                    $addition,
                    $index
                );
            } catch (TranslatedException $e) {
                throw new FormControlException($e, [self::CONTAINER_NAME_ADDITIONS, (string)$addition->getId()]);
            }
        }
        $values[self::CONTAINER_NAME_ADDITIONS] = $additionsValues;

        return $values;
    }

    protected function appendAdditionControls(Container $container, AdditionEntity $addition, int $index): void
    {
        $prices = $this->createAdditionPrices($addition);
        $options = $this->createAdditionOptions($addition, $prices);
        $preselectedOptionIds = $this->getPreselectedOptions($addition, $index);
        $predisabledOptionIds = $this->getPredisabledOptions($addition, $prices);
        if (0 === count($options)) {
            return;
        }
        if (
            $this->disabledMinimum
            || 1 !== $addition->getMinimum()
            || $addition->getMaximum() > 1
            || $addition->getMaximum() === count($options)
        ) {
            $control = $container->addCheckboxList(
                (string)$addition->getId(),
                new NotTranslate((string)$addition->getName()),
                $options
            )
                ->setRequired(false)
                ->setDefaultValue($preselectedOptionIds);
        } else {
            $control = $container->addRadioList(
                (string)$addition->getId(),
                new NotTranslate((string)$addition->getName()),
                $options
            )
                ->setRequired(false);
            if (count($preselectedOptionIds) > 0) {
                $control->setDefaultValue($preselectedOptionIds[0]);
            }
        }
        if (count($preselectedOptionIds) > 0) {
            $control->getControlPrototype()
                ->setAttribute('data-price-prechecked', json_encode($preselectedOptionIds, JSON_THROW_ON_ERROR));
        }
        if (count($predisabledOptionIds) > 0) {
            $control->getControlPrototype()
                ->setAttribute('data-price-predisabled', json_encode($predisabledOptionIds, JSON_THROW_ON_ERROR));
        }
        if (count($prices) > 0) {
            /** @noinspection PhpUndefinedMethodInspection */
            $control->getControlPrototype()
                ->addClass('price_item')
                ->setAttribute('data-price-value', json_encode($prices, JSON_THROW_ON_ERROR));
        }
    }

    /**
     * @param AdditionEntity $addition
     * @return array<int,mixed>
     */
    protected function createAdditionPrices(AdditionEntity $addition): array
    {
        $result = [];
        foreach ($addition->getOptions() as $option) {
            $price = $option->getPrice();
            if (null === $price) {
                continue;
            }
            $amount = $price->getPriceAmountByCurrency($this->getCurrency());
            if (null === $amount) {
                continue;
            }
            $currency = $amount->getCurrency();
            if (null === $currency) {
                continue;
            }
            $result[(int)$option->getId()] = [
                'amount' => $amount->getAmount(),
                'currency' => $currency->getSymbol(),
                'countLeft' => $option->getCapacityLeft(
                    $this->getApplicationDao()->countIssuedApplicationsWithOption($option)
                ),
            ];
        }

        return $result;
    }

    /**
     * @param AdditionEntity $addition
     * @param mixed[] $prices
     * @return array<int,Html> id=>Html
     */
    protected function createAdditionOptions(AdditionEntity $addition, $prices): array
    {
        $result = [];
        foreach ($addition->getOptions() as $option) {
            $result[(int)$option->getId()] = $this->createOptionLabel($option, $prices);
        }

        return $result;
    }

    /**
     * @param AdditionEntity $additionEntity
     * @param int $index
     * @return int[]
     */
    protected function getPreselectedOptions(AdditionEntity $additionEntity, int $index = 1): array
    {
        $result = $this->preselectedOptionIds;
        foreach ($additionEntity->getOptions() as $option) {
            if (OptionEntity::AUTOSELECT_ALWAYS === $option->getAutoSelect()) {
                $result[] = (int)$option->getId();
            }
            if (OptionEntity::AUTOSELECT_SECONDON === $option->getAutoSelect() && $index >= 2) {
                $result[] = (int)$option->getId();
            }
        }

        return $result;
    }

    /**
     * @param AdditionEntity $additionEntity
     * @param mixed[] $prices
     * @return int[]
     */
    protected function getPredisabledOptions(AdditionEntity $additionEntity, array $prices): array
    {
        $result = [];
        $isAdditionPredisabled = false;
        foreach ($this->predisabledAdditionVisibilities as $additionVisibility) {
            if (in_array($additionVisibility, $additionEntity->getVisible(), true)) {
                $isAdditionPredisabled = true;
                break;
            }
        }
        foreach ($additionEntity->getOptions() as $option) {
            $isAutoselected = OptionEntity::AUTOSELECT_NONE !== $option->getAutoSelect();
            $isFull = isset($prices[$option->getId()]['countLeft']) && 0 === $prices[$option->getId()]['countLeft'];
            if (!$this->isAdmin() && ($isAutoselected || $isFull || $isAdditionPredisabled)) {
                $result[] = (int)$option->getId();
            }
        }

        return $result;
    }

    protected function isAdmin(): bool
    {
        return $this->admin;
    }

    /**
     * @param string[] $visibilities
     */
    public function setPredisabledAdditionVisibilities(array $visibilities = []): void
    {
        $this->predisabledAdditionVisibilities = $visibilities;
    }

    /**
     * @param int[] $optionIds
     */
    public function setPreselectedOptions(array $optionIds = []): void
    {
        $this->preselectedOptionIds = $optionIds;
    }

    public function resetPreselectedOptions(): void
    {
        $this->preselectedOptionIds = [];
    }

    /**
     * @param OptionEntity $option
     * @param mixed[] $prices
     * @return Html
     */
    protected function createOptionLabel(OptionEntity $option, $prices): Html
    {
        $result = Html::el();
        if (null !== $option->getName()) {
            $result->addHtml(
                Html::el('span', ['class' => 'name'])
                    ->setText($option->getName())
            );
        }
        if (
            $this->isVisiblePrice() && isset($prices[$option->getId()]) &&
            isset($prices[$option->getId()]['amount']) && isset($prices[$option->getId()]['currency'])
        ) {
            $price = $prices[$option->getId()];
            $result->addHtml(
                Html::el('span', ['class' => 'description inline price'])
                    ->setText($price['amount'] . $price['currency'])
            );
        }
        if (isset($prices[$option->getId()]['countLeft']) && $this->isVisibleCountLeft()) {
            $left = $prices[$option->getId()]['countLeft'];
            $result->addHtml(
                Html::el('span', ['class' => 'description inline countLeft'])
                    ->setText($this->getTranslator()->translate('Occupancy.Left.Options', $left))
            );
        }
        if (null !== $option->getDescription()) {
            $result->addHtml(
                Html::el('span', ['class' => 'description'])
                    ->setHtml($option->getDescription())
            );
        }

        return $result;
    }
}
