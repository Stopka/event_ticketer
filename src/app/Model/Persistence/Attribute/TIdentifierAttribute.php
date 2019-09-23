<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 12.1.17
 * Time: 22:43
 */

namespace App\Model\Persistence\Attribute;

use Doctrine\ORM\Mapping as ORM;

trait TIdentifierAttribute {
    use \Kdyby\Doctrine\Entities\Attributes\Identifier;

    /**
     * @ORM\Column(type="string",unique=true)
     * @var string
     */
    private $uid;


    /**
     * Resets id to null
     */
    protected function resetId(): void {
        $this->id = null;
        $this->uid = self::generateUid();
    }

    /**
     * Return uuid without dashes
     * @return null|string
     */
    public function getIdAlphaNumeric(): ?string {
        $id = $this->getId();
        if ($id === null) {
            return null;
        }
        return str_replace("-", "", $this->getId());
    }

    /**
     * @return string
     */
    public function getUid(): string {
        return $this->uid;
    }

    public static function generateUid(): string {
        $random = random_int(0, 1000000000);
        $hash = hash('sha256', $random);
        $string = substr($hash, 0, 16);
        $prefix = $string . '.';
        return uniqid($prefix, true);
    }
}