<?php

declare(strict_types=1);

namespace Ticketer\Controls\Menus;

use Nette\Localization\ITranslator;
use Stopka\NetteMenuControl\ISubmenuFactory;
use Ticketer\Controls\TInjectTranslator;
use Ticketer\Model\Database\Entities\AdditionEntity;
use Ticketer\Model\Database\Entities\CartEntity;
use Ticketer\Model\Database\Entities\EarlyEntity;
use Ticketer\Model\Database\Entities\EarlyWaveEntity;
use Ticketer\Model\Database\Entities\EventEntity;
use Ticketer\Model\Database\Entities\IEntity;
use Ticketer\Model\Database\Entities\OptionEntity;
use Ticketer\Model\Database\Entities\ReservationEntity;
use Ticketer\Model\Database\Entities\SubstituteEntity;
use Nette\SmartObject;

abstract class MenuFactory
{
    use SmartObject;
    use TInjectTranslator;

    private ISubmenuFactory $submenuFactory;

    public function __construct(ITranslator $translator, ISubmenuFactory $submenuFactory)
    {
        $this->submenuFactory = $submenuFactory;
        $this->injectTranslator($translator);
    }

    public function create(): Menu
    {
        $menu = new Menu($this->submenuFactory, $this->getTranslator(), 'Domů', ':Front:Homepage:default');
        $menu->setIcon('fa fa-home')
            ->setLinkParamPreprocessor(
                Menu::LINK_PARAM_PROCESSOR_ALL,
                function ($value, string $key, Menu $menu) {
                    if (!$value instanceof IEntity) {
                        return $value;
                    }

                    return $value->getId();
                }
            )->setLinkParamPreprocessor(
                CartEntity::class,
                function (CartEntity $value, string $key, Menu $menu): CartEntity {
                    $event = $value->getEvent();
                    if (null !== $event) {
                        $menu->setLinkParam(EventEntity::class, $event);
                    }

                    return $value;
                }
            )->setLinkParamPreprocessor(
                EarlyEntity::class,
                function (EarlyEntity $value, string $key, Menu $menu): EarlyEntity {
                    $earlyWave = $value->getEarlyWave();
                    if (null !== $earlyWave) {
                        $menu->setLinkParam(EarlyWaveEntity::class, $earlyWave);
                    }

                    return $value;
                }
            )->setLinkParamPreprocessor(
                ReservationEntity::class,
                function (ReservationEntity $value, string $key, Menu $menu): ReservationEntity {
                    $event = $value->getEvent();
                    if (null !== $event) {
                        $menu->setLinkParam(EventEntity::class, $event);
                    }

                    return $value;
                }
            )->setLinkParamPreprocessor(
                SubstituteEntity::class,
                function (SubstituteEntity $value, string $key, Menu $menu): SubstituteEntity {
                    $event = $value->getEvent();
                    if (null !== $event) {
                        $menu->setLinkParam(EventEntity::class, $event);
                    }

                    return $value;
                }
            )->setLinkParamPreprocessor(
                EarlyWaveEntity::class,
                function (EarlyWaveEntity $value, string $key, Menu $menu): EarlyWaveEntity {
                    $event = $value->getEvent();
                    if (null !== $event) {
                        $menu->setLinkParam(EventEntity::class, $event);
                    }

                    return $value;
                }
            )->setLinkParamPreprocessor(
                AdditionEntity::class,
                function (AdditionEntity $value, string $key, Menu $menu): AdditionEntity {
                    $event = $value->getEvent();
                    if (null !== $event) {
                        $menu->setLinkParam(EventEntity::class, $event);
                    }

                    return $value;
                }
            )->setLinkParamPreprocessor(
                OptionEntity::class,
                function (OptionEntity $value, string $key, Menu $menu): OptionEntity {
                    $addition = $value->getAddition();
                    if (null !== $addition) {
                        $menu->setLinkParam(AdditionEntity::class, $addition);
                    }

                    return $value;
                }
            );

        return $menu;
    }
}
