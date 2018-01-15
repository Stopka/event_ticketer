<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 15.1.18
 * Time: 20:16
 */

namespace App\Model\Persistence\Manager;


use App\Model\Exception\ApplicationException;
use App\Model\Persistence\Attribute\TNumberAttribute;
use Kdyby\Doctrine\EntityManager;

trait TUpdateNumber {

    abstract function getEntityManager(): EntityManager;

    private function getEm(): EntityManager{
        $em = $this->getEntityManager();
        return $em->create(
            $em->getConnection(),
            $em->getConfiguration(),
            $em->getEventManager()
        );
    }

    /**
     * @param TNumberAttribute[] $items
     * @return int
     * @throws ApplicationException
     */
    public function flushNumbered(array $items): void{
        $em = $this->getEm();
        for($i = 0; $i<5; $i++) {
            try {
                foreach ($items as $item){

                }
                $em->flush();
                return;
            }catch (\Exception $e){
                $exception = $e;
            }
        }
        throw $exception;
    }
}