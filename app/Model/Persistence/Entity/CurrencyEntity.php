<?php
/**
 * Created by IntelliJ IDEA.
 * AdministratorEntity: stopka
 * Date: 12.1.17
 * Time: 0:18
 */

namespace App\Model\Persistence\Entity;

use App\Model\Persistence\Attribute\TIdentifierAttribute;
use App\Model\Persistence\Attribute\TNameAttribute;
use Doctrine\ORM\Mapping as ORM;

/**
 * Měna
 * @package App\Model\Entities
 * @ORM\Entity
 */
class CurrencyEntity extends BaseEntity {
    use TIdentifierAttribute, TNameAttribute;

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
    private $default = false;

    /**
     * @return string
     */
    public function getSymbol(): string {
        return $this->symbol;
    }

    /**
     * @param string $symbol
     */
    public function setSymbol(string $symbol): void {
        $this->symbol = $symbol;
    }

    /**
     * @return string
     */
    public function getCode(): string {
        return $this->code;
    }

    /**
     * @param string $code
     */
    public function setCode(string $code): void {
        $this->code = $code;
    }

    /**
     * @return bool
     */
    public function isDefault(): bool {
        return $this->default;
    }

    /**
     * @param bool $default
     * @return $this
     */
    public function setDefault(bool $default = true): self {
        $this->default = $default;
        return $this;
    }
}