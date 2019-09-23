<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 22.1.18
 * Time: 11:02
 */

namespace App\FrontModule\Controls\Menus;


use App\Controls\Menus\Menu;
use App\Model\Persistence\Entity\CartEntity;
use App\Model\Persistence\Entity\EarlyEntity;
use App\Model\Persistence\Entity\EventEntity;
use App\Model\Persistence\Entity\ReservationEntity;
use App\Model\Persistence\Entity\SubstituteEntity;

class MenuFactory extends \App\Controls\Menus\MenuFactory {

    public function create(): Menu {
        $menu = parent::create();

        $events = $menu->add('Entity.Plural.Event', ':Front:Homepage:default')
            ->setIcon('fa fa-calendar');
        $event = $events->add('Entity.Singular.Event', ':Front:Event:register')
            ->setIcon('fa fa-calendar-o')
            ->setCurentable(false)
            ->setLinkParamNeeded(EventEntity::class);
        $event->add('Entity.Singular.Registration', ':Front:Event:register')
            ->setIcon('fa fa-user-plus')
            ->setLinkParamNeeded(EventEntity::class);
        $event->add('Entity.Singular.Substitute', ':Front:Event:substitute')
            ->setIcon('fa fa-retweet')
            ->setLinkParamNeeded(EventEntity::class);
        $early = $event->add('Entity.Singular.Early', ':Front:Early:register')
            ->setIcon('fa fa-star')
            ->setCurentable(false)
            ->setLinkParamNeeded(EarlyEntity::class . '...');
        $early->add('Entity.Singular.Registration', ':Front:Early:register')
            ->setIcon('fa fa-user-plus')
            ->setLinkParamNeeded(EarlyEntity::class . '...');
        $early->add('Entity.Singular.Substitute', ':Front:Early:substitute')
            ->setIcon('fa fa-retweet')
            ->setLinkParamNeeded(EarlyEntity::class . '...');
        $reservation = $event->add('Entity.Singular.Reservation', ':Front:Reservation:register')
            ->setIcon('fa fa-address-book-o')
            ->setLinkParamNeeded(ReservationEntity::class . '...')
            ->setCurentable(false);
        $reservation->add('Entity.Singular.Registration', ':Front:Reservation:register')
            ->setIcon('fa fa-user-plus')
            ->setLinkParamNeeded(ReservationEntity::class . '...');
        $substitute = $event->add('Entity.Singular.Substitute', ':Front:Substitute:register')
            ->setIcon('fa fa-retweet')
            ->setLinkParamNeeded(SubstituteEntity::class)
            ->setCurentable(false);
        $substitute->add('Entity.Singular.Registration', ':Front:Substitute:register')
            ->setIcon('fa fa-user-plus')
            ->setLinkParamNeeded(SubstituteEntity::class . '...');
        $event->add('Entity.Singular.Cart', ':Front:Cart:default')
            ->setIcon('fa fa-shopping-cart')
            ->setLinkParamNeeded(CartEntity::class . '...');
        return $menu;
    }
}