<?php

namespace App\AdminModule\Presenters;


class HomepagePresenter extends BasePresenter {

    /**
     * @throws \Nette\Application\AbortException
     */
    public function renderDefault() {
        $this->redirect("Event:default");
    }

}
