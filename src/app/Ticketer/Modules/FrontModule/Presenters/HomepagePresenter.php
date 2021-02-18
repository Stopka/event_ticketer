<?php

declare(strict_types=1);

namespace Ticketer\Modules\FrontModule\Presenters;

use Ticketer\Model\Database\Daos\ApplicationDao;
use Ticketer\Model\Database\Daos\EventDao;
use Ticketer\Model\Database\Entities\EventEntity;
use Ticketer\Modules\FrontModule\Templates\HomepageTemplate;

/**
 * @method HomepageTemplate getTemplate()
 */
class HomepagePresenter extends BasePresenter
{

    public EventDao $eventDao;

    public ApplicationDao $applicationDao;

    /**
     * HomepagePresenter constructor.
     * @param BasePresenterDependencies $dependencies
     * @param EventDao $additionDao
     * @param ApplicationDao $applicationDao
     */
    public function __construct(
        BasePresenterDependencies $dependencies,
        EventDao $additionDao,
        ApplicationDao $applicationDao
    ) {
        parent::__construct($dependencies);
        $this->eventDao = $additionDao;
        $this->applicationDao = $applicationDao;
    }

    public function renderDefault(): void
    {
        $events = $this->eventDao->getPublicAvailibleEvents();
        $futureEvents = $this->eventDao->getPublicFutureEvents();
        $template = $this->getTemplate();
        $template->events = $events;
        $template->futureEvents = $futureEvents;
    }

    /**
     * @param EventEntity $event
     * @return integer
     */
    public function countApplications(EventEntity $event): int
    {
        return $this->applicationDao->countIssuedApplications($event);
    }
}
