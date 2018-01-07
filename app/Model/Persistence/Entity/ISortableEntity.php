<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 12.1.17
 * Time: 22:42
 */

namespace App\Model\Persistence\Attribute;

interface ISortableEntity {

    /**
     * @return string
     */
    public function getId();

    /**
     * @return int
     */
    public function getPosition(): int;

    /**
     * @param int $position
     */
    public function setPosition(int $position): void;
}