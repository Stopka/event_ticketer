<?php

declare(strict_types=1);

namespace Ticketer\Modules\FrontModule\Presenters;

use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Ticketer\Model\Database\Daos\EventDao;
use Ticketer\Modules\FrontModule\Controls\IOccupancyControlFactory;
use Ticketer\Modules\FrontModule\Controls\OccupancyControl;
use Ticketer\Modules\FrontModule\Templates\OccupancyTemplate;

/**
 * @method OccupancyTemplate getTemplate()
 */
class OccupancyPresenter extends BasePresenter
{
    public EventDao $eventDao;
    public IOccupancyControlFactory $occupancyControlFactory;

    public function __construct(
        IOccupancyControlFactory $occupancyControlFactory,
        EventDao $eventDao,
        BasePresenterDependencies $dependencies
    ) {
        parent::__construct($dependencies);
        $this->eventDao = $eventDao;
        $this->occupancyControlFactory = $occupancyControlFactory;
    }


    /**
     * @return OccupancyControl
     */
    protected function createComponentOccupancy(): OccupancyControl
    {
        return $this->occupancyControlFactory->create();
    }

    /**
     * @param string|null $id
     * @param bool $showHeaders
     * @throws AbortException
     * @throws BadRequestException
     */
    public function renderOccupancy(?string $id = null, bool $showHeaders = true): void
    {
        if (null === $id) {
            $events = $this->eventDao->getPublicAvailibleEvents();
            if (count($events) > 0) {
                $this->redirect('this', $events[0]->getId()->toString());
            }
            $events = $this->eventDao->getPublicFutureEvents();
            if (count($events) > 0) {
                $this->redirect('this', $events[0]->getId()->toString());
            }
            $event = null;
        } else {
            $uuid = $this->deserializeUuid($id);
            $event = $this->eventDao->getEvent($uuid);
        }
        if (null === $event) {
            return;
        }
        /** @var OccupancyControl $occupancy */
        $occupancy = $this->getComponent('occupancy');
        $occupancy->setEvent($event);

        $template = $this->getTemplate();
        $template->event = $event;
        $template->showHeaders = $showHeaders;
    }
}
