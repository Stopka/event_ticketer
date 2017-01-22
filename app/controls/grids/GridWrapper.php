<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 22.1.17
 * Time: 14:38
 */

namespace App\Grids;


use Nette\Application\UI\Control;

abstract class GridWrapper extends Control {

    /**
     * @var null|string path to template
     */
    private $template_path = null;

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
        $this->configure($grid);
        return $grid;
    }

    abstract function configure(Grid $grid);

    /**
     * @param array ...$args
     */
    public function render(...$args) {
        if (!$this->template_path) {
            /** @var Grid $grid */
            $grid = $this->getComponent('grid');
            $grid->render(...$args);
            return;
        }
        $this->template->setFile($this->template_path);
        $this->template->render();
    }

    /**
     * @param $template_path string
     */
    protected function setTemplate($template_path) {
        $this->template_path = $template_path;
    }
}