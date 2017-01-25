<?php

namespace App\FrontModule\Responses;

use App\Model\Entities\ApplicationEntity;
use Joseki\Application\Responses\PdfResponse;
use Nette\Application\Application;

/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 25.1.17
 * Time: 11:11
 */
class ApplicationPdfRenderer extends PdfRenderer {

    /** @var  ApplicationEntity */
    private $application;

    public function __construct(Application $app) {
        parent::__construct($app);
        $this->setTemplatePath(__DIR__.'/ApplicationPdfRenderer.latte');
    }


    public function setApplication(ApplicationEntity $application){
        $this->application = $application;
    }

    protected function createTemplate() {
        $template = parent::createTemplate();
        $template->application = $this->application;
        return $template;
    }

    protected function createPdf() {
        $pdf = parent::createPdf();
        $pdf->setPageFormat("A4-P");
        $pdf->getMPDF()->setFooter('www.ldtpardubice.cz');
        $pdf->setSaveMode(PdfResponse::INLINE);
        return $pdf;
    }

}