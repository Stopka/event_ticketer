<?php
/**
 * Created by IntelliJ IDEA.
 * AdministratorEntity: stopka
 * Date: 12.1.17
 * Time: 0:18
 */

namespace App\Model\Entities;

use App\Model\Entities\Attributes\Name;
use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\Attributes\Identifier;

/**
 * Administrátor systému
 * @package App\Model\Entities
 * @ORM\Entity
 */
class CurrencyEntity extends BaseEntity {
    use Identifier,Name;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    private $symbol;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    private $code;

    /**
     * @ORM\Column(type="boolean", name="`default`")
     * @var boolean
     */
    private $default=false;

    /**
     * @return string
     */
    public function getSymbol() {
        return $this->symbol;
    }

    /**
     * @param string $symbol
     */
    public function setSymbol($symbol) {
        $this->symbol = $symbol;
    }

    /**
     * @return string
     */
    public function getCode() {
        return $this->code;
    }

    /**
     * @param string $code
     */
    public function setCode($code) {
        $this->code = $code;
    }

    /**
     * @return bool
     */
    public function isDefault() {
        return $this->default;
    }

    /**
     * @param bool $default
     * @return $this
     */
    public function setDefault($default = true) {
        $this->default = $default;
        return $this;
    }
}