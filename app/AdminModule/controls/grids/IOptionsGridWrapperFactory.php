<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 29.11.17
 * Time: 0:04
 */

namespace App\AdminModule\Controls\Grids;


interface IOptionsGridWrapperFactory {
    /**
     * @return OptionsGridWrapper
     */
    public function create():OptionsGridWrapper;
}