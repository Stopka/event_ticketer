<?php

declare(strict_types=1);

namespace Ticketer\Responses\SpreadsheetResponse;

use ArrayAccess;
use Nette\Localization\ITranslator;
use Nette\SmartObject;
use Ticketer\Responses\SpreadsheetResponse\Renderers\TextRenderer;

class Column
{
    use SmartObject;

    /** @var string */
    protected string $key;

    /** @var  string|object */
    protected $caption;

    protected SpreadsheetResponse $parent;

    /** @var  callable */
    protected $renderer;

    protected ITranslator $translator;

    protected ?string $cellFormat;

    protected DataTypeEnum $dataType;

    /**
     * @param string $key
     * @param string|object $caption
     * @param SpreadsheetResponse $parent
     */
    public function __construct(string $key, $caption, SpreadsheetResponse $parent)
    {
        $this->key = $key;
        $this->caption = $caption;
        $this->parent = $parent;
        $this->setTextRenderer();
        $this->dataType = DataTypeEnum::STRING();
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @return string
     */
    public function getCaption(): string
    {
        return $this->translator->translate($this->caption);
    }

    /**
     * @param mixed $data
     * @return string
     */
    public function createContent($data): string
    {
        return call_user_func($this->renderer, $data, $this);
    }

    /**
     * @param ArrayAccess<string,mixed> $data
     * @return mixed
     */
    public function getColumnData(ArrayAccess $data)
    {
        return $data[$this->key] ?? null;
    }

    /**
     * @return $this
     */
    public function setTextRenderer(): self
    {
        return $this->setRenderer(new TextRenderer($this));
    }

    /**
     * @param callable $callback
     * @return $this
     */
    public function setRenderer(callable $callback): self
    {
        $this->renderer = $callback;

        return $this;
    }

    public function getCellFormat(): ?string
    {
        return $this->cellFormat;
    }

    public function setCellFormat(?string $cellFormat): self
    {
        $this->cellFormat = $cellFormat;

        return $this;
    }

    public function getDataType(): DataTypeEnum
    {
        return $this->dataType;
    }

    public function setDataType(DataTypeEnum $dataType): self
    {
        $this->dataType = $dataType;

        return $this;
    }
}
