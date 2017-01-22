<?php

namespace App\AdminModule\Presenters;


use App\AdminModule\Controls\Grids\IApplicationsGridWrapperFactory;

class ApplicationPresenter extends BasePresenter {

    /**
     * @var  IApplicationsGridWrapperFactory
     * @inject
     */
    public $applicationsGridWrapperFactory;

    public function renderDefault() {

    }

    protected function createComponentApplicationsGrid(){
        return $this->applicationsGridWrapperFactory->create();
    }
}
