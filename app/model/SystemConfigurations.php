<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 20.1.17
 * Time: 22:09
 */

namespace App\Model;


use Nette\DI\Container;
use Nette\Object;

class SystemConfigurations extends Object {

    /** @var $container Container */
    private $container;

    public function __construct(Container $container) {
        $this->container = $container;
    }

    /**
     * @param string|null $section
     * @return mixed
     */
    public function getParameters($section = null){
        $params = $this->container->getParameters();
        if(!$section){
            return $params;
        }
        if(!isset($params[$section])){
            return null;
        }
        return $params[$section];
    }

}