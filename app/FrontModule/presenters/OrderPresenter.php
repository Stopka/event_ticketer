<?php

namespace App\FrontModule\Presenters;

use App\Model;


class OrderPresenter extends BasePresenter {

    public function renderDefault() {
        $this->template->anyVariable = 'any value';
    }

}
