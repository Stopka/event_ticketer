<?php

declare(strict_types=1);

namespace Ticketer\Modules\FrontModule\Presenters;

use Nette\Application\AbortException;
use Ticketer\Model\DateFormatter;
use Ticketer\Model\Database\Daos\ApplicationDao;
use Ticketer\Model\Database\Daos\EventDao;
use Ticketer\Model\Database\Entities\EventEntity;

class HomepagePresenter extends BasePresenter
{

    public EventDao $eventDao;

    public ApplicationDao $applicationDao;

    public DateFormatter $dateFormatter;

    /**
     * HomepagePresenter constructor.
     * @param BasePresenterDependencies $dependencies
     * @param EventDao $additionDao
     * @param ApplicationDao $applicationDao
     * @param DateFormatter $dateFormatter
     */
    public function __construct(
        BasePresenterDependencies $dependencies,
        EventDao $additionDao,
        ApplicationDao $applicationDao,
        DateFormatter $dateFormatter
    ) {
        parent::__construct($dependencies);
        $this->eventDao = $additionDao;
        $this->applicationDao = $applicationDao;
        $this->dateFormatter = $dateFormatter;
    }

    /**
     * @throws AbortException
     */
    public function renderDefault(): void
    {
        $events = $this->eventDao->getPublicAvailibleEvents();
        $future_events = $this->eventDao->getPublicFutureEvents();
        $template = $this->getTemplate();
        $template->events = $events;
        $template->future_events = $future_events;
    }

    /**
     * @param EventEntity $event
     * @return integer
     */
    public function countApplications(EventEntity $event): int
    {
        return $this->applicationDao->countIssuedApplications($event);
    }

    /**
     * @param \DateTime $dateTime
     * @return string
     */
    public function formatDate(\DateTime $dateTime): string
    {
        return $this->dateFormatter->getDateString($dateTime);
    }
}
