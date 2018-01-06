<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 12.1.17
 * Time: 22:43
 */

namespace App\Model\Persistence\Attribute;

use Nette\InvalidArgumentException;

trait TIdentifierAttribute {
    use \Kdyby\Doctrine\Entities\Attributes\UniversallyUniqueIdentifier;

    /**
     * Resets id to null
     */
    protected function resetId(): void {
        $this->id = null;
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

    public static function getIdFromAplhaNumeric(?string $stringId): ?string {
        if($stringId === null){
            return null;
        }
        if (!preg_match("/[a-z0-9]{12}/", $stringId)) {
            throw new InvalidArgumentException("String '$stringId' is not a valid alpha numeric id!");
        }
        return substr($stringId,0,8).'-'.
            substr($stringId,8,4).'-'.
            substr($stringId,8+4,4).'-'.
            substr($stringId,8+4+4,4).'-'.
            substr($stringId,8+4+4+4, 12);
    }
}