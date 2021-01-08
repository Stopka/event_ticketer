<?php

declare(strict_types=1);

namespace Ticketer\Modules\FrontModule\Responses;

use Ticketer\Model\DateFormatter;
use Ticketer\Model\Database\Entities\ApplicationEntity;
use Ticketer\Responses\PdfResponse\PdfResponseFactoryInterface;
use Ticketer\Responses\PdfResponse\PdfResponse;
use Nette\Application\IResponse;
use Nette\Application\UI\ITemplate;
use Nette\Application\UI\ITemplateFactory;
use Nette\Http\IRequest;
use Nette\Utils\Strings;

class ApplicationPdfResponse implements IResponse
{

    /** @var PdfResponseFactoryInterface */
    private $pdfResponseFactory;

    /** @var ITemplateFactory */
    private $templateFactory;

    /** @var ITemplate|null */
    private ?ITemplate $template = null;

    /** @var DateFormatter */
    private $dateFormatter;

    /** @var string */
    private $saveMode = PdfResponse::INLINE;

    public function __construct(
        PdfResponseFactoryInterface $pdfResponseFactory,
        DateFormatter $dateFormatter,
        ITemplateFactory $templateFactory
    ) {
        $this->pdfResponseFactory = $pdfResponseFactory;
        $this->templateFactory = $templateFactory;
        $this->dateFormatter = $dateFormatter;
    }

    protected function createTemplate(): ITemplate
    {
        return $this->templateFactory->createTemplate();
    }

    protected function getTemplate(): ITemplate
    {
        if (null === $this->template) {
            $this->template = $this->createTemplate();
        }

        return $this->template;
    }


    /** @var  ApplicationEntity */
    private $application;

    public function setApplication(ApplicationEntity $application): void
    {
        $this->application = $application;
    }

    protected function buildTemplate(): ITemplate
    {
        $template = $this->getTemplate();
        $template->setFile(__DIR__ . '/ApplicationPdfResponse.latte');
        $template->baseDir = __DIR__;
        $application = $this->application;
        $bus = null;
        $tricko = null;
        foreach ($application->getChoices() as $choice) {
            $option = $choice->getOption();
            if (null === $option) {
                continue;
            }
            $addition = $option->getAddition();
            if (null === $addition) {
                continue;
            }
            if ('Doprava' === $addition->getName()) {
                if ('Autobus' === $option->getName()) {
                    $bus = true;
                }
                if ('Individuální' === $option->getName()) {
                    $bus = false;
                }
            }
            if ('Tričko' === $addition->getName()) {
                $tricko = $option->getName();
            }
        }
        $address = [];
        if (null !== $application->getAddress()) {
            $address[] = $application->getAddress();
        }
        if (null !== $application->getCity()) {
            $address[] = $application->getCity();
        }
        if (null !== $application->getZip()) {
            $address[] = $application->getZip();
        }
        $template->application = $application;
        $template->bus = $bus;
        $template->tricko = $tricko;
        $template->address = implode('; ', $address);
        $template->birth = null !== $application->getBirthDate() ? $this->dateFormatter->getDateString(
            $application->getBirthDate()
        ) : null;
        $template->id = Strings::padLeft((string)$this->application->getId(), 3, '0');

        return $template;
    }

    protected function buildPdfResponse(): PdfResponse
    {
        $template = $this->buildTemplate();
        $pdf = $this->pdfResponseFactory->create($template);
        $pdf->setSaveMode($this->getSaveMode());
        $pdf->setPageFormat("A4");
        $title = "Application form " . (string)$this->application->getId();
        $pdf->setDocumentTitle($title);
        $pdf->setDocumentAuthor("ldtpardubice.cz");
        $pdf->setPageMargins("13,13,13,13,10,10");
        $mpdf = $pdf->getMPDF();
        $mpdf->SetFooter("<a href='https://ldtpardubice.cz'>ldtpardubice.cz</a>");

        return $pdf;
    }

    /**
     * @return string
     */
    public function getSaveMode(): string
    {
        return $this->saveMode;
    }

    /**
     * @param string $saveMode
     */
    public function setSaveMode(string $saveMode): void
    {
        $this->saveMode = $saveMode;
    }

    /**
     * @param IRequest $httpRequest
     * @param \Nette\Http\IResponse $httpResponse
     */
    public function send(IRequest $httpRequest, \Nette\Http\IResponse $httpResponse): void
    {
        $pdfResponse = $this->buildPdfResponse();
        $pdfResponse->send($httpRequest, $httpResponse);
    }

    /**
     * @param string $dir
     * @param string $filename
     */
    public function save(string $dir, string $filename): void
    {
        $pdfResponse = $this->buildPdfResponse();
        $pdfResponse->save($dir, $filename);
    }

    /**
     * Saves pdf into file without tampering the file name
     * @param string $filePath
     */
    public function saveToFilePath(string $filePath): void
    {
        $pdfResponse = $this->buildPdfResponse();
        file_put_contents($filePath, (string)$pdfResponse);
    }
}
