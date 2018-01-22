<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 22.1.18
 * Time: 11:02
 */

namespace App\Controls\Menus;


use App\Controls\TInjectTranslator;
use App\Model\Persistence\Entity\AdditionEntity;
use App\Model\Persistence\Entity\CartEntity;
use App\Model\Persistence\Entity\EarlyEntity;
use App\Model\Persistence\Entity\EarlyWaveEntity;
use App\Model\Persistence\Entity\EventEntity;
use App\Model\Persistence\Entity\IEntity;
use App\Model\Persistence\Entity\OptionEntity;
use App\Model\Persistence\Entity\ReservationEntity;
use App\Model\Persistence\Entity\SubstituteEntity;
use Kdyby\Translation\ITranslator;
use Nette\SmartObject;

abstract class MenuFactory {
    use SmartObject, TInjectTranslator;

    public function __construct(ITranslator $translator) {
        $this->injectTranslator($translator);
    }

    public function create(): Menu {
        $menu = new Menu($this->getTranslator(), 'Domů', ':Front:Homepage:default');
        $menu->setIcon('fa fa-home')
            ->setLinkParamPreprocessor(Menu::LINK_PARAM_PROCESSOR_ALL, function ($value, string $key, Menu $menu) {
                if (!is_subclass_of($value, IEntity::class)) {
                    return $value;
                }
                /** @var IEntity $value */
                return $value->getId();
            })->setLinkParamPreprocessor(CartEntity::class, function (CartEntity $value, string $key, Menu $menu) {
                if ($event = $value->getEvent()) {
                    $menu->setLinkParam(EventEntity::class, $event);
                }
                return $value;
            })->setLinkParamPreprocessor(EarlyEntity::class, function (EarlyEntity $value, string $key, Menu $menu) {
                if ($earlyWave = $value->getEarlyWave()) {
                    $menu->setLinkParam(EarlyWaveEntity::class, $earlyWave);
                }
                return $value;
            })->setLinkParamPreprocessor(ReservationEntity::class, function (ReservationEntity $value, string $key, Menu $menu) {
                if ($event = $value->getEvent()) {
                    $menu->setLinkParam(EventEntity::class, $event);
                }
                return $value;
            })->setLinkParamPreprocessor(SubstituteEntity::class, function (SubstituteEntity $value, string $key, Menu $menu) {
                if ($event = $value->getEvent()) {
                    $menu->setLinkParam(EventEntity::class, $event);
                }
                return $value;
            })->setLinkParamPreprocessor(EarlyWaveEntity::class, function (EarlyWaveEntity $value, string $key, Menu $menu) {
                if ($event = $value->getEvent()) {
                    $menu->setLinkParam(EventEntity::class, $event);
                }
                return $value;
            })->setLinkParamPreprocessor(AdditionEntity::class, function (AdditionEntity $value, string $key, Menu $menu) {
                if ($event = $value->getEvent()) {
                    $menu->setLinkParam(EventEntity::class, $event);
                }
                return $value;
            })->setLinkParamPreprocessor(OptionEntity::class, function (OptionEntity $value, string $key, Menu $menu) {
                if ($addition = $value->getAddition()) {
                    $menu->setLinkParam(AdditionEntity::class, $addition);
                }
                return $value;
            });

        return $menu;
    }
}