<?php

namespace App;

use Nette;
use Nette\Application\Routers\RouteList;
use Nette\Application\Routers\Route;


class RouterFactory {
    use Nette\StaticClass;

    /**
     * @return Nette\Application\IRouter
     */
    public static function createRouter() {
        $router = new RouteList;
        $router[] = new Route("admin/<presenter>/<action>[/<id>]", [
            'module' => 'Admin',
            'presenter' => 'Homepage',
            'action' => 'default',
            'locale' => 'cs'
        ]);
        $router[] = new Route("<presenter>/<action>[/<id>]", [
            'module' => 'Front',
            'presenter' => 'Homepage',
            'action' => 'default',
            'locale' => 'cs'
        ]);
        return $router;
    }

}
