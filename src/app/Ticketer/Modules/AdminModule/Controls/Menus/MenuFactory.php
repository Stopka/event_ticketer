<?php

declare(strict_types=1);

namespace Ticketer\Modules\AdminModule\Controls\Menus;

use Ticketer\Controls\Menus\Menu;
use Ticketer\Model\Database\Entities\AdditionEntity;
use Ticketer\Model\Database\Entities\CartEntity;
use Ticketer\Model\Database\Entities\CurrencyEntity;
use Ticketer\Model\Database\Entities\EarlyEntity;
use Ticketer\Model\Database\Entities\EarlyWaveEntity;
use Ticketer\Model\Database\Entities\EventEntity;
use Ticketer\Model\Database\Entities\OptionEntity;

class MenuFactory extends \Ticketer\Controls\Menus\MenuFactory
{
    public function create(): Menu
    {
        $menu = parent::create();
        $admin = $menu->addSubmenu('Presenter.Admin.Homepage.Default.H1', ':Admin:Homepage:default')
            ->setIcon('fa fa-user-secret');
        $admin->addSubmenu('Presenter.Admin.Sign.In.H1', ':Admin:Sign:in')
            ->setIcon('fa fa-key');
        $setting = $admin->addSubmenu('Presenter.AdminLayout.Setting.H1', ':Admin:Currency:default')
            ->setCurentable(false)
            ->setIcon('fa fa-wrench');
        $currency = $setting->addSubmenu('Entity.Plural.Currency', ':Admin:Currency:default')
            ->setIcon('fa fa-btc');
        $currency->addSubmenu('Presenter.Admin.Currency.Edit.H1', ':Admin:Currency:edit')
            ->setLinkParamNeeded(CurrencyEntity::class)
            ->setIcon('fa fa-pencil');

        $events = $admin->addSubmenu('Entity.Plural.Event', ':Admin:Event:default')
            ->setIcon('fa fa-calendar');
        //$events->add('Presenter.Event.Add.H1', ':Admin:Event:add');
        $editEvent = $events->addSubmenu('Form.Action.Edit', ':Admin:Event:edit')
            ->setCurentable(false)
            ->setLinkParamNeeded(EventEntity::class)
            ->setIcon('fa fa-pencil');
        $editEvent->addSubmenu('Presenter.Admin.Event.Edit.H1', ':Admin:Event:edit')
            ->setIcon('fa fa-pencil')
            ->setLinkParamNeeded(EventEntity::class);
        $additions = $editEvent->addSubmenu('Entity.Plural.Addition', ':Admin:Addition:default')
            ->setIcon('fa fa-archive')
            ->setLinkParamNeeded(EventEntity::class);
        $addAddition = $additions->addSubmenu('Presenter.Admin.Addition.Add.H1', ':Admin:Addition:add')
            ->setIcon('fa fa-plus-circle')
            ->setLinkParamNeeded(EventEntity::class);
        $addAddition->addSubmenu('Entity.Plural.Option', ':Admin:Addition:add')
            ->setIcon('fa fa-list-ul')
            ->setLinkParamNeeded('...');
        $editAddition = $additions->addSubmenu('Form.Action.Edit', ':Admin:Addition:edit')
            ->setIcon('fa fa-pencil')
            ->setCurentable(false)
            ->setLinkParamNeeded(AdditionEntity::class);
        $editAddition->addSubmenu('Presenter.Admin.Addition.Edit.H1', ':Admin:Addition:edit')
            ->setIcon('fa fa-pencil')
            ->setLinkParamNeeded(AdditionEntity::class);
        $options = $editAddition->addSubmenu('Entity.Plural.Option', ':Admin:Option:default')
            ->setIcon('fa fa-list-ul')
            ->setLinkParamNeeded(AdditionEntity::class);
        $options->addSubmenu('Presenter.Admin.Option.Add.H1', ':Admin:Option:add')
            ->setIcon('fa fa-plus-circle')
            ->setLinkParamNeeded(AdditionEntity::class);
        $options->addSubmenu('Presenter.Admin.Option.Edit.H1', ':Admin:Option:edit')
            ->setIcon('fa fa-pencil')
            ->setLinkParamNeeded(OptionEntity::class);


        $appsEvent = $events->addSubmenu('Entity.Singular.Event', ':Admin:Application:default')
            ->setIcon('fa fa-calendar-o')
            ->setCurentable(false)
            ->setLinkParamNeeded(EventEntity::class);
        $apps = $appsEvent->addSubmenu('Entity.Plural.Application', ':Admin:Application:default')
            ->setIcon('fa fa-ticket')
            ->setLinkParamNeeded(EventEntity::class);
        $apps->addSubmenu('Presenter.Admin.Application.Reserve.H1', ':Admin:Application:reserve')
            ->setIcon('fa fa-address-book-o')
            ->setLinkParamNeeded(EventEntity::class);
        $apps->addSubmenu('Presenter.Admin.Cart.Edit.H1', ':Admin:Cart:edit')
            ->setIcon('fa fa-pencil')
            ->setLinkParamNeeded(CartEntity::class);
        $apps->addSubmenu('Entity.Singular.Cart', ':Admin:Cart:default')
            ->setIcon('fa fa-shopping-cart')
            ->setLinkParamNeeded(CartEntity::class);
        $appsEvent->addSubmenu('Attribute.Event.Occupancy', ':Admin:Application:occupancy')
            ->setIcon('fa fa-adjust')
            ->setLinkParamNeeded(EventEntity::class);
        $appsEvent->addSubmenu('Entity.Plural.Substitute', ':Admin:Substitute:default')
            ->setIcon('fa fa-retweet')
            ->setLinkParamNeeded(EventEntity::class);
        $earlies = $appsEvent->addSubmenu('Entity.Plural.Early', ':Admin:Early:default')
            ->setIcon('fa fa-star')
            ->setLinkParamNeeded(EventEntity::class);
        $earlies->addSubmenu('Presenter.Admin.Early.Add.H1', ':Admin:Early:add')
            ->setIcon('fa fa-plus-circle')
            ->setLinkParamNeeded(EventEntity::class);
        $earlies->addSubmenu('Presenter.Admin.Early.Edit.H1', ':Admin:Early:edit')
            ->setIcon('fa fa-pencil')
            ->setLinkParamNeeded(EarlyEntity::class);
        $waves = $appsEvent->addSubmenu('Entity.Plural.EarlyWave', ':Admin:EarlyWave:default')
            ->setIcon('fa fa-star-o')
            ->setLinkParamNeeded(EventEntity::class);
        $waves->addSubmenu('Presenter.Admin.EarlyWave.Add.H1', ':Admin:EarlyWave:add')
            ->setIcon('fa fa-plus-circle')
            ->setLinkParamNeeded(EventEntity::class);
        $waves->addSubmenu('Presenter.Admin.EarlyWave.Edit.H1', ':Admin:EarlyWave:edit')
            ->setIcon('fa fa-pencil')
            ->setLinkParamNeeded(EarlyWaveEntity::class);

        return $menu;
    }
}
