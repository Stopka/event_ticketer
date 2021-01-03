<?php

declare(strict_types=1);

namespace Ticketer\Responses\SpreadsheetResponse;

use ArrayAccess;
use Nette\Application\IResponse;
use Nette\Http\IRequest;
use Nette\Http\IRequest as HttpRequest;
use Nette\Http\IResponse as HttpResponse;
use Nette\Localization\ITranslator;
use Nette\SmartObject;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf;
use Ticketer\Utils\Charset\CharsetConverter;
use Ticketer\Utils\Charset\CharsetEnum;
use Traversable;

/**
 * @author   stopka
 */
class SpreadsheetResponse implements IResponse
{
    use SmartObject;

    protected Spreadsheet $spreadsheet;

    /** @var Column[] */
    protected array $columnsOrder = [];

    /** @var Column[] */
    protected array $columns = [];

    /** @var CharsetConverter */
    protected CharsetConverter $charset;

    /** @var iterable<mixed> */
    protected iterable $dataSource;

    /** @var  string */
    protected string $filename;

    /** @var  string */
    protected string $delimiter = ',';

    protected FormatEnum $format;

    private ITranslator $translator;

    /**************************************/
    /**         Public functions         **/
    /**************************************/

    /**
     *
     * @param iterable<mixed> $dataSource
     * @param FormatEnum|null $format
     */
    public function __construct(iterable $dataSource, ?FormatEnum $format = null, ITranslator $translator)
    {
        $this->dataSource = $dataSource;
        $this->setFormat($format);
        $this->setCharset();
        $this->setFilenameWithDate();
        $this->spreadsheet = new Spreadsheet();
        $this->translator = $translator;
    }

    /**
     * @return ITranslator
     */
    public function getTranslator(): ITranslator
    {
        return $this->translator;
    }

    /**
     * @param FormatEnum|null $format
     */
    public function setFormat(?FormatEnum $format = null): void
    {
        if (null === $format) {
            $format = FormatEnum::CSV();
        }
        /** @var FormatEnum $format */
        $this->format = $format;
    }

    /**
     * Sets output file name
     * @param string $filename
     * @return $this
     */
    public function setFilename(string $filename): self
    {
        $this->filename = $filename;

        return $this;
    }

    /**
     * can be used to define which delimiter will be used in the CSV format.
     * Only has effect when the instance of this object has been created
     * with the format set to the class static variable EXPORT_FORMAT_CSV, or
     * the string 'csv'.
     * @param string $delimiter
     */
    public function setColumnDelimiter(string $delimiter = ','): void
    {
        $this->delimiter = $delimiter;
    }

    /**
     * Sets filename in format $prefixDate($date_format)$suffix
     * poskytuje fluent interface
     * @param string $prefix
     * @param string $dateFormat
     * @return $this
     */
    public function setFilenameWithDate($prefix = '', $dateFormat = 'Y-m-d-H-i-s'): self
    {
        $this->setFilename($prefix . date($dateFormat) . '.' . strtolower($this->format->getValue()));

        return $this;
    }

    /**
     * Sets charset of output file
     * @param CharsetEnum|null $charset defaults to utf-8
     * @return $this
     */
    public function setCharset(?CharsetEnum $charset = null): self
    {
        $this->charset = new CharsetConverter($charset ?? CharsetEnum::UTF_8());

        return $this;
    }

    /**
     * Adds new column
     * @param string $key
     * @param string $caption
     * @return Column
     * @throws SettingException
     */
    public function addColumn(string $key, string $caption): Column
    {
        $column = new Column($key, $caption, $this);
        if (isset($this->columns[$key])) {
            throw new SettingException("Duplicate column.");
        }
        $this->columnsOrder[] = $column;
        $this->columns[$key] = $column;

        return $column;
    }

    /**
     * Sends export as http response
     * @param HttpRequest $httpRequest
     * @param HttpResponse $httpResponse
     * @throws Exception
     */
    public function send(IRequest $httpRequest, HttpResponse $httpResponse): void
    {
        $this->createFileContent();
        $this->setResponseHeaders($httpResponse);
        IOFactory::registerWriter(FormatEnum::PDF()->getValue(), Mpdf::class);
        $writer = IOFactory::createWriter($this->spreadsheet, $this->format->getValue());
        $writer->save('php://output');
    }

    /**
     * Method is used to set various metadata of the generated document.
     * @param string $creator name of the document creator
     * @param string $title
     * @param string $subject
     * @param string $description
     * @param string $keywords
     * @param string $category
     */
    public function setMetaData(
        string $creator = '',
        string $title = '',
        string $subject = '',
        string $description = '',
        string $keywords = '',
        string $category = ''
    ): void {
        $this->spreadsheet->getProperties()
            ->setCreator($creator)
            ->setLastModifiedBy($creator)
            ->setTitle($title)
            ->setSubject($subject)
            ->setDescription($description)
            ->setKeywords($keywords)
            ->setCategory($category);
    }

    /**************************************/
    /**       Protected functions        **/
    /**************************************/

    /**
     * Builds spreadsheet  model in memmory
     */
    protected function createFileContent(): void
    {
        $this->addHeaders();
        $this->addBody();
        $this->autoSizeColumns();
    }

    /**
     * @param HttpResponse $httpResponse
     */
    private function setResponseHeaders(HttpResponse $httpResponse): void
    {
        $httpResponse->setContentType($this->getMimeType(), $this->charset->getCharset()->getValue());
        $httpResponse->setHeader('Content-Description', 'File Transfer');
        $httpResponse->setHeader('Content-Disposition', 'attachment; filename=' . $this->filename);
        $httpResponse->setHeader('Content-Transfer-Encoding', 'binary');
        $httpResponse->setHeader('Expires', '0');
        $httpResponse->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0');
        $httpResponse->setHeader('Pragma', 'public');
    }

    private function getMimeType(): string
    {
        switch ($this->format) {
            default:
            case FormatEnum::CSV():
                return 'text/csv';
            case FormatEnum::XLS():
                return 'application/vnd.ms-excel';
            case FormatEnum::XLSX():
                return 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
            case FormatEnum::ODS():
                return 'application/vnd.oasis.opendocument.spreadsheet';
            case FormatEnum::HTML():
                return 'text/html';
            case FormatEnum::PDF():
                return 'application/pdf';
        }
    }

    private function addHeaders(): void
    {
        $sheet = $this->spreadsheet->getActiveSheet();
        $idx = 1;
        foreach ($this->columnsOrder as $column) {
            $coordinates = Coordinate::stringFromColumnIndex($idx) . "1";
            $sheet->setCellValue($coordinates, $column->getCaption());
            $idx++;
        }
    }

    private function addBody(): void
    {
        $sheet = $this->spreadsheet->getActiveSheet();
        $idy = 2;
        foreach ($this->dataSource as $row) {
            $idx = 1;
            foreach ($this->columnsOrder as $column) {
                $coordinates = Coordinate::stringFromColumnIndex($idx) . "" . $idy;
                $sheet->setCellValueExplicit(
                    $coordinates,
                    $column->createContent($row),
                    $column->getDataType()->getValue()
                );
                if (null !== $column->getCellFormat()) {
                    $sheet->getStyle($coordinates)
                        ->getNumberFormat()
                        ->setFormatCode($column->getCellFormat());
                }
                $idx++;
            }
            $idy++;
        }
    }

    private function autoSizeColumns(): void
    {
        //Font::setAutoSizeMethod(Font::AUTOSIZE_METHOD_EXACT);
        $sheet = $this->spreadsheet->getActiveSheet();
        foreach (range('A', $sheet->getHighestDataColumn()) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        $sheet->calculateColumnWidths();
    }
}
