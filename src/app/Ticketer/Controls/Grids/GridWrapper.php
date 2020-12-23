<?php

declare(strict_types=1);

namespace Ticketer\Controls\Grids;

use Ticketer\Controls\Control;
use Ticketer\Controls\TInjectDateFormatter;

abstract class GridWrapper extends Control
{
    use TInjectDateFormatter;

    /**
     * @var null|string path to template
     */
    private $templatePath = null;

    /**
     * GridWrapper constructor.
     * @param GridWrapperDependencies $gridWrapperDependencies
     */
    public function __construct(GridWrapperDependencies $gridWrapperDependencies)
    {
        parent::__construct($gridWrapperDependencies->getControlDependencies());
        $this->injectDateFormatter($gridWrapperDependencies->getDateFormatter());
    }


    /**
     * @return Grid
     */
    protected function createGrid(): Grid
    {
        return new Grid();
    }

    /**
     * @return Grid
     */
    protected function createComponentGrid(): Grid
    {
        $grid = $this->createGrid();
        $grid->setTranslator($this->getTranslator());
        $grid->setDateFormatter($this->getDateFormatter());
        $grid->setDefaultPerPage(300);
        $grid->setItemsPerPageList([300]);
        $this->configure($grid);

        return $grid;
    }

    abstract protected function configure(Grid $grid): void;

    /**
     * @param array<mixed> ...$args
     */
    public function render(...$args): void
    {
        $templatePath = $this->templatePath;
        if (null === $templatePath) {
            $templatePath = __DIR__ . '/GridWrapper.latte';
        }
        $template = $this->getTemplate();
        $template->setFile($templatePath);
        $template->render();
    }

    /**
     * @param string|null $templatePath
     */
    protected function setTemplate(?string $templatePath): void
    {
        $this->templatePath = $templatePath;
    }
}
