<?php

declare(strict_types=1);

namespace Ticketer\Modules\AdminModule\Controls\Grids;

use Ticketer\Controls\Grids\Grid;
use Ticketer\Model\Database\Entities\AdditionEntity;
use Ticketer\Modules\FrontModule\Controls\Grids\CartApplicationsGridWrapper as FrontCartApplicationsGridWrapper;

class CartApplicationsGridWrapper extends FrontCartApplicationsGridWrapper
{
    protected function isVisible(AdditionEntity $additionEntity): bool
    {
        return $additionEntity->isVisibleIn(AdditionEntity::VISIBLE_PREVIEW);
    }


    protected function appendApplicationColumns(Grid $grid): void
    {
        parent::appendApplicationColumns($grid);
        $grid->addColumnDate('birthDate', 'Datum narození');
        $grid->addColumnText('gender', 'Pohlaví')
            ->setReplacement([0 => 'Muž', 1 => 'Žena']);
    }
}
