<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 22.1.17
 * Time: 14:38
 */

namespace App\Grids;


use Grido\Translations\FileTranslator;
use Nette\Application\UI\Control;
use Nette\Localization\ITranslator;

abstract class GridWrapper extends Control {

    /**
     * @var null|string path to template
     */
    private $template_path = null;

    /** @var  ITranslator */
    private  $translator;

    public function __construct(ITranslator $translator) {
        $this->translator = $translator;
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
        //$grid->setTranslator($this->translator);
        $grid->setTranslator(new FileTranslator('cs'));
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
        $this->template->setFile($template_path);
        $this->template->render();
    }

    /**
     * @param $template_path string
     */
    protected function setTemplate($template_path) {
        $this->template_path = $template_path;
    }
}