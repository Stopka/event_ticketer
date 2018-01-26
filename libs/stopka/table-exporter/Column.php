<?php

namespace Stopka\TableExporter;

    /*
     * To change this template, choose Tools | Templates
     * and open the template in the editor.
     */
use Nette\Localization\ITranslator;
use Nette\SmartObject;

/**
 * Sloupec v CSV souboru. Umožňuje nastait validační pravidla a ověřit je
 *
 * @author stopka
 */
class Column {
    use SmartObject;

    /** @var \string */
    protected $key;
    /** @var  \string */
    protected $caption;
    /** @var  \Nette\Application\IResponse */
    protected $parent;

    /** @var  \callback */
    protected $renderer;

    /** @var  ITranslator */
    protected $translator;

    protected $cellFormat;

    protected $dataType;

    /**
     * Tvorba sloupce
     * @param \string $key klíč sloupce
     * @param \string $caption Nadpis sloupce
     */
    public function __construct($key, $caption, \Nette\Application\IResponse $parent) {
        $this->key = $key;
        $this->caption = $caption;
        $this->parent = $parent;
        $this->setTextRenderer();
    }

    /**
     * Získá klíč sloupce
     * @return \string
     */
    public function getKey() {
        return $this->key;
    }

    /**
     * Vrátí nadpis sloupce přeložený dle nastavení translátoru
     * @return string
     */
    public function getCaption() {
        return $this->caption;
        if($this->translator===NULL){
            /** @var Translator $translator */
            $translator=$this->getContext()->getService('translator');
            return $translator->translate($this->caption);
        }

        if($this->translator===FALSE){
            return $this->caption;
        }
        return  $this->translator->translate($this->caption);
    }

    /**
     * Vrátí obsah buňky tabulky
     * @param $data
     * @return \string
     */
    public function createContent($data) {
        return call_user_func($this->renderer, (object)$data, $this);
    }

    /**
     * Nastaví tranlsator (null - výchozí, false vypne)
     * @param $translator ITranslator|NULL|FALSE
     */
    public function setTranslator($translator) {
        $this->translator=$translator;
    }

    /**
     * Vrátí obsah buňky z dodaných dat
     * @param $data
     * @return mixed
     */
    public function getColumnData($data) {
        return $data[$this->key];
    }

    /**
     * Rederer dat - vrací pouze text a nahrazuje null prázdným řetězcem
     * @param $data
     * @return string
     */
    private function renderText($data) {
        $col = $this->getColumnData($data);
        if ($col === NULL) {
            return "";
        }
        return $col;
    }

    /**
     * Rederer dat - vrací přeložený text
     * @param $data
     * @return string
     */
    private function renderTranslatedText($data) {
        return (string)$this->getColumnData($data);
    }

    /**
     * @return \Nette\DI\Container
     */
    protected function getContext() {
        return $this->parent->getContext();
    }

    /**
     * Rederer dat - vrací pouze text a nahrazuje null prázdným řetězcem
     * @param $data
     * @return string
     */
    private function renderSettingsFormatedDate($data) {
        $col = $this->getColumnData($data);
        if ($col === NULL) {
            return "";
        }
        /** @var Setting $setting */
        $setting=$this->getContext()->getService('setting');
        return $setting->formatDate($col);
    }

    /**
     * Rederer dat - vrací pouze text a nahrazuje null prázdným řetězcem
     * @param $data
     * @return string
     */
    private function renderSettingsFormatedDateTime($data) {
        $col = $this->getColumnData($data);
        if ($col === NULL) {
            return "";
        }

        /** @var Setting $setting */
        $setting=$this->getContext()->getService('setting');
        return $setting->formatDateTime($col);
    }

    /**
     * Nastaví text renderer
     * @return Column
     */
    public function setTextRenderer() {
        $this->renderer = [$this, "renderText"];
        return $this;
    }

    /**
     * Nastaví text renderer s automatickým překladem
     * @return Column
     */
    public function setTranslatedTextRenderer() {
        $this->renderer = [$this, "renderTranslatedText"];
        return $this;
    }

    /**
     * Nastaví date renderer formátující datum podle nastavení company
     * @return Column
     */
    public function setSettingsFormatedDateRenderer() {
        $this->renderer = [$this, "renderSettingsFormatedDate"];
        return $this;
    }

    /**
     * Nastaví date renderer formátující datum a čas podle nastavení company
     * @return Column
     */
    public function setSettingsFormatedDateTimeRenderer() {
        $this->renderer = [$this, "renderSettingsFormatedDateTime"];
        return $this;
    }

    /**
     * Nastaví vlastní renderer
     * @param callable $callback
     * @return Column
     */
    public function setCustomRenderer($callback) {
        $this->renderer = $callback;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCellFormat() {
        return $this->cellFormat;
    }

    /**
     * @param mixed $cellFormat
     */
    public function setCellFormat($cellFormat): self {
        $this->cellFormat = $cellFormat;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDataType() {
        return $this->dataType;
    }

    /**
     * @param mixed $dataType
     */
    public function setDataType($dataType): self {
        $this->dataType = $dataType;
        return $this;
    }
}


