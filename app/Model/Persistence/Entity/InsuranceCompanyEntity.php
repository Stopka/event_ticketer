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
 * Pojišťovna
 * @package App\Model\Entities
 * @ORM\Entity
 */
class InsuranceCompanyEntity extends BaseEntity {
    use TIdentifierAttribute, TNameAttribute;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    private $code;

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

}