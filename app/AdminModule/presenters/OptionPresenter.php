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

    /**
     * @param int $id
     * @throws \Nette\Application\AbortException
     */
    public function actionDefault(int $id) {
        $addition = $this->additionDao->getAddition($id);
        if(!$addition){
            $this->flashTranslatedMessage('Addition.NotFound',self::FLASH_MESSAGE_TYPE_ERROR);
            $this->redirect("Homepage:");
        }
        /** @var OptionsGridWrapper $optionsGrid */
        $optionsGrid = $this->getComponent('optionsGrid');
        $optionsGrid->setAdditionEntity($addition);
        $this->template->addition = $addition;
    }

    /**
     * @param int $id
     * @throws \Nette\Application\AbortException
     */
    public function actionAdd(int $id) {
        $addition = $this->additionDao->getAddition($id);
        if(!$addition){
            $this->flashTranslatedMessage('Addition.NotFound',self::FLASH_MESSAGE_TYPE_ERROR);
            $this->redirect("Homepage:");
        }
        /** @var OptionFormWrapper $optionForm */
        $optionForm = $this->getComponent('optionForm');
        $optionForm->setAdditionEntity($addition);
        $this->template->option = null;
        $this->template->addition = $addition;
    }

    /**
     * @param int $id
     * @throws \Nette\Application\AbortException
     */
    public function actionEdit(int $id) {
        $option = $this->optionDao->getOption($id);
        if(!$option){
            $this->flashTranslatedMessage('Addition.NotFound',self::FLASH_MESSAGE_TYPE_ERROR);
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
