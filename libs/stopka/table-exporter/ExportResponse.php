<?php

namespace Stopka\TableExporter;

/*
 * Based on implementation of CSVResponse.
 */
use Nette\Application\ApplicationException;
use PHPExcel;
use PHPExcel_Shared_Font;

/**
 * Renderer exportu dat do XLSX souboru
 *
 * @author stopka
 */
class ExportResponse extends \Nette\Object implements \Nette\Application\IResponse {
    
    const EXPORT_FORMAT_CSV = 'csv';
    const EXPORT_FORMAT_XLS = 'xls';
    const EXPORT_FORMAT_XLSX = 'xlsx';
    const formats=[
        self::EXPORT_FORMAT_CSV,
        self::EXPORT_FORMAT_XLS,
        self::EXPORT_FORMAT_XLSX
    ];
    
    /** @var PHPExcel **/
    protected $phpe;
    
    /** @var Column[] */
    protected $columns_order = Array();
    
    /** @var Column[] */
    protected $columns = Array();

    /** @var Charsets */
    protected $charset;
    
    /** @var array */
    protected $data_source;

    /** @var  \String */
    protected $filename;
    
    /** @var  \String */
    protected $delimiter = ',';
    
    /** @var string */
    protected $format;
    
    /**************************************/
    /**         Public functions         **/
    /**************************************/

    /**
     * 
     * @param array $data_source
     * @param string $format specifies which format the exporter will export to. Use the class constants with
     * prefix 'EXPORT_FORMAT_'.
     */
    public function __construct($data_source, $format = NULL) {
        if(!$format){
            $format = self::EXPORT_FORMAT_CSV;
        }
        $this->data_source = $data_source;
        if(!in_array($format,self::formats)){
            throw new ApplicationException(["Unsupported export format (%format%)",["format"=>$format]]);
        }
        $this->format = $format;
        $this->setCharset();
        $this->setFilenameWithDate();
        $this->phpe = new PHPExcel();
    }

    /**
     * Nastaví jméno souboru
     * @param \string $filename
     * @return ExportResponse $this
     */
    public function setFilename($filename) {
        $this->filename = $filename;
        return $this;
    }
    
    /**
     * can be used to define which delimiter will be used in the CSV format.
     * Only has effect when the instance of this object has been created
     * with the format set to the class static variable EXPORT_FORMAT_CSV, or
     * the string 'csv'.
     * @param type $delimiter
     */
    public function setColumnDelimiter($delimiter){
        $this->delimiter = $delimiter;
    }

    /**
     * Nastaví jméno souboru ve tvaru $prefixDate($date_format)$suffix
     * poskytuje fluent interface
     * @param string $prefix
     * @param string $date_format
     * @return ExportResponse
     */
    public function setFilenameWithDate($prefix = "", $date_format = 'Y-m-d_H-i-s') {
        $this->setFilename($prefix . date($date_format));
        return $this;
    }

    /**
     * Nastaví charset výstupního souboru
     * @param \string $charset
     * @return ExportResponse
     */
    public function setCharset($charset = "utf-8") {
        $this->charset = new Charsets($charset);
        return $this;
    }
    
    /**
     * Přidá do definice sloupec s daným klíčem
     * @param \string $key
     * @param \string $caption
     * @return Column
     * @throws \Nette\Application\ApplicationException pokud je již klíč obsazen
     */
    public function addColumn($key, $caption) {
        $column = new Column($key, $caption, $this);
        if (isset($this->columns[$key])) {
            throw new \Nette\Application\ApplicationException("Duplicate column.");
        }
        $this->columns_order[] = $column;
        $this->columns[$key] = $column;
        return $column;
    }

    /**
     * Odešle odpověď serveru v podobě csv souboru
     * @param \Nette\Http\IRequest $httpRequest
     * @param \Nette\Http\IResponse $httpResponse
     */
    public function send(\Nette\Http\IRequest $httpRequest, \Nette\Http\IResponse $httpResponse) {
        $this->createFileContent();        
        $this->setResponseHeaders($httpResponse);
        
        $objWriter = null;
        switch($this->format){
            default: 
            case 'csv' : 
                $objWriter = new \PHPExcel_Writer_CSV($this->phpe);
                $objWriter->setDelimiter($this->delimiter);
                break;
            case 'xls' : $objWriter = new \PHPExcel_Writer_Excel5($this->phpe); break;
            case 'xlsx': $objWriter = new \PHPExcel_Writer_Excel2007($this->phpe); break;
        }
        $objWriter->save('php://output');
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
    public function setMetaData($creator, $title, $subject, $description, $keywords, $category){
        $this->phpe->getProperties()->setCreator($creator)
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
     * Vytvoří datový soubor v paměti
     */
    protected function createFileContent() {
        $this->addHeaders();
        $this->addBody();
        $this->autoSizeColumns();
    }
    
    /**************************************/
    /**         Private functions        **/
    /**************************************/
    
    private function setResponseHeaders(\Nette\Http\IResponse $httpResponse){
        switch($this->format){
            default:
            case self::EXPORT_FORMAT_CSV : $httpResponse->setContentType('text/csv', $this->charset->getName()); break;
            case self::EXPORT_FORMAT_XLS :
            case self::EXPORT_FORMAT_XLSX: $httpResponse->setContentType('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $this->charset->getName()); break;
        }
        
        $httpResponse->setHeader('Content-Description', 'File Transfer');
        $httpResponse->setHeader('Content-Disposition', 'attachment; filename=' . $this->filename.'.'.$this->format);
        $httpResponse->setHeader('Content-Transfer-Encoding', 'binary');
        $httpResponse->setHeader('Expires', 0);
        $httpResponse->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0');
        $httpResponse->setHeader('Pragma', 'public');
    }
    
    private function addHeaders(){
        $idx = 0;
        foreach ($this->columns_order as $column) {
            $this->phpe->getActiveSheet()->setCellValue(\PHPExcel_Cell::stringFromColumnIndex($idx++)."1", $column->getCaption());
        }
    }
    
    private function addBody(){       
        $idy = 2;       
        foreach ($this->data_source as $row) {
            $idx = 0;
            foreach ($this->columns_order as $column) {
                $this->phpe->getActiveSheet()->setCellValue(\PHPExcel_Cell::stringFromColumnIndex($idx++)."".$idy, $column->createContent($row));
            }
            $idy++;
        }
        
    }
    
    private function autoSizeColumns(){
        \PHPExcel_Shared_Font::setAutoSizeMethod(PHPExcel_Shared_Font::AUTOSIZE_METHOD_EXACT);
        foreach (range('A', $this->phpe->getActiveSheet()->getHighestDataColumn()) as $col) {
            $this->phpe->getActiveSheet()
                    ->getColumnDimension($col)
                    ->setAutoSize(true);
        }
        $this->phpe->getActiveSheet()->calculateColumnWidths();
    }

}


