<?php

declare(strict_types=1);

namespace Ticketer\Modules\FrontModule\Controls\Menus;

use Ticketer\Controls\Menus\Menu;
use Ticketer\Model\Database\Entities\CartEntity;
use Ticketer\Model\Database\Entities\EarlyEntity;
use Ticketer\Model\Database\Entities\EventEntity;
use Ticketer\Model\Database\Entities\ReservationEntity;
use Ticketer\Model\Database\Entities\SubstituteEntity;

class MenuFactory extends \Ticketer\Controls\Menus\MenuFactory
{

    public function create(): Menu
    {
        $menu = parent::create();

        $events = $menu->addSubmenu('Entity.Plural.Event', ':Front:Homepage:default')
            ->setIcon('fa fa-calendar');
        $event = $events->addSubmenu('Entity.Singular.Event', ':Front:Event:register')
            ->setIcon('fa fa-calendar-o')
            ->setCurentable(false)
            ->setLinkParamNeeded(EventEntity::class);
        $event->addSubmenu('Entity.Singular.Registration', ':Front:Event:register')
            ->setIcon('fa fa-user-plus')
            ->setLinkParamNeeded(EventEntity::class);
        $event->addSubmenu('Entity.Singular.Substitute', ':Front:Event:substitute')
            ->setIcon('fa fa-retweet')
            ->setLinkParamNeeded(EventEntity::class);
        $early = $event->addSubmenu('Entity.Singular.Early', ':Front:Early:register')
            ->setIcon('fa fa-star')
            ->setCurentable(false)
            ->setLinkParamNeeded(EarlyEntity::class . '...');
        $early->addSubmenu('Entity.Singular.Registration', ':Front:Early:register')
            ->setIcon('fa fa-user-plus')
            ->setLinkParamNeeded(EarlyEntity::class . '...');
        $early->addSubmenu('Entity.Singular.Substitute', ':Front:Early:substitute')
            ->setIcon('fa fa-retweet')
            ->setLinkParamNeeded(EarlyEntity::class . '...');
        $reservation = $event->addSubmenu('Entity.Singular.Reservation', ':Front:Reservation:register')
            ->setIcon('fa fa-address-book-o')
            ->setLinkParamNeeded(ReservationEntity::class . '...')
            ->setCurentable(false);
        $reservation->addSubmenu('Entity.Singular.Registration', ':Front:Reservation:register')
            ->setIcon('fa fa-user-plus')
            ->setLinkParamNeeded(ReservationEntity::class . '...');
        $substitute = $event->addSubmenu('Entity.Singular.Substitute', ':Front:Substitute:register')
            ->setIcon('fa fa-retweet')
            ->setLinkParamNeeded(SubstituteEntity::class)
            ->setCurentable(false);
        $substitute->addSubmenu('Entity.Singular.Registration', ':Front:Substitute:register')
            ->setIcon('fa fa-user-plus')
            ->setLinkParamNeeded(SubstituteEntity::class . '...');
        $event->addSubmenu('Entity.Singular.Cart', ':Front:Cart:default')
            ->setIcon('fa fa-shopping-cart')
            ->setLinkParamNeeded(CartEntity::class . '...');

        return $menu;
    }
}
