<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 28.11.17
 * Time: 12:35
 */

namespace App\FrontModule\Controls;

use App\Controls\TInjectTranslator;
use App\Model\Exception\NotReadyException;
use App\Model\OccupancyIcons;
use App\Model\Persistence\Attribute\TOccupancyIconAttribute;
use App\Model\Persistence\Dao\ApplicationDao;
use App\Model\Persistence\Dao\OptionDao;
use App\Model\Persistence\Entity\EventEntity;
use App\Model\Persistence\Entity\OptionEntity;
use Kdyby\Translation\ITranslator;
use Nette\Application\UI\Control;
use Nette\Utils\Html;

class OccupancyControl extends Control {
    use TInjectTranslator;

    /** @var  ApplicationDao */
    private $applicationDao;

    /** @var  OptionDao */
    private $optionDao;

    /** @var  OccupancyIcons */
    private $occupancyIcons;

    /** @var  EventEntity */
    private $event;

    /** @var bool */
    private $admin = false;

    public function __construct(ITranslator $translator, ApplicationDao $applicationDao, OptionDao $optionDao, OccupancyIcons $occupancyIcons) {
        parent::__construct();
        $this->applicationDao = $applicationDao;
        $this->optionDao = $optionDao;
        $this->occupancyIcons = $occupancyIcons;
        $this->injectTranslator($translator);
    }

    public function setAdmin(bool $admin = true) {
        $this->admin = $admin;
    }


    public function setEvent(EventEntity $event) {
        $this->event = $event;
    }

    public function render() {
        $event = $this->event;
        if (!$event) {
            throw new NotReadyException("No event to render");
        }
        $template = $this->getTemplate();
        $template->setFile(__DIR__ . '/OccupancyControl.latte');
        $template->event = $event;
        $template->event_issued = $this->limitValue($this->applicationDao->countIssuedApplications($event), $event->getCapacity());
        $template->event_occupied = $this->limitValue($this->applicationDao->countOccupiedApplications($event), $event->getCapacity());
        $template->options = $this->optionDao->getOptionsWithLimitedCapacity($event);
        $template->admin = $this->admin;
        $template->render();
    }

    private function limitValue(int $value, ?int $limit): int {
        if ($this->admin || !$limit) {
            return $value;
        }
        return min($value, $limit);
    }

    /**
     * @param TOccupancyIconAttribute $item
     * @param bool $occupied
     */
    public function renderIcon($item, int $occupied = 2, $over = false) {
        $title = $occupied ? ($occupied == 1 ? 'Issued' : 'Occupied') : 'Free';
        $title = 'Occupancy.State.' . $title;
        $title = $this->getTranslator()->translate($title);
        if ($over) {
            $title .= ' (' . $this->getTranslator()->translate('Occupancy.State.Over') . ')';
        }
        $classes = [
            'occupancy-item',
            $occupied ? ($occupied == 1 ? 'issued' : 'occupied') : 'free',
            $over ? 'over' : 'in'
        ];
        $html = Html::el('span', [
            'title' => $title,
            'class' => $classes
        ]);
        $html->addHtml(
            $this->occupancyIcons->getIconHtml($item->getOccupancyIcon(), $occupied)
        );
        echo $html;
    }

    /**
     * @param OptionEntity $option
     * @return int
     */
    public function countOptionsOccupied(OptionEntity $option): int {
        $value = $this->applicationDao->countOccupiedApplicationsWithOption($option);
        return $this->limitValue($value, $option->getCapacity());
    }

    /**
     * @param OptionEntity $option
     * @return int
     */
    public function countOptionsIssued(OptionEntity $option): int {
        $value = $this->applicationDao->countIssuedApplicationsWithOption($option);
        return $this->limitValue($value, $option->getCapacity());
    }
}