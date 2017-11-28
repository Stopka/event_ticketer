<?php

namespace App\AdminModule\Presenters;


class HomepagePresenter extends BasePresenter {

    public function renderDefault() {
        $this->redirect("Event:default");
    }

}
