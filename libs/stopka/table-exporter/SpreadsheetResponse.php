<?php

namespace Stopka\TableExporter;

/*
 * Exports data to spreadsheet
 */
use Nette\Application\IResponse;
use Nette\Http\IRequest;
use Nette\SmartObject;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf;

/**
 * Renderer exportu dat do XLSX souboru
 *
 * @author stopka
 */
class SpreadsheetResponse implements IResponse {
    use SmartObject;

    const EXPORT_FORMAT_CSV = 'Csv';
    const EXPORT_FORMAT_XLS = 'Xls';
    const EXPORT_FORMAT_XLSX = 'Xlsx';
    const EXPORT_FORMAT_ODS = 'Ods';
    const EXPORT_FORMAT_HTML = 'Html';
    const EXPORT_FORMAT_PDF = 'Pdf';

    public static $formats = [
        self::EXPORT_FORMAT_CSV => 'CSV',
        self::EXPORT_FORMAT_XLS => 'XLS',
        self::EXPORT_FORMAT_XLSX => 'XLSX',
        self::EXPORT_FORMAT_ODS => 'ODS',
        self::EXPORT_FORMAT_HTML => 'HTML',
        self::EXPORT_FORMAT_PDF => 'PDF'
    ];

    /** @var Spreadsheet */
    protected $spreadsheet;

    /** @var Column[] */
    protected $columns_order = Array();

    /** @var Column[] */
    protected $columns = Array();

    /** @var Charsets */
    protected $charset;

    /** @var \Nette\Database\Table\Selection */
    protected $data_source;

    /** @var  \String */
    protected $filename;

    /** @var  \String */
    protected $delimiter = ',';

    /** @var boolean */
    protected $format;

    /**************************************/
    /**         Public functions         **/
    /**************************************/

    /**
     *
     * @param iterable $data_source
     * @param string $format specifies which format the exporter will export to. Use the class constants with
     * prefix 'EXPORT_FORMAT_'.
     * @throws SettingException
     */
    public function __construct(iterable $data_source, $format = self::EXPORT_FORMAT_CSV) {
        $this->data_source = $data_source;
        $this->setFormat($format);
        $this->format = $format;
        $this->setCharset();
        $this->setFilenameWithDate();
        $this->spreadsheet = new Spreadsheet();
    }

    /**
     * @param string $format
     * @throws SettingException
     */
    public function setFormat(string $format = self::EXPORT_FORMAT_CSV): void {
        if (!in_array($format, array_keys(self::$formats))) {
            throw new SettingException(["Unsupported export format (%format%)", ["format" => $format]]);
        }
        $this->format = $format;
    }

    /**
     * Sets output file name
     * @param \string $filename
     * @return $this
     */
    public function setFilename($filename): self {
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
    public function setColumnDelimiter(string $delimiter = ','): void {
        $this->delimiter = $delimiter;
    }

    /**
     * Sets filename in format $prefixDate($date_format)$suffix
     * poskytuje fluent interface
     * @param string $prefix
     * @param string $date_format
     * @return $this
     */
    public function setFilenameWithDate($prefix = "", $date_format = 'Y-m-d_H-i-s'): self {
        $this->setFilename($prefix . date($date_format) . "." . strtolower($this->format));
        return $this;
    }

    /**
     * Sets charset of output file
     * @param \string $charset
     * @return $this
     */
    public function setCharset($charset = "utf-8"): self {
        $this->charset = new Charsets($charset);
        return $this;
    }

    /**
     * Adds new column
     * @param \string $key
     * @param \string $caption
     * @return Column
     * @throws SettingException
     */
    public function addColumn($key, $caption) {
        $column = new Column($key, $caption, $this);
        if (isset($this->columns[$key])) {
            throw new SettingException("Duplicate column.");
        }
        $this->columns_order[] = $column;
        $this->columns[$key] = $column;
        return $column;
    }

    /**
     * Sends export as http response
     * @param \Nette\Http\IRequest $httpRequest
     * @param \Nette\Http\IResponse $httpResponse
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function send(IRequest $httpRequest, \Nette\Http\IResponse $httpResponse) {
        $this->createFileContent();
        $this->setResponseHeaders($httpResponse);
        IOFactory::registerWriter(self::EXPORT_FORMAT_PDF, Mpdf::class);
        $writer = IOFactory::createWriter($this->spreadsheet, $this->format);
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
    public function setMetaData($creator, $title, $subject, $description, $keywords, $category) {
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
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    protected function createFileContent() {
        $this->addHeaders();
        $this->addBody();
        $this->autoSizeColumns();
    }

    /**************************************/
    /**         Private functions        **/
    /**************************************/

    /**
     * @param \Nette\Http\IResponse $httpResponse
     */
    private function setResponseHeaders(\Nette\Http\IResponse $httpResponse) {
        switch ($this->format) {
            default:
            case self::EXPORT_FORMAT_CSV :
                $httpResponse->setContentType('text/csv', $this->charset->getName());
                break;
            case self::EXPORT_FORMAT_XLS :
                $httpResponse->setContentType('application/vnd.ms-excel', $this->charset->getName());
                break;
            case self::EXPORT_FORMAT_XLSX:
                $httpResponse->setContentType('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $this->charset->getName());
                break;
            case self::EXPORT_FORMAT_ODS:
                $httpResponse->setContentType('application/vnd.oasis.opendocument.spreadsheet', $this->charset->getName());
                break;
            case self::EXPORT_FORMAT_HTML:
                $httpResponse->setContentType('text/html', $this->charset->getName());
                break;
            case self::EXPORT_FORMAT_PDF:
                $httpResponse->setContentType('application/pdf', $this->charset->getName());
                break;
        }

        $httpResponse->setHeader('Content-Description', 'File Transfer');
        $httpResponse->setHeader('Content-Disposition', 'attachment; filename=' . $this->filename);
        $httpResponse->setHeader('Content-Transfer-Encoding', 'binary');
        $httpResponse->setHeader('Expires', 0);
        $httpResponse->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0');
        $httpResponse->setHeader('Pragma', 'public');
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    private function addHeaders() {
        $sheet = $this->spreadsheet->getActiveSheet();
        $idx = 1;
        foreach ($this->columns_order as $column) {
            $coordinates = Coordinate::stringFromColumnIndex($idx) . "1";
            $sheet->setCellValue($coordinates, $column->getCaption());
            $idx++;
        }
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    private function addBody() {
        $sheet = $this->spreadsheet->getActiveSheet();
        $idy = 2;
        foreach ($this->data_source as $row) {
            $idx = 1;
            foreach ($this->columns_order as $column) {
                $coordinates = Coordinate::stringFromColumnIndex($idx) . "" . $idy;
                $sheet->setCellValue($coordinates, $column->createContent($row));
                $idx++;
            }
            $idy++;
        }
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    private function autoSizeColumns() {
        //Font::setAutoSizeMethod(Font::AUTOSIZE_METHOD_EXACT);
        $sheet = $this->spreadsheet->getActiveSheet();
        foreach (range('A', $sheet->getHighestDataColumn()) as $col) {
            $sheet->getColumnDimension($col)
                ->setAutoSize(true);
        }
        $sheet->calculateColumnWidths();
    }
}