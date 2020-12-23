<?php

declare(strict_types=1);

namespace Ticketer\Routers;

use Nette\Application\Routers\Route;
use Nette\Application\Routers\RouteList;
use Nette\Routing\Router;

class RouterFactory
{

    public function createRouter(): Router
    {
        return (new RouteList())
            ->addRoute(
                'admin/<presenter>/<action>[/<id>]',
                [
                    'module' => 'Admin',
                    'presenter' => 'Homepage',
                    'action' => 'default',
                    'locale' => 'cs',
                ]
            )
            ->addRoute(
                'api/<presenter>/<action>[/<id>]',
                [
                    'module' => 'Api',
                    'presenter' => 'Homepage',
                    'action' => 'default',
                    'locale' => 'cs',
                ]
            )
            ->addRoute(
                '<presenter>/<action>[/<id>]',
                [
                    'module' => 'Front',
                    'presenter' => 'Homepage',
                    'action' => 'default',
                    'locale' => 'cs',
                ]
            );
    }
}
