<?php

namespace App\FrontModule\Presenters;

use App\Model\DateFormatter;
use App\Model\Persistence\Dao\ApplicationDao;
use App\Model\Persistence\Dao\EventDao;
use App\Model\Persistence\Entity\EventEntity;


class HomepagePresenter extends BasePresenter {

    /** @var EventDao */
    public $eventDao;

    /** @var ApplicationDao */
    public $applicationDao;

    /** @var DateFormatter */
    public $dateFormatter;

    /**
     * HomepagePresenter constructor.
     * @param EventDao $additionDao
     * @param ApplicationDao $applicationDao
     * @param DateFormatter $dateFormatter
     */
    public function __construct(EventDao $additionDao, ApplicationDao $applicationDao, DateFormatter $dateFormatter) {
        parent::__construct();
        $this->eventDao = $additionDao;
        $this->applicationDao = $applicationDao;
        $this->dateFormatter = $dateFormatter;
    }

    /**
     * @throws \Nette\Application\AbortException
     */
    public function renderDefault() {
        $events = $this->eventDao->getPublicAvailibleEvents();
        $future_events = $this->eventDao->getPublicFutureEvents();
        $this->template->events = $events;
        $this->template->future_events = $future_events;
    }

    /**
     * @param $event
     * @return integer
     */
    public function countApplications(EventEntity $event){
        return $this->applicationDao->countIssuedApplications($event);
    }

    /**
     * @param \DateTime $dateTime
     * @return string
     */
    public function formatDate(\DateTime $dateTime): string {
        return $this->dateFormatter->getDateString($dateTime);
    }

}
