<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Entities;

use Exception;
use Nette\Utils\Strings;

trait TArrayValue
{
    /**
     * @param null|string[] $with všechny pokud není dáno, jinak list
     * @param string[] $without   bez kterých parametrů
     * @return array<mixed>
     */
    public function getValueArray(?array $with = null, array $without = []): array
    {
        $without[] = 'getValueArray';
        $without = $this->prepareGetters($without);
        /** @var string[] $methods */
        $methods = get_class_methods($this);
        if (null !== $with) {
            $with = $this->prepareGetters($with);
            $with = array_filter(
                $with,
                static function (string $item) use ($methods): bool {
                    return in_array($item, $methods, true);
                }
            );
            $methods = $with;
        }
        $results = [];
        foreach ($methods as $method) {
            $reflection = new \ReflectionMethod($this, $method);
            $parameters = $reflection->getParameters();
            if (
                !Strings::startsWith($method, 'get')
                || in_array($method, $without, true)
                || count($parameters) > 0
            ) {
                continue;
            }
            $key = Strings::firstLower(Strings::substring($method, 3));
            try {
                /** @var callable $getter */
                $getter = [$this, $method];
                $results[$key] = call_user_func($getter);
            } catch (Exception $e) {
            }
        }

        return $results;
    }

    /**
     * @param array<mixed> $values
     * @param array<string> $omit
     */
    public function setByValueArray(array $values, array $omit = []): void
    {
        foreach ($values as $name => $value) {
            if (in_array($name, $omit, true)) {
                continue;
            }
            $setterName = 'set' . Strings::capitalize($name);
            if (method_exists($this, $setterName)) {
                /** @var callable $setter */
                $setter = [$this, $setterName];
                call_user_func($setter, [$value]);
            }
        }
    }


    /**
     * @param array<string> $itemNames
     * @return array<string>
     */
    private function prepareGetters(array $itemNames): array
    {
        return array_map(
            static function (string $itemName): string {
                if (Strings::startsWith($itemName, 'get')) {
                    return $itemName;
                }

                return 'get' . Strings::firstUpper($itemName);
            },
            $itemNames
        );
    }
}
