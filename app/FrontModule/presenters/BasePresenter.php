<?php

namespace App\FrontModule\Presenters;

use App\Model;


/**
 * Base presenter for front application presenters.
 */
abstract class BasePresenter extends \App\Presenters\BasePresenter {

    /** @persistent null|string Určuje jazykovou verzi webu. */
    public $locale;

}
