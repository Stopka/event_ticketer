<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 28.11.17
 * Time: 12:10
 */

namespace App\Model;


use App\Model\Exception\DuplicateEntryException;
use App\Model\Exception\NotFoundException;
use Nette\Object;
use Nette\Utils\Html;

class OccupancyIcons extends Object {
    private $icons = [];

    /**
     * @param string $key
     * @param string $label
     */
    public function addIcon(string $key, string $label): void {
        if (isset($this->icons[$key])) {
            throw new DuplicateEntryException("There is already icon with key " . $key);
        }
        $this->icons[$key] = $label;
    }

    /**
     * @return string[]
     */
    public function getIcons(): array {
        $this->icons;
    }

    /**
     * @return array
     */
    public function getLabeledIcons(): array {
        $result = [];
        foreach ($this->icons as $key => $title) {
            $result[$key] = $this->getLabel($key);
        }
        return $result;
    }

    /**
     * @param string $key
     * @param bool $occupied
     * @return Html
     */
    public function getIconHtml(string $key, bool $occupied = true): Html {
        return Html::el("i", [
            'class' => [
                "occupancy-icon",
                "occupancy-icon-$key",
                $occupied ? 'occupied' : 'free'
            ]
        ]);
    }

    protected function getLabel(string $key): Html {
        return Html::el("span")->addHtml(
            $this->getIconHtml($key)
        )->addHtml(
            Html::el('span')
                ->setText($this->icons[$key] ?? "")
        );
    }

    /**
     * @param string $key
     * @param bool $occupied
     * @return string
     * @throws NotFoundException
     */
    public function getPublicIconPath(string $key, bool $occupied = true): string {
        if (!isset($this->icons[$key])) {
            throw new NotFoundException("Icon is not defined");
        }
        return $this->iconsPath . '/' . $key . '_' . ($occupied ? '1' : '0') . '.' . ($this->extensions[$key]);
    }
}