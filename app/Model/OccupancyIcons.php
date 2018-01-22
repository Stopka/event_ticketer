<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 28.11.17
 * Time: 12:10
 */

namespace App\Model;


use App\Controls\TInjectTranslator;
use App\Model\Exception\DuplicateEntryException;
use App\Model\Exception\NotFoundException;
use Kdyby\Translation\ITranslator;
use Nette\SmartObject;
use Nette\Utils\Html;
use Nette\Utils\Strings;

class OccupancyIcons {
    use SmartObject, TInjectTranslator;

    private $icons = [];

    private $classes = [];

    public function __construct(ITranslator $translator) {
        $this->injectTranslator($translator);
    }


    /**
     * @param string $key
     * @param string $label
     */
    public function addIcon(string $key, ?string $label = null, ?string $class = null): void {
        $label = $label ?? Strings::firstUpper($key);
        $class = $class ?? 'occupancy-icon-' . $key;
        if (isset($this->icons[$key])) {
            throw new DuplicateEntryException("There is already icon with key " . $key);
        }
        $this->icons[$key] = $label;
        $this->classes[$key] = $class;
    }

    /**
     * @return string[]
     */
    public function getIcons(): array {
        return $this->icons;
    }

    /**
     * @return string[]
     */
    public function getIconClasses(): array {
        return $this->classes;
    }

    /**
     * @param $noneLabel null|string
     * @return array
     */
    public function getLabeledIcons(?string $noneLabel = null): array {
        $result = [];
        if ($noneLabel) {
            $result[null] = $noneLabel;
        }
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
    public function getIconHtml(?string $key, int $occupied = 2): Html {
        $icons = $this->getIconClasses();
        if (!isset($icons[$key])) {
            $key = null;
        }
        if (!$key) {
            foreach ($this->icons as $k => $label) {
                $key = $k;
                break;
            }
        }
        return Html::el("i", [
            'class' => [
                "occupancy-icon",
                $icons[$key],
                $occupied ? ($occupied == 2 ? 'issued' : 'occupied') : 'free'
            ]
        ]);
    }

    protected function getLabel(string $key): Html {
        return Html::el("span")->addHtml(
            $this->getIconHtml($key)
        )->addText(' ')
            ->addHtml(
            Html::el('span')
                ->setText($this->getLabelText($key))
        );
    }

    protected function getLabelText(string $key): string {
        $label = $this->icons[$key] ?? "";
        return $this->getTranslator()->translate('Value.OccupancyIcon.' . $label);
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