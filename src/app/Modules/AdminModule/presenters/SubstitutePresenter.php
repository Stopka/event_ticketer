<?php

namespace App\AdminModule\Presenters;


use App\AdminModule\Controls\Grids\ISubstitutesGridWrapperFactory;
use App\AdminModule\Controls\Grids\SubstitutesGridWrapper;
use App\Model\Persistence\Dao\EventDao;
use App\Model\Persistence\Entity\EventEntity;

class SubstitutePresenter extends BasePresenter {

    /** @var  ISubstitutesGridWrapperFactory */
    public $substitutesGridWrapperFactory;

    /** @var EventDao */
    public $eventDao;

    /**
     * SubstitutePresenter constructor.
     * @param ISubstitutesGridWrapperFactory $substitutesGridWrapperFactory
     * @param EventDao $additionDao
     */
    public function __construct(ISubstitutesGridWrapperFactory $substitutesGridWrapperFactory, EventDao $additionDao) {
        parent::__construct();
        $this->substitutesGridWrapperFactory = $substitutesGridWrapperFactory;
        $this->eventDao = $additionDao;
    }

    /**
     * @param int $id
     * @throws \Nette\Application\AbortException
     */
    public function actionDefault(int $id) {
        $event = $this->eventDao->getEvent($id);
        if(!$event){
            $this->flashTranslatedMessage("Error.Event.NotFound",self::FLASH_MESSAGE_TYPE_ERROR);
            $this->redirect('Homepage:');
        }
        $this->getMenu()->setLinkParam(EventEntity::class, $event);
        /** @var SubstitutesGridWrapper $substitutesGridWrapper */
        $substitutesGridWrapper = $this->getComponent('substitutesGrid');
        $substitutesGridWrapper->setEvent($event);
        $this->template->event = $event;
    }

    protected function createComponentSubstitutesGrid(){
        return $this->substitutesGridWrapperFactory->create();
    }
}
