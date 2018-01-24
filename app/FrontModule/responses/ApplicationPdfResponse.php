<?php

namespace App\FrontModule\Responses;

use App\Model\Persistence\Entity\ApplicationEntity;
use App\Responses\PdfResponse\IPdfResponseFactory;
use App\Responses\PdfResponse\PdfResponse;
use Nette;
use Nette\Application\IResponse;

/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 25.1.17
 * Time: 11:11
 */
class ApplicationPdfResponse extends Nette\Application\UI\Control implements IResponse {

    /** @var IPdfResponseFactory */
    private $pdfResponseFactory;

    public function __construct(IPdfResponseFactory $pdfResponseFactory) {
        parent::__construct();
        $this->pdfResponseFactory = $pdfResponseFactory;
    }


    /** @var  \App\Model\Persistence\Entity\ApplicationEntity */
    private $application;

    public function setApplication(ApplicationEntity $application) {
        $this->application = $application;
    }

    protected function buildHtml(): string {
        $template = $this->getTemplate();
        $template->setFile(__DIR__ . '/ApplicationPdfResponse.latte');
        $application = $this->application;
        $bus = null;
        $tricko = null;
        foreach ($application->getChoices() as $choice) {
            if ($choice->getOption()->getAddition()->getName() == "Doprava") {
                if ($choice->getOption()->getName() == 'Autobus') {
                    $bus = true;
                }
                if ($choice->getOption()->getName() == 'Individuální') {
                    $bus = false;
                }
            }
            if ($choice->getOption()->getAddition()->getName() == "Tričko") {
                $tricko = $choice->getOption()->getName();
            }
        }
        $address = [];
        if ($application->getAddress()) {
            $address[] = $application->getAddress();
        }
        if ($application->getCity()) {
            $address[] = $application->getCity();
        }
        if ($application->getZip()) {
            $address[] = $application->getZip();
        }
        $template->application = $application;
        $template->bus = $bus;
        $template->tricko = $tricko;
        $template->address = implode('; ', $address);
        $template->id = Nette\Utils\Strings::padLeft($this->application->getId(), 3, '0');
        $html = (string)$template;
        return $html;
    }

    protected function buildPdfResponse() {
        $html = $this->buildHtml();
        $pdf = $this->pdfResponseFactory->create($html);
        $pdf->setSaveMode(PdfResponse::INLINE);
        $pdf->setPageFormat("A4");
        $pdf->setDocumentTitle("Application form");
        $pdf->setDocumentAuthor("ldtpardubice.cz");
        $pdf->setPageMargins("13,13,13,13,10,10");
        $mpdf = $pdf->getMPDF();
        $mpdf->setFooter("<a href='https://ldtpardubice.cz'>ldtpardubice.cz</a>");
        return $pdf;
    }

    /**
     * @param Nette\Http\IRequest $httpRequest
     * @param Nette\Http\IResponse $httpResponse
     */
    function send(Nette\Http\IRequest $httpRequest, Nette\Http\IResponse $httpResponse) {
        $pdfResponse = $this->buildPdfResponse();
        $pdfResponse->send($httpRequest, $httpResponse);
    }
}