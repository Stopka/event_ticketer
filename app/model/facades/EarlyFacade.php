<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 12.1.17
 * Time: 0:27
 */

namespace App\Model\Facades;


use App\Model\Entities\EarlyEntity;

class EarlyFacade extends EntityFacade {

    protected function getEntityClass() {
        return EarlyEntity::class;
    }

    public function sendEmails($wave_id){
        $dao = $this->getRepository();
        /** @var EarlyEntity[] $earlies */
        $earlies = $dao->findAll();

    }

}