<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 9.3.15
 * Time: 15:47
 */

namespace Stopka\NetteFormRenderer;


use Nette\Forms\Controls\BaseControl;
use Nette\Utils\Html;

class HtmlFormComponent extends BaseControl{
    /**
     * @param  string  label
     * @param Html html
     */
    public function __construct($label = NULL, Html $html){
        parent::__construct($label);
        $this->setHtml($html);
        $this->setOmitted();
    }

    public function setHtml(Html $html){
        $this->control->setName($html->getName());
        $this->control->addHtml($html->getHtml());
        $this->control->addAttributes(["class"=>"form-html-control"]);
    }
}