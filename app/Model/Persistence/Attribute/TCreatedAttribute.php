<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 12.1.17
 * Time: 22:42
 */

namespace App\Model\Persistence\Attribute;

use Doctrine\ORM\Mapping as ORM;

trait TCreatedAttribute {

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @var \DateTime
     */
    private $created;

    /**
     * @return \DateTime
     */
    public function getCreated(): ?\DateTime {
        return $this->created;
    }

    /**
     * @param \DateTime|NULL $created
     */
    protected function setCreated(?\DateTime $created = NULL): void {
        if (!$created) {
            $created = new \DateTime();
        }
        $this->created = $created;
    }

    /**
     *
     */
    protected function resetUpdated(): void {
        $this->updated = NULL;
    }

}