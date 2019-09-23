<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 2.2.17
 * Time: 0:00
 */

namespace App\AdminModule\Controls\Grids;


use App\Controls\Grids\Grid;
use App\Model\Persistence\Entity\AdditionEntity;

class CartApplicationsGridWrapper extends \App\FrontModule\Controls\Grids\CartApplicationsGridWrapper {
    protected function isVisible(AdditionEntity $additionEntity): bool {
        return $additionEntity->isVisibleIn(AdditionEntity::VISIBLE_PREVIEW);
    }


    protected function appendApplicationColumns(Grid $grid) {
        parent::appendApplicationColumns($grid);
        $grid->addColumnDate('birthDate','Datum narození');
        $grid->addColumnText('gender','Pohlaví')
            ->setReplacement([0=>'Muž',1=>'Žena']);
    }

}