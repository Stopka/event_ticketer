<?php
/**
 * Created by IntelliJ IDEA.
 * AdministratorEntity: stopka
 * Date: 12.1.17
 * Time: 0:18
 */

namespace App\Model\Entities;

use Doctrine\ORM\Mapping as ORM;
use Nette\Utils\Strings;

/**
 * Administrátor systému
 * @package App\Model\Entities
 */
class BaseEntity extends \Kdyby\Doctrine\Entities\BaseEntity {

    /**
     * @param array $values
     */
    public function setByValueArray($values) {
        foreach ($values as $name => $value) {
            $setterName = 'set' . Strings::capitalize($name);
            if (method_exists($this, $setterName)) {
                call_user_func([$this, $setterName], $value);
            }
        }
    }

    /**
     * @param $with null|string[] všechny pokud není dáno, jinak list
     * @param $without string[] bez kterých parametrů
     * @return array
     */
    public function getValueArray($with = null, $without = []) {
        array_push($without,'getValueArray');
        for($i=0;$i<count($without);$i++){
            if(Strings::startsWith($without[$i],'get')){
                continue;
            }
            $without[$i]='get'.Strings::capitalize($without[$i]);
        }
        $methods = get_class_methods($this);
        if($with){
            for($i=0;$i<count($with);$i++) {
                if (!Strings::startsWith($with[$i], 'get')) {
                    $with[$i] = 'get' . Strings::capitalize($with[$i]);
                }
                if (!in_array($with[$i], $methods)) {
                    unset($with[$i]);
                }
            }
            $methods = $with;
        }
        $results = [];
        foreach ($methods as $method) {
            if(!Strings::startsWith($method,'get') || in_array($method,$without)){
                continue;
            }
            $key = Strings::firstLower(Strings::substring($method,3));
            try {
                $results[$key] = call_user_func([$this, $method]);
            }catch (\Exception $e){

            }
        }
        return $results;
    }
}