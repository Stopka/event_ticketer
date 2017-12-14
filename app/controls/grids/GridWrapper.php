<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 22.1.17
 * Time: 14:38
 */

namespace App\Controls\Grids;


use App\Controls\Control;
use App\Model\DateFormatter;
use Nette\Localization\ITranslator;

abstract class GridWrapper extends Control {

    /**
     * @var null|string path to template
     */
    private $template_path = null;

    /** @var  ITranslator */
    private  $translator;

    /** @var DateFormatter */
    private $dateFormatter;

    /**
     * GridWrapper constructor.
     * @param GridWrapperDependencies $gridWrapperDependencies
     */
    public function __construct(GridWrapperDependencies $gridWrapperDependencies) {
        parent::__construct();
        $this->translator = $gridWrapperDependencies->getTranslator();
        $this->dateFormatter = $gridWrapperDependencies->getDateFormatter();
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
        $grid->setTranslator($this->translator);
        //$grid->setTranslator(new FileTranslator('cs'));
        $grid->setDateFormatter($this->dateFormatter);
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