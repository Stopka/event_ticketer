<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 22.1.18
 * Time: 11:02
 */

namespace App\AdminModule\Controls\Menus;


use App\Controls\Menus\Menu;
use App\Model\Persistence\Entity\AdditionEntity;
use App\Model\Persistence\Entity\CartEntity;
use App\Model\Persistence\Entity\CurrencyEntity;
use App\Model\Persistence\Entity\EarlyEntity;
use App\Model\Persistence\Entity\EarlyWaveEntity;
use App\Model\Persistence\Entity\EventEntity;
use App\Model\Persistence\Entity\OptionEntity;

class MenuFactory extends \App\Controls\Menus\MenuFactory {

    public function create(): Menu {
        $menu = parent::create();
        $admin = $menu->add('Presenter.Admin.Homepage.Default.H1', ':Admin:Homepage:default')
            ->setIcon('fa fa-user-secret');
        $setting = $admin->add('Presenter.AdminLayout.Setting.H1', ':Admin:Currency:default')
            ->setCurentable(false)
            ->setIcon('fa fa-wrench');
        $currency = $setting->add('Entity.Plural.Currency', ':Admin:Currency:default')
            ->setIcon('fa fa-btc');
        $currency->add('Presenter.Admin.Currency.Edit.H1', ':Admin:Currency:edit')
            ->setLinkParamNeeded(CurrencyEntity::class)
            ->setIcon('fa fa-pencil');

        $events = $admin->add('Entity.Plural.Event', ':Admin:Event:default')
            ->setIcon('fa fa-calendar');
        //$events->add('Presenter.Event.Add.H1', ':Admin:Event:add');
        $editEvent = $events->add('Form.Action.Edit', ':Admin:Event:edit')
            ->setCurentable(false)
            ->setLinkParamNeeded(EventEntity::class)
            ->setIcon('fa fa-pencil');
        $editEvent->add('Presenter.Admin.Event.Edit.H1', ':Admin:Event:edit')
            ->setIcon('fa fa-pencil')
            ->setLinkParamNeeded(EventEntity::class);
        $additions = $editEvent->add('Entity.Plural.Addition', ':Admin:Addition:default')
            ->setIcon('fa fa-archive')
            ->setLinkParamNeeded(EventEntity::class);
        $addAddition = $additions->add('Presenter.Admin.Addition.Add.H1', ':Admin:Addition:add')
            ->setIcon('fa fa-plus-circle')
            ->setLinkParamNeeded(EventEntity::class);
        $addAddition->add('Entity.Plural.Option', ':Admin:Addition:add')
            ->setIcon('fa fa-list-ul')
            ->setLinkParamNeeded('...');
        $editAddition = $additions->add('Form.Action.Edit', ':Admin:Addition:edit')
            ->setIcon('fa fa-pencil')
            ->setCurentable(false)
            ->setLinkParamNeeded(AdditionEntity::class);
        $editAddition->add('Presenter.Admin.Addition.Edit.H1', ':Admin:Addition:edit')
            ->setIcon('fa fa-pencil')
            ->setLinkParamNeeded(AdditionEntity::class);
        $options = $editAddition->add('Entity.Plural.Option', ':Admin:Option:default')
            ->setIcon('fa fa-list-ul')
            ->setLinkParamNeeded(AdditionEntity::class);
        $options->add('Presenter.Admin.Option.Add.H1', ':Admin:Option:add')
            ->setIcon('fa fa-plus-circle')
            ->setLinkParamNeeded(AdditionEntity::class);
        $options->add('Presenter.Admin.Option.Edit.H1', ':Admin:Option:edit')
            ->setIcon('fa fa-pencil')
            ->setLinkParamNeeded(OptionEntity::class);


        $appsEvent = $events->add('Entity.Singular.Event', ':Admin:Application:default')
            ->setIcon('fa fa-calendar-o')
            ->setCurentable(false)
            ->setLinkParamNeeded(EventEntity::class);
        $apps = $appsEvent->add('Entity.Plural.Application', ':Admin:Application:default')
            ->setIcon('fa fa-ticket')
            ->setLinkParamNeeded(EventEntity::class);
        $apps->add('Presenter.Admin.Application.Reserve.H1', ':Admin:Application:reserve')
            ->setIcon('fa fa-address-book-o')
            ->setLinkParamNeeded(EventEntity::class);
        $apps->add('Presenter.Admin.Cart.Edit.H1', ':Admin:Cart:edit')
            ->setIcon('fa fa-pencil')
            ->setLinkParamNeeded(CartEntity::class);
        $apps->add('Entity.Singular.Cart', ':Admin:Cart:default')
            ->setIcon('fa fa-shopping-cart')
            ->setLinkParamNeeded(CartEntity::class);
        $appsEvent->add('Entity.Plural.Substitute', ':Admin:Substitute:default')
            ->setIcon('fa fa-retweet')
            ->setLinkParamNeeded(EventEntity::class);
        $earlies = $appsEvent->add('Entity.Plural.Early', ':Admin:Early:default')
            ->setIcon('fa fa-star')
            ->setLinkParamNeeded(EventEntity::class);
        $earlies->add('Presenter.Admin.Early.Add.H1', ':Admin:Early:add')
            ->setIcon('fa fa-plus-circle')
            ->setLinkParamNeeded(EventEntity::class);
        $earlies->add('Presenter.Admin.Early.Edit.H1', ':Admin:Early:edit')
            ->setIcon('fa fa-pencil')
            ->setLinkParamNeeded(EarlyEntity::class);
        $waves = $appsEvent->add('Entity.Plural.EarlyWave', ':Admin:EarlyWave:default')
            ->setIcon('fa fa-star-o')
            ->setLinkParamNeeded(EventEntity::class);
        $waves->add('Presenter.Admin.EarlyWave.Add.H1', ':Admin:EarlyWave:add')
            ->setIcon('fa fa-plus-circle')
            ->setLinkParamNeeded(EventEntity::class);
        $waves->add('Presenter.Admin.EarlyWave.Edit.H1', ':Admin:EarlyWave:edit')
            ->setIcon('fa fa-pencil')
            ->setLinkParamNeeded(EarlyWaveEntity::class);

        return $menu;
    }
}