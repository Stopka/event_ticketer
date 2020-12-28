<?php

declare(strict_types=1);

namespace Ticketer\Controls\Forms;

use Contributte\Translation\Wrappers\NotTranslate;
use Nette\Localization\ITranslator;
use Ticketer\Model\Database\Entities\AdditionVisibilityEntity;
use Ticketer\Model\Database\Enums\OptionAutoselectEnum;
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

class AdditionsControlsBuilder
{
    use TRecalculateControl;
    use SmartObject;

    public const CONTAINER_NAME_ADDITIONS = 'additions';

    /** @var callable(AdditionVisibilityEntity $addition):bool */
    private $visibilityResolver;

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

    /** @var callable(AdditionVisibilityEntity $addition):bool */
    private $predisabledAdditionVisibilityResolver;

    /** @var string[] */
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
        $this->visibilityResolver = static function (AdditionVisibilityEntity $visibility): bool {
            return $visibility->isReservation();
        };
        $this->predisabledAdditionVisibilityResolver = static function (AdditionVisibilityEntity $visibility): bool {
            return false;
        };
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
     * @param callable(AdditionVisibilityEntity $addition):bool $resolver
     * @return AdditionsControlsBuilder
     */
    public function setVisibilityResolver(callable $resolver): self
    {
        $this->visibilityResolver = $resolver;

        return $this;
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
            if (!$addition->getVisibility()->matches($this->visibilityResolver)) {
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
     * @param array<string> $values
     * @param AdditionEntity $addition
     * @param int $index
     * @return array<string>
     * @throws InvalidStateException
     */
    protected function preprocessAdditionValues(array $values, AdditionEntity $addition, int $index = 1): array
    {
        $prices = $this->createAdditionPrices($addition);
        $preselectedOptionIds = $this->getPreselectedOptions($addition, $index);
        $predisabledOptionIds = $this->getPredisabledOptions($addition, $prices);
        foreach ($addition->getOptions() as $option) {
            if (
                !in_array($option->getId()->toString(), $values, true)
                && in_array($option->getId()->toString(), $preselectedOptionIds, true)
                && in_array($option->getId()->toString(), $predisabledOptionIds, true)
            ) {
                $values[] = $option->getId()->toString();
            }
            if (
                in_array($option->getId()->toString(), $values, true)
                && !in_array($option->getId()->toString(), $preselectedOptionIds, true)
                && in_array($option->getId()->toString(), $predisabledOptionIds, true)
            ) {
                $i = array_search($option->getId()->toString(), $values, true);
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
            if (!$addition->getVisibility()->matches($this->visibilityResolver)) {
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
     * @return array<string,mixed>
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
            $result[(string)$option->getId()] = [
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
     * @return array<string,Html> id=>Html
     */
    protected function createAdditionOptions(AdditionEntity $addition, $prices): array
    {
        $result = [];
        foreach ($addition->getOptions() as $option) {
            $result[(string)$option->getId()] = $this->createOptionLabel($option, $prices);
        }

        return $result;
    }

    /**
     * @param AdditionEntity $additionEntity
     * @param int $index
     * @return string[]
     */
    protected function getPreselectedOptions(AdditionEntity $additionEntity, int $index = 1): array
    {
        $result = $this->preselectedOptionIds;
        foreach ($additionEntity->getOptions() as $option) {
            if ($option->getAutoSelect()->equals(OptionAutoselectEnum::ALWAYS())) {
                $result[] = (string)$option->getId();
            }
            if (
                $option->getAutoSelect()->equals(OptionAutoselectEnum::SECOND_ON())
                && $index >= 2
            ) {
                $result[] = (string)$option->getId();
            }
        }

        return $result;
    }

    /**
     * @param AdditionEntity $additionEntity
     * @param mixed[] $prices
     * @return string[]
     */
    protected function getPredisabledOptions(AdditionEntity $additionEntity, array $prices): array
    {
        $result = [];
        $isAdditionPredisabled = $additionEntity->getVisibility()->matches(
            $this->predisabledAdditionVisibilityResolver
        );
        foreach ($additionEntity->getOptions() as $option) {
            $isAutoSelected = !$option->getAutoSelect()->equals(OptionAutoselectEnum::NONE());
            $isFull = isset($prices[$option->getId()->toString()]['countLeft'])
                && 0 === $prices[$option->getId()->toString()]['countLeft'];
            if (
                !$this->isAdmin()
                && ($isAutoSelected || $isFull || $isAdditionPredisabled)
            ) {
                $result[] = (string)$option->getId();
            }
        }

        return $result;
    }

    protected function isAdmin(): bool
    {
        return $this->admin;
    }

    /**
     * @param callable(AdditionVisibilityEntity $addition):bool $resolver
     */
    public function setPredisabledAdditionVisibilityResolver(callable $resolver): void
    {
        $this->predisabledAdditionVisibilityResolver = $resolver;
    }

    /**
     * @param string[] $optionIds
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
    protected function createOptionLabel(OptionEntity $option, array $prices): Html
    {
        $result = Html::el();
        if (null !== $option->getName()) {
            $result->addHtml(
                Html::el('span', ['class' => 'name'])
                    ->setText($option->getName())
            );
        }
        if (
            isset(
                $prices[(string)$option->getId()]['amount'],
                $prices[(string)$option->getId()]['currency']
            ) && $this->isVisiblePrice()
        ) {
            $price = $prices[(string)$option->getId()];
            $result->addHtml(
                Html::el('span', ['class' => 'description inline price'])
                    ->setText($price['amount'] . $price['currency'])
            );
        }
        if (isset($prices[(string)$option->getId()]['countLeft']) && $this->isVisibleCountLeft()) {
            $left = $prices[(string)$option->getId()]['countLeft'];
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
