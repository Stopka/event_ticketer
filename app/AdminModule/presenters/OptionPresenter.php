<?php

namespace App\AdminModule\Presenters;


use App\AdminModule\Controls\Forms\IOptionFormWrapperFactory;
use App\AdminModule\Controls\Forms\OptionFormWrapper;
use App\AdminModule\Controls\Grids\IOptionsGridWrapperFactory;
use App\AdminModule\Controls\Grids\OptionsGridWrapper;
use App\Model\Persistence\Dao\AdditionDao;
use App\Model\Persistence\Dao\OptionDao;

class OptionPresenter extends BasePresenter {

    /** @var  OptionDao */
    private $optionDao;

    /** @var  AdditionDao */
    private $additionDao;

    /** @var  IOptionsGridWrapperFactory */
    private $optionsGridWrapperFactory;

    /** @var IOptionFormWrapperFactory  */
    private $optionFormWrapperFactory;

    public function __construct(OptionDao $optionDao, AdditionDao $additionDao, IOptionsGridWrapperFactory $optionsGridWrapperFactory, IOptionFormWrapperFactory $optionFormWrapperFactory) {
        parent::__construct();
        $this->optionDao = $optionDao;
        $this->additionDao = $additionDao;
        $this->optionsGridWrapperFactory = $optionsGridWrapperFactory;
        $this->optionFormWrapperFactory = $optionFormWrapperFactory;
    }

    public function actionDefault($id = null) {
        $addition = $this->additionDao->getAddition($id);
        if(!$addition){
            $this->flashMessage('Přídavek nenalezen','error');
            $this->redirect("Homepage:");
        }
        /** @var OptionsGridWrapper $optionsGrid */
        $optionsGrid = $this->getComponent('optionsGrid');
        $optionsGrid->setAdditionEntity($addition);
        $this->template->addition = $addition;
    }

    public function actionAdd($id){
        $addition = $this->additionDao->getAddition($id);
        if(!$addition){
            $this->flashMessage('Přídavek nenalezen','error');
            $this->redirect("Homepage:");
        }
        /** @var OptionFormWrapper $optionForm */
        $optionForm = $this->getComponent('optionForm');
        $optionForm->setAdditionEntity($addition);
        $this->template->option = null;
        $this->template->addition = $addition;
    }

    public function actionEdit($id = null){
        $option = $this->optionDao->getOption($id);
        if(!$option){
            $this->flashMessage('Možnost nenalezena','error');
            $this->redirect("Homepage:");
        }
        /** @var OptionFormWrapper $optionForm */
        $optionForm = $this->getComponent('optionForm');
        $optionForm->setOptionEntity($option);
        $this->template->option = $option;
        $this->template->addition = $option->getAddition();
    }

    /**
     * @return OptionsGridWrapper
     */
    public function createComponentOptionsGrid(){
        return $this->optionsGridWrapperFactory->create();
    }

    /**
     * @return OptionFormWrapper
     */
    public function createComponentOptionForm(){
        return $this->optionFormWrapperFactory->create();
    }
}
