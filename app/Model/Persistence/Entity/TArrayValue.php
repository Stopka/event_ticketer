<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 20.1.18
 * Time: 11:28
 */

namespace App\Model\Persistence\Entity;


use Nette\Utils\Strings;

trait TArrayValue {
    /**
     * @param $with null|string[] všechny pokud není dáno, jinak list
     * @param $without string[] bez kterých parametrů
     * @return array
     */
    public function getValueArray(?array $with = null, array $without = []): array {
        array_push($without, 'getValueArray');
        for ($i = 0; $i < count($without); $i++) {
            if (Strings::startsWith($without[$i], 'get')) {
                continue;
            }
            $without[$i] = 'get' . Strings::firstUpper($without[$i]);
        }
        $methods = get_class_methods($this);
        if ($with) {
            for ($i = 0; $i < count($with); $i++) {
                if (!Strings::startsWith($with[$i], 'get')) {
                    $with[$i] = 'get' . Strings::firstUpper($with[$i]);
                }
                if (!in_array($with[$i], $methods)) {
                    unset($with[$i]);
                }
            }
            $methods = $with;
        }
        $results = [];
        foreach ($methods as $method) {
            $reflection = new \ReflectionMethod($this, $method);
            $parameters = $reflection->getParameters();
            if (!Strings::startsWith($method, 'get') || in_array($method, $without) || count($parameters)) {
                continue;
            }
            $key = Strings::firstLower(Strings::substring($method, 3));
            try {
                $results[$key] = call_user_func([$this, $method]);
            } catch (\Exception $e) {

            }
        }
        return $results;
    }

    /**
     * @param array $values
     * @param array $omit
     */
    public function setByValueArray(array $values, array $omit = []): void {
        foreach ($values as $name => $value) {
            if (in_array($name, $omit)) {
                continue;
            }
            $setterName = 'set' . Strings::capitalize($name);
            if (method_exists($this, $setterName)) {
                call_user_func([$this, $setterName], $value);
            }
        }
    }
}