<?php
/**
 * Created by IntelliJ IDEA.
 * AdministratorEntity: stopka
 * Date: 12.1.17
 * Time: 0:18
 */

namespace App\Model\Entities;

use App\Model\Entities\Attributes\BirthDate;
use App\Model\Entities\Attributes\PersonName;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\Attributes\Identifier;

/**
 * Administrátor systému
 * @package App\Model\Entities
 * @ORM\Entity
 */
class ChildEntity extends BaseEntity {
    use Identifier,PersonName,BirthDate;

    public function __construct() {
        $this->options = new ArrayCollection();
    }

    /**
     * @ORM\ManyToMany(targetEntity="OptionEntity"))
     * @var OptionEntity[]
     */
    private $options;

    /**
     * @ORM\ManyToOne(targetEntity="OrderEntity", inversedBy="children")
     * @var OrderEntity
     */
    private $parent;

    /**
     * @return OptionEntity[]
     */
    public function getOptions() {
        return $this->options;
    }

    /**
     * @param OptionEntity $option
     */
    public function addOption($option) {
        $this->options->add($option);
    }

    /**
     * @param OptionEntity $option
     */
    public function removeOption($option) {
        $this->options->removeElement($option);
    }

    /**
     * @return OrderEntity
     */
    public function getParent() {
        return $this->parent;
    }

    /**
     * @param OrderEntity $parent
     */
    public function setParent($parent) {
        if($this->parent){
            $this->parent->removeInversedChild($this);
        }
        $this->parent = $parent;
        if($parent) {
            $parent->addInversedChild($this);
        }
    }
}