<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 20.1.17
 * Time: 20:18
 */

namespace App\Model\Facades;


use App\Model\Entities\ChoiceEntity;

class ChoiceFacade extends EntityFacade {

    protected function getEntityClass() {
        return ChoiceEntity::class;
    }

    /**
     * @param $choiceId integer
     * @return null|ChoiceEntity
     */
    public function inversePayed($choiceId){
        /** @var ChoiceEntity $choice */
        $choice = $this->get($choiceId);
        $choice->inversePayed();
        $this->getEntityManager()->flush();
    }

}