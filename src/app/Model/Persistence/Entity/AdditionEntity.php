<?php
/**
 * Created by IntelliJ IDEA.
 * AdministratorEntity: stopka
 * Date: 12.1.17
 * Time: 0:18
 */

namespace App\Model\Persistence\Entity;

use App\Model\Persistence\Attribute\ISortableEntity;
use App\Model\Persistence\Attribute\TIdentifierAttribute;
use App\Model\Persistence\Attribute\TNameAttribute;
use App\Model\Persistence\Attribute\TPositionAttribute;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Přídavek k přihlášce (Přihláška, Faktura, Doprava, Tričko...)
 * @package App\Model\Entities
 * @ORM\Entity
 */
class AdditionEntity extends BaseEntity implements ISortableEntity {
    use TIdentifierAttribute, TPositionAttribute, TNameAttribute;

    const VISIBLE_RESERVATION = 'reservation';
    const VISIBLE_REGISTER = 'register';
    const VISIBLE_CUSTOMER = 'customer';
    const VISIBLE_PREVIEW = 'preview';
    const VISIBLE_EXPORT = 'export';

    public static function getVisiblePlaces(){
        return [
            self::VISIBLE_RESERVATION => 'Value.Addition.Visible.Reservation',
            self::VISIBLE_REGISTER => 'Value.Addition.Visible.Register',
            self::VISIBLE_CUSTOMER => 'Value.Addition.Visible.Customer',
            self::VISIBLE_PREVIEW => 'Value.Addition.Visible.Preview',
            self::VISIBLE_EXPORT => 'Value.Addition.Visible.Export',
        ];
    }

    public function __construct() {
        parent::__construct();
        $this->options = new ArrayCollection();
    }

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @var integer
     */
    private $requiredForState;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @var integer
     */
    private $enoughForState;

    /**
     * @ORM\Column(type="integer")
     * @var integer
     */
    private $minimum = 1;

    /**
     * @ORM\Column(type="integer")
     * @var integer
     */
    private $maximum = 1;

    /**
     * @ORM\Column(type="json_array")
     * @var string[]
     */
    private $visible = [];

    /**
     * @ORM\ManyToOne(targetEntity="EventEntity", inversedBy="additions")
     * @var EventEntity
     */
    private $event;

    /**
     * @ORM\OneToMany(targetEntity="OptionEntity", mappedBy="addition", cascade={"persist","remove"})
     * @ORM\OrderBy({"position" = "ASC"})
     * @var OptionEntity[]
     */
    private $options;

    /**
     * @return EventEntity
     */
    public function getEvent(): ?EventEntity {
        return $this->event;
    }

    /**
     * @param EventEntity $event
     * @return $this
     */
    public function setEvent(?EventEntity $event) {
        if ($this->event) {
            $event->event->removeInversedAddition($this);
        }
        $this->event = $event;
        if ($event) {
            $event->addInversedAddition($this);
        }
        return $this;
    }

    /**
     * @return OptionEntity[]
     */
    public function getOptions(): array {
        return $this->options->toArray();
    }

    /**
     * @param OptionEntity $option
     */
    public function addOption(OptionEntity $option): void {
        $option->setAddtition($this);
    }

    /**
     * @param OptionEntity $option
     */
    public function removeOption(OptionEntity $option): void {
        $option->setAddtition(NULL);
    }

    /**
     * @param OptionEntity $option
     * @internal
     */
    public function addInversedOption(OptionEntity $option): void {
        $this->options->add($option);
    }

    /**
     * @param OptionEntity $option
     * @internal
     */
    public function removeInversedOption(OptionEntity $option): void {
        $this->options->removeElement($option);
    }

    /**
     * @return int
     */
    public function getMinimum(): int {
        return $this->minimum;
    }

    /**
     * @param int $minimum
     */
    public function setMinimum(int $minimum): void {
        $this->minimum = $minimum;
    }

    /**
     * @return int
     */
    public function getMaximum(): int {
        return $this->maximum;
    }

    /**
     * @param int $maximum
     */
    public function setMaximum(int $maximum): void {
        $this->maximum = $maximum;
    }

    /**
     * @return int
     */
    public function getRequiredForState(): ?int {
        return $this->requiredForState;
    }

    /**
     * @param int $requiredForState
     */
    public function setRequiredForState(?int $requiredForState): void {
        $this->requiredForState = $requiredForState;
    }

    /**
     * @param string $place
     * @return bool
     */
    public function isVisibleIn(string $place): bool {
        return in_array($place,$this->getVisible());
    }

    /**
     * @return string[]
     */
    public function getVisible(): array{
        return $this->visible;
    }

    /**
     * @param  string[] $places
     */
    public function setVisible(array $places): void{
        $this->visible = array_values($places);
    }

    /**
     * @param bool $visible
     */
    public function setVisibleIn(string $place, bool $visible = true): void {
        $index = array_search($place, $this->getVisible());
        if($index !== false && !$visible){
            unset($this->visible[$index]);
        }
        if($index === false && $visible){
            $this->visible[] = $place;
        }
    }

    /**
     * @return int
     */
    public function getEnoughForState(): ?int {
        return $this->enoughForState;
    }

    /**
     * @param int $enoughForState
     */
    public function setEnoughForState(?int $enoughForState): void {
        $this->enoughForState = $enoughForState;
    }

}