<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 22.1.17
 * Time: 14:38
 */

namespace App\Controls\Grids;


use App\Controls\Control;
use App\Controls\TInjectDateFormatter;

abstract class GridWrapper extends Control {
    use TInjectDateFormatter;

    /**
     * @var null|string path to template
     */
    private $template_path = null;

    /**
     * GridWrapper constructor.
     * @param GridWrapperDependencies $gridWrapperDependencies
     */
    public function __construct(GridWrapperDependencies $gridWrapperDependencies) {
        parent::__construct($gridWrapperDependencies->getControlDependencies());
        $this->injectDateFormatter($gridWrapperDependencies->getDateFormatter());
    }


    /**
     * @return Grid
     */
    protected function createGrid() {
        return new Grid();
    }

    /**
     * @return Grid
     */
    protected function createComponentGrid() {
        $grid = $this->createGrid();
        $grid->setTranslator($this->getTranslator());
        //$grid->setTranslator(new FileTranslator('cs'));
        $grid->setDateFormatter($this->getDateFormatter());
        $this->configure($grid);
        $grid->setDefaultPerPage(300);
        $grid->setPerPageList([300]);
        return $grid;
    }

    abstract protected function configure(Grid $grid);

    /**
     * @param array ...$args
     */
    public function render(...$args) {
        $template_path = $this->template_path;
        if (!$template_path) {
            $template_path = __DIR__.'/GridWrapper.latte';
        }
        $template = $this->getTemplate();
        $template->setFile($template_path);
        $template->render();
    }

    /**
     * @param $template_path string
     */
    protected function setTemplate($template_path) {
        $this->template_path = $template_path;
    }
}