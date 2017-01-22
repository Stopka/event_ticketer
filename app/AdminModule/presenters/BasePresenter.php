<?php

namespace App\AdminModule\Presenters;


/**
 * Base presenter for admin application presenters.
 */
abstract class BasePresenter extends \App\Presenters\BasePresenter {

    public function startup() {
        parent::startup();
        $this->checkUser();
    }

    /**
     * Provede kontrolu, zda je uživatel přihlášen
     * pokud není přesměruje ho na přihlášení
     */
    protected function checkUser() {
        if (!$this->getUser()->isLoggedIn()) {
            $this->flashMessage('Pro pokračování se musíte se přihlásit', 'warning');
            $backlink = $this->storeRequest();
            $this->redirect('Sign:in', array('backlink' => $backlink));
        }
    }
}