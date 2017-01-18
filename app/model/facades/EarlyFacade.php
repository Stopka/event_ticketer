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

    /**
     * @param $hash string
     * @return EarlyEntity|null
     */
    public function getReadyEarlyByHash($hash) {
        list($id, $guid) = explode('_', $hash . '_');
        if (!$id || !$guid)
            return NULL;
        /** @var EarlyEntity $early */
        $early = $this->get($id);
        if($early&&$early->getGuid()==$guid&&$early->isReadyToRegister())
            return $early;
        return NULL;
    }

}