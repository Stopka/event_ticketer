<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 28.11.17
 * Time: 12:35
 */

namespace App\FrontModule\Controls;

use App\Model\Exception\NotReadyException;
use App\Model\OccupancyIcons;
use App\Model\Persistence\Dao\ApplicationDao;
use App\Model\Persistence\Dao\OptionDao;
use App\Model\Persistence\Entity\EventEntity;
use App\Model\Persistence\Entity\OptionEntity;
use Nette\Application\UI\Control;

class OccupancyControl extends Control {

    /** @var  ApplicationDao */
    private $applicationDao;

    /** @var  OptionDao */
    private $optionDao;

    /** @var  OccupancyIcons */
    private  $occupancyIcons;

    /** @var  EventEntity */
    private $event;

    public function __construct(ApplicationDao $applicationDao, OptionDao $optionDao, OccupancyIcons $occupancyIcons) {
        parent::__construct();
        $this->applicationDao = $applicationDao;
        $this->optionDao = $optionDao;
        $this->occupancyIcons = $occupancyIcons;
    }


    public function setEvent(EventEntity $event) {
        $this->event = $event;
    }

    public function render() {
        $event = $this->event;
        if(!$event){
            throw new NotReadyException("No event to render");
        }
        $template = $this->getTemplate();
        $template->setFile(__DIR__ . '/OccupancyControl.latte');
        $template->event = $event;
        $template->event_issued = $this->applicationDao->countIssuedApplications($event);
        $template->event_reserved = $this->applicationDao->countReservedApplications($event);
        $template->options = $this->optionDao->getOptionsWithLimitedCapacity($event);
        $template->icons = $this->occupancyIcons;
        $template->render();
    }

    /**
     * @param OptionEntity $option
     * @return int
     */
    public function countOptionsReserved(OptionEntity $option): int {
        return $this->applicationDao->countReservedApplicationsWithOption($option);
    }
}