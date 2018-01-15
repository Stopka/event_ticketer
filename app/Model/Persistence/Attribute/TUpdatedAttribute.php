<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 12.1.17
 * Time: 22:42
 */

namespace App\Model\Persistence\Attribute;

use Doctrine\ORM\Mapping as ORM;

trait TUpdatedAttribute {

    /**
     * @ORM\Column(type="date", nullable=true)
     * @var \DateTime
     */
    private $updated;

    /**
     * @return \DateTime
     */
    public function getUpdated(): ?\DateTime {
        return $this->updated;
    }

    /**
     * @param \DateTime|NULL $updated
     */
    protected function setUpdated(?\DateTime $updated = NULL): void {
        if(!$updated){
            $updated = new \DateTime();
        }
        $this->updated = $updated;
    }

    /**
     *
     */
    protected function resetUpdated(): void{
        $this->updated = NULL;
    }

}