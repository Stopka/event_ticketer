<?php

namespace App\AdminModule\Presenters;


use App\AdminModule\Controls\Forms\EarlyWaveFormWrapper;
use App\AdminModule\Controls\Forms\IEarlyWaveFormWrapperFactory;
use App\AdminModule\Controls\Grids\EarlyWavesGridWrapper;
use App\AdminModule\Controls\Grids\IEarlyWavesGridWrapperFactory;
use App\Model\Persistence\Dao\EarlyWaveDao;
use App\Model\Persistence\Dao\EventDao;
use App\Model\Persistence\Entity\EarlyWaveEntity;
use App\Model\Persistence\Entity\EventEntity;

class EarlyWavePresenter extends BasePresenter {

    /** @var IEarlyWaveFormWrapperFactory */
    private $earlyWaveFormWrapperFactory;

    /** @var IEarlyWavesGridWrapperFactory */
    private $earlyWavesGridWrapperFactory;

    /** @var EarlyWaveDao */
    private $earlyWaveDao;

    /** @var EventDao */
    private $eventDao;

    public function __construct(IEarlyWaveFormWrapperFactory $earlyWaveFormWrapperFactory, IEarlyWavesGridWrapperFactory $earlyWavesGridWrapperFactory, EarlyWaveDao $earlyWaveDao, EventDao $eventDao) {
        parent::__construct();
        $this->earlyWaveDao =  $earlyWaveDao;
        $this->earlyWaveFormWrapperFactory = $earlyWaveFormWrapperFactory;
        $this->earlyWavesGridWrapperFactory = $earlyWavesGridWrapperFactory;
        $this->eventDao = $eventDao;
    }

    /**
     * @param int $id
     * @throws \Nette\Application\AbortException
     */
    public function actionDefault(int $id) {
        $event = $this->eventDao->getEvent($id);
        if(!$event){
            $this->flashTranslatedMessage('Error.Event.NotFound', self::FLASH_MESSAGE_TYPE_ERROR);
            $this->redirect('Homepage:');
        }
        $this->getMenu()->setLinkParam(EventEntity::class, $event);
        /** @var EarlyWavesGridWrapper $grid */
        $grid = $this->getComponent('earlyWavesGrid');
        $grid->setEventEntity($event);
        $template = $this->getTemplate();
        $template->event = $event;
    }

    /**
     * @param int $id
     * @throws \Nette\Application\AbortException
     */
    public function actionAdd(int $id) {
        $event = $this->eventDao->getEvent($id);
        if(!$event){
            $this->flashTranslatedMessage('Error.Event.NotFound', self::FLASH_MESSAGE_TYPE_ERROR);
            $this->redirect('Homepage:');
        }
        $this->getMenu()->setLinkParam(EventEntity::class, $event);
        /** @var EarlyWaveFormWrapper $earlyWaveForm */
        $earlyWaveForm = $this->getComponent('earlyWaveForm');
        $earlyWaveForm->setEventEntity($event);
        $template = $this->getTemplate();
        $template->earlyWave = null;
        $template->event = $earlyWaveForm->getEventEntity();
    }

    /**
     * @param int $id
     * @throws \Nette\Application\AbortException
     */
    public function actionEdit(int $id) {
        $earlyWave = $this->earlyWaveDao->getEarlyWave($id);
        if(!$earlyWave){
            $this->flashTranslatedMessage('Error.EarlyWave.NotFound',self::FLASH_MESSAGE_TYPE_ERROR);
            $this->redirect("Homepage:");
        }
        $this->getMenu()->setLinkParam(EarlyWaveEntity::class, $earlyWave);
        /** @var EarlyWaveFormWrapper $earlyWaveForm */
        $earlyWaveForm = $this->getComponent('earlyWaveForm');
        $earlyWaveForm->setEarlyWaveEntity($earlyWave);
        $template = $this->getTemplate();
        $template->earlyWave = $earlyWave;
        $template->event = $earlyWaveForm->getEventEntity();
    }

    /**
     * @return EarlyWavesGridWrapper
     */
    protected function createComponentEarlyWavesGrid(){
        return $this->earlyWavesGridWrapperFactory->create();
    }

    /**
     * @return EarlyWaveFormWrapper
     */
    protected function createComponentEarlyWaveForm(){
        return $this->earlyWaveFormWrapperFactory->create();
    }
}
