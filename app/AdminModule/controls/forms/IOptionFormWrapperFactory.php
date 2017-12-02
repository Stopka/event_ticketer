<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 29.11.17
 * Time: 0:35
 */

namespace App\AdminModule\Controls\Forms;


interface IOptionFormWrapperFactory {
    /**
     * @return OptionFormWrapper
     */
    public function create(): OptionFormWrapper;
}