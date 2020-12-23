<?php

declare(strict_types=1);

namespace Ticketer\Model;

use Nette\Localization\ITranslator;
use RuntimeException;
use Ticketer\Controls\TInjectTranslator;
use Ticketer\Model\Exceptions\DuplicateEntryException;
use Ticketer\Model\Exceptions\NotFoundException;
use Nette\SmartObject;
use Nette\Utils\Html;
use Nette\Utils\Strings;

class OccupancyIcons
{
    use SmartObject;
    use TInjectTranslator;

    /** @var array<string,string> */
    private $icons = [];

    /** @var array<string,string> */
    private $classes = [];

    public function __construct(ITranslator $translator)
    {
        $this->injectTranslator($translator);
    }


    /**
     * @param string $key
     * @param string|null $label
     * @param string|null $class
     */
    public function addIcon(string $key, ?string $label = null, ?string $class = null): void
    {
        if ('' === $key) {
            throw new RuntimeException("Key can't be empty");
        }
        $label = $label ?? Strings::firstUpper($key);
        $class = $class ?? 'occupancy-icon-' . $key;
        if (isset($this->icons[$key])) {
            throw new RuntimeException("There is already icon with key " . $key);
        }
        $this->icons[$key] = $label;
        $this->classes[$key] = $class;
    }

    /**
     * @return array<string,string>
     */
    public function getIcons(): array
    {
        return $this->icons;
    }

    /**
     * @return array<string,string>
     */
    public function getIconClasses(): array
    {
        return $this->classes;
    }

    /**
     * @param string|null $noneLabel
     * @return array<string,Html>
     */
    public function getLabeledIcons(?string $noneLabel = null): array
    {
        $result = [];
        if (null !== $noneLabel) {
            $result[''] = Html::el()->setText($noneLabel);
        }
        foreach ($this->icons as $key => $title) {
            $result[$key] = $this->getLabel($key);
        }

        return $result;
    }

    /**
     * @param string|null $key
     * @param int $occupied
     * @return Html
     */
    public function getIconHtml(?string $key, int $occupied = 2): Html
    {
        $icons = $this->getIconClasses();
        if (!isset($icons[$key])) {
            $key = null;
        }
        if (null === $key) {
            $key = array_key_first($this->icons);
        }

        return Html::el(
            "i",
            [
                'class' => [
                    "occupancy-icon",
                    $icons[$key],
                    $this->getHtmlClass($occupied),
                ],
            ]
        );
    }

    public function getLabel(string $key): Html
    {
        return Html::el("span")->addHtml(
            $this->getIconHtml($key)
        )->addText(' ')
            ->addHtml(
                Html::el('span')
                    ->setText($this->getLabelText($key))
            );
    }

    protected function getLabelText(string $key): string
    {
        $label = $this->icons[$key] ?? "";

        return $this->getTranslator()->translate('Value.OccupancyIcon.' . $label);
    }

    /**
     * @param int $occupied
     * @return string
     */
    private function getHtmlClass(int $occupied): string
    {
        switch ($occupied) {
            case 0:
                return 'free';
            case 2:
                return 'issued';
            default:
                return 'occupied';
        }
    }
}
