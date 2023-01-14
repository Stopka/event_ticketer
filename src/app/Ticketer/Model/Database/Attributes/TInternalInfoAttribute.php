<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Attributes;

use Doctrine\ORM\Mapping as ORM;
use JsonException;

trait TInternalInfoAttribute
{
    /**
     * @ORM\Column(type="text",nullable=true)
     * @var string|null
     */
    private $internalInfo;

    /**
     * @return array<string,mixed>
     * @throws JsonException
     */
    public function getInternalInfo(): ?array
    {
        return json_decode((string)$this->internalInfo, true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @param array<string,mixed>|null $internalInfo
     * @throws JsonException
     */
    public function setInternalInfo(?array $internalInfo): void
    {
        $this->internalInfo = json_encode($internalInfo, JSON_THROW_ON_ERROR);
    }

    /**
     * @param string $key
     * @return mixed|null
     */
    public function getInternalInfoItem(string $key)
    {
        $info = $this->getInternalInfo();
        if (null === $info || !array_key_exists($key, $info)) {
            return null;
        }

        return $info[$key];
    }

    /**
     * @param string $key
     * @param mixed $value
     * @throws JsonException
     */
    public function setInternalInfoItem(string $key, $value): void
    {
        $info = $this->getInternalInfo();
        if (null === $info) {
            $info = [];
        }
        $info[$key] = $value;
        $this->setInternalInfo($info);
    }
}
