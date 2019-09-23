<?php

namespace App\FrontModule\Responses;

use App\Model\DateFormatter;
use App\Model\Persistence\Entity\ApplicationEntity;
use App\Responses\PdfResponse\IPdfResponseFactory;
use App\Responses\PdfResponse\PdfResponse;
use Nette\Application\IResponse;
use Nette\Application\UI\ITemplate;
use Nette\Application\UI\ITemplateFactory;
use Nette\Http\IRequest;
use Nette\Utils\Strings;

/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 25.1.17
 * Time: 11:11
 */
class ApplicationPdfResponse implements IResponse {

    /** @var IPdfResponseFactory */
    private $pdfResponseFactory;

    /** @var ITemplateFactory */
    private $templateFactory;

    /** @var ITemplate */
    private $template;

    /** @var DateFormatter */
    private $dateFormatter;

    /** @var string */
    private $saveMode = PdfResponse::INLINE;

    public function __construct(
        IPdfResponseFactory $pdfResponseFactory,
        DateFormatter $dateFormatter,
        ITemplateFactory $templateFactory
    ) {
        $this->pdfResponseFactory = $pdfResponseFactory;
        $this->templateFactory = $templateFactory;
        $this->dateFormatter = $dateFormatter;
    }

    protected function createTemplate(): ITemplate {
        return $this->templateFactory->createTemplate();
    }

    protected function getTemplate(): ITemplate {
        if (!$this->template) {
            $this->template = $this->createTemplate();
        }
        return $this->template;
    }


    /** @var  \App\Model\Persistence\Entity\ApplicationEntity */
    private $application;

    public function setApplication(ApplicationEntity $application) {
        $this->application = $application;
    }

    protected function buildTemplate(): ITemplate {
        $template = $this->getTemplate();
        $template->setFile(__DIR__ . '/ApplicationPdfResponse.latte');
        $template->baseDir = __DIR__;
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
        $template->birth = $application->getBirthDate() ? $this->dateFormatter->getDateString($application->getBirthDate()) : null;
        $template->id = Strings::padLeft($this->application->getId(), 3, '0');
        return $template;
    }

    protected function buildPdfResponse() {
        $template = $this->buildTemplate();
        $pdf = $this->pdfResponseFactory->create($template);
        $pdf->setSaveMode($this->getSaveMode());
        $pdf->setPageFormat("A4");
        $title = "Application form " . Strings::padLeft($this->application->getId(), 5, '0');
        $pdf->setDocumentTitle($title);
        $pdf->setDocumentAuthor("ldtpardubice.cz");
        $pdf->setPageMargins("13,13,13,13,10,10");
        $mpdf = $pdf->getMPDF();
        $mpdf->setFooter("<a href='https://ldtpardubice.cz'>ldtpardubice.cz</a>");
        return $pdf;
    }

    /**
     * @return string
     */
    public function getSaveMode(): string {
        return $this->saveMode;
    }

    /**
     * @param string $saveMode
     */
    public function setSaveMode(string $saveMode): void {
        $this->saveMode = $saveMode;
    }

    /**
     * @param IRequest $httpRequest
     * @param \Nette\Http\IResponse $httpResponse
     */
    function send(IRequest $httpRequest, \Nette\Http\IResponse $httpResponse) {
        $pdfResponse = $this->buildPdfResponse();
        $pdfResponse->send($httpRequest, $httpResponse);
    }

    /**
     * @param string $dir
     * @param string $filename
     */
    public function save(string $dir, string $filename) {
        $pdfResponse = $this->buildPdfResponse();
        $pdfResponse->save($dir, $filename);
    }
}