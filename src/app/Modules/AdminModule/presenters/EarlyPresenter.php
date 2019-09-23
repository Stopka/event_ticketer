<?php

namespace App\AdminModule\Presenters;


use App\AdminModule\Controls\Forms\EarlyFormWrapper;
use App\AdminModule\Controls\Forms\IEarlyFormWrapperFactory;
use App\AdminModule\Controls\Grids\EarliesGridWrapper;
use App\AdminModule\Controls\Grids\IEarliesGridWrapperFactory;
use App\Model\Persistence\Dao\EarlyDao;
use App\Model\Persistence\Dao\EventDao;
use App\Model\Persistence\Entity\EarlyEntity;
use App\Model\Persistence\Entity\EventEntity;

class EarlyPresenter extends BasePresenter {

    /** @var  EventDao */
    private $eventDao;

    /** @var  IEarliesGridWrapperFactory */
    private $earliesGridWrapperFactory;

    /** @var IEarlyFormWrapperFactory */
    private $earlyFormWrapperFactory;

    /** @var EarlyDao */
    private $earlyDao;

    public function __construct(EventDao $eventDao, IEarliesGridWrapperFactory $earliesGridWrapperFactory, IEarlyFormWrapperFactory $earlyFormWrapperFactory, EarlyDao $earlyDao) {
        parent::__construct();
        $this->eventDao = $eventDao;
        $this->earlyDao =  $earlyDao;
        $this->earliesGridWrapperFactory = $earliesGridWrapperFactory;
        $this->earlyFormWrapperFactory = $earlyFormWrapperFactory;
    }

    /**
     * @param int $id
     * @throws \Nette\Application\AbortException
     */
    public function actionDefault(int $id) {
        $event = $this->eventDao->getEvent($id);
        if(!$event){
            $this->redirect("Event:edit");
        }
        $this->getMenu()->setLinkParam(EventEntity::class, $event);
        /** @var EarliesGridWrapper $earliesGrid */
        $earliesGrid = $this->getComponent('earliesGrid');
        $earliesGrid->setEventEntity($event);
        $this->template->event = $event;
    }

    /**
     * @param int $id
     * @throws \Nette\Application\AbortException
     */
    public function actionAdd(int $id) {
        $event = $this->eventDao->getEvent($id);
        if(!$event){
            $this->flashTranslatedMessage('Error.Event.NotFound',self::FLASH_MESSAGE_TYPE_ERROR);
            $this->redirect("Homepage:");
        }
        $this->getMenu()->setLinkParam(EventEntity::class, $event);
        /** @var EarlyFormWrapper $earlyForm */
        $earlyForm = $this->getComponent('earlyForm');
        $earlyForm->setEventEntity($event);
        $this->template->early = null;
        $this->template->event = $event;
    }

    /**
     * @param int $id
     * @throws \Nette\Application\AbortException
     */
    public function actionEdit(int $id) {
        $early = $this->earlyDao->getEarly($id);
        if(!$early){
            $this->flashTranslatedMessage('Error.Addition.NotFound',self::FLASH_MESSAGE_TYPE_ERROR);
            $this->redirect("Homepage:");
        }
        $this->getMenu()->setLinkParam(EarlyEntity::class, $early);
        /** @var EarlyFormWrapper $earlyForm */
        $earlyForm = $this->getComponent('earlyForm');
        $earlyForm->setEarlyEntity($early);
        $this->template->early = $early;
        $this->template->event = $earlyForm->getEventEntity();
    }

    /**
     * @return EarliesGridWrapper
     */
    protected function createComponentEarliesGrid(){
        return $this->earliesGridWrapperFactory->create();
    }

    /**
     * @return EarlyFormWrapper
     */
    protected function createComponentEarlyForm(){
        return $this->earlyFormWrapperFactory->create();
    }
}
