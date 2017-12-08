<?php

namespace App\FrontModule\Responses;
use Joseki\Application\Responses\PdfResponse;
use Nette\Application\Application;
use Nette\Application\UI\Presenter;
use Nette\SmartObject;

/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 25.1.17
 * Time: 11:11
 */
abstract class PdfRenderer {
    use SmartObject;

    /** @var  Application */
    private $app;

    private $templatePath;

    public function __construct(Application $app) {
        $this->app = $app;
    }

    public function setTemplatePath($path){
        $this->templatePath = $path;
    }

    /**
     * @return \Nette\Application\UI\ITemplate
     */
    protected function createTemplate(){
        /** @var Presenter $presenter */
        $presenter = $this->app->getPresenter();
        $template = $presenter->getTemplate();
        $template->setFile($this->templatePath);
        return $template;
    }

    /**
     * @return PdfResponse
     */
    protected function createPdf(){
        $template = $this->createTemplate();
        $pdf = new PdfResponse($template);
        return $pdf;
    }


    public function save($path, $filename = null){
        $pdf = $this->createPdf();
        $pdf->save($path, $filename);
    }

    /**
     * @return PdfResponse
     */
    public function getResponse(){
        return $this->createPdf();
    }

}