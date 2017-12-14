<?php

namespace App\AdminModule\Presenters;


use App\AdminModule\Controls\Forms\EarlyWaveFormWrapper;
use App\AdminModule\Controls\Forms\IEarlyWaveFormWrapperFactory;
use App\AdminModule\Controls\Grids\EarliesGridWrapper;
use App\Model\Persistence\Dao\EarlyWaveDao;

class EarlyWavePresenter extends BasePresenter {

    /** @var IEarlyWaveFormWrapperFactory */
    private $earlyWaveFormWrapperFactory;

    /** @var EarlyWaveDao */
    private $earlyWaveDao;

    public function __construct(IEarlyWaveFormWrapperFactory $earlyWaveFormWrapperFactory, EarlyWaveDao $earlyWaveDao) {
        parent::__construct();
        $this->earlyWaveDao =  $earlyWaveDao;
        $this->earlyWaveFormWrapperFactory = $earlyWaveFormWrapperFactory;
    }

    public function actionEdit($id = null){
        $early = $this->earlyWaveDao->getEarlyWave($id);
        if(!$early){
            $this->flashMessage('Error.EarlyWave.NotFound',self::FLASH_MESSAGE_TYPE_ERROR);
            $this->redirect("Homepage:");
        }
        /** @var EarlyWaveFormWrapper $earlyWaveForm */
        $earlyWaveForm = $this->getComponent('earlyWaveForm');
        $earlyWaveForm->setEarlyWaveEntity($early);
        $template = $this->getTemplate();
        $template->earlyWave = $early;
        $template->event = $earlyWaveForm->getEventEntity();
    }

    /**
     * @return EarliesGridWrapper
     */
    protected function createComponentEarlyWavesGrid(){
        return $this->earliesGridWrapperFactory->create();
    }

    /**
     * @return EarlyWaveFormWrapper
     */
    protected function createComponentEarlyForm(){
        return $this->earlyWaveFormWrapperFactory->create();
    }
}
