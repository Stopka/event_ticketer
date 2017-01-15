<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 12.1.17
 * Time: 22:42
 */

namespace App\Model\Entities\Attributes;

use Doctrine\ORM\Mapping as ORM;

trait Guid {

    /**
     * @ORM\Column(type="guid")
     * @ORM\GeneratedValue(strategy="UUID")
     * @var string
     */
    private $guid;

    /**
     * @return string
     */
    public function getGuid() {
        return $this->guid;
    }
}