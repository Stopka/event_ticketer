<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 18.5.17
 * Time: 20:46
 */

namespace App\Model\Persistence\Doctrine;

use Doctrine\ORM\Mapping\UnderscoreNamingStrategy;
use Nette\Utils\Strings;

class NamingStrategy extends UnderscoreNamingStrategy {

    public function classToTableName($className) {
        $subject = parent::classToTableName($className);
        if (!Strings::endsWith($className, 'Entity')) {
            return $subject;
        }
        $replace = '';
        $search = $this->getCase() == CASE_LOWER ? '_entity' : '_ENTITY';
        $pos = strrpos($subject, $search);
        if ($pos !== false) {
            $subject = substr_replace($subject, $replace, $pos, strlen($search));
        }
        return $subject;
    }

}