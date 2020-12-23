<?php

declare(strict_types=1);

namespace Ticketer\Modules\FrontModule\Controls;

use Nette\Localization\ITranslator;
use Nette\Utils\Strings;
use Ticketer\Controls\TInjectTranslator;
use Ticketer\Model\Exceptions\NotReadyException;
use Ticketer\Model\OccupancyIcons;
use Ticketer\Model\Database\Attributes\TOccupancyIconAttribute;
use Ticketer\Model\Database\Daos\ApplicationDao;
use Ticketer\Model\Database\Daos\OptionDao;
use Ticketer\Model\Database\Entities\EventEntity;
use Ticketer\Model\Database\Entities\OptionEntity;
use Nette\Application\UI\Control;
use Nette\Utils\Html;

class OccupancyControl extends Control
{
    use TInjectTranslator;

    /** @var  ApplicationDao */
    private $applicationDao;

    /** @var  OptionDao */
    private $optionDao;

    /** @var  OccupancyIcons */
    private $occupancyIcons;

    /** @var  EventEntity|null */
    private $event;

    /** @var bool */
    private $admin = false;

    public function __construct(
        ITranslator $translator,
        ApplicationDao $applicationDao,
        OptionDao $optionDao,
        OccupancyIcons $occupancyIcons
    ) {
        $this->applicationDao = $applicationDao;
        $this->optionDao = $optionDao;
        $this->occupancyIcons = $occupancyIcons;
        $this->injectTranslator($translator);
    }

    public function setAdmin(bool $admin = true): void
    {
        $this->admin = $admin;
    }


    public function setEvent(EventEntity $event): void
    {
        $this->event = $event;
    }

    public function render(): void
    {
        $event = $this->event;
        if (null === $event) {
            throw new NotReadyException("No event to render");
        }
        $template = $this->getTemplate();
        $template->setFile(__DIR__ . '/OccupancyControl.latte');
        $template->event = $event;
        $template->event_issued = $this->limitValue(
            $this->applicationDao->countIssuedApplications($event),
            $event->getCapacity(),
            $event->isCapacityFull()
        );
        $template->event_occupied = $this->limitValue(
            $this->applicationDao->countOccupiedApplications($event),
            $event->getCapacity()
        );
        $template->options = $this->optionDao->getOptionsWithLimitedCapacity($event);
        $template->admin = $this->admin;
        $template->render();
    }

    private function limitValue(int $value, ?int $limit, bool $full = false): int
    {
        if (!$this->admin) {
            if ($full && null !== $limit) {
                return $limit;
            }
            if (null !== $limit) {
                return min($value, $limit);
            }
        }

        return $value;
    }

    /**
     * @param OptionEntity|EventEntity $item
     * @param int $occupied
     * @param bool $over
     */
    public function renderIcon($item, int $occupied = 2, bool $over = false): void
    {
        $stateTitle = $this->getStateTitle($occupied);
        $title = $this->getTranslator()->translate('Occupancy.State.' . $stateTitle);
        if ($over) {
            $title .= ' (' . $this->getTranslator()->translate('Occupancy.State.Over') . ')';
        }
        $classes = [
            'occupancy-item',
            Strings::lower($stateTitle),
            $over ? 'over' : 'in',
        ];
        $html = Html::el(
            'span',
            [
                'title' => $title,
                'class' => $classes,
            ]
        );
        $html->addHtml(
            $this->occupancyIcons->getIconHtml($item->getOccupancyIcon(), $occupied)
        );
        echo (string)$html;
    }

    /**
     * @param OptionEntity $option
     * @return int
     */
    public function countOptionsOccupied(OptionEntity $option): int
    {
        $value = $this->applicationDao->countOccupiedApplicationsWithOption($option);

        return $this->limitValue($value, $option->getCapacity());
    }

    /**
     * @param OptionEntity $option
     * @return int
     */
    public function countOptionsIssued(OptionEntity $option): int
    {
        $value = $this->applicationDao->countIssuedApplicationsWithOption($option);

        return $this->limitValue($value, $option->getCapacity(), $option->isCapacityFull());
    }

    private function getStateTitle(int $occupied): string
    {
        switch ($occupied) {
            case 0:
                return 'Free';
            case 1:
                return 'Issued';
            case 2:
                return 'Occupied';
            default:
                return 'Unknown';
        }
    }
}
