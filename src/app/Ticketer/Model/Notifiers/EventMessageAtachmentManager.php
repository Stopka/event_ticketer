<?php

declare(strict_types=1);

namespace Ticketer\Model\Notifiers;

use Ticketer\Model\FileStorage;
use Ticketer\Model\FileStorageFactory;
use Ticketer\Model\Database\Entities\EventEntity;
use Nette\Mail\Message;
use Nette\SmartObject;
use Nette\Utils\Strings;

class EventMessageAtachmentManager
{
    use SmartObject;

    /** @var FileStorageFactory */
    private $fileStorageFactory;

    /** @var FileStorage|null */
    private $fileStorage;

    /** @var EventEntity */
    private $eventEntity;

    /** @var string */
    private $namespace;

    /** @var string */
    private $eventDir;

    public function __construct(
        FileStorageFactory $fileStorageFactory,
        EventEntity $eventEntity,
        string $namespace,
        string $eventDir = '/Attachments/Event'
    ) {
        $this->fileStorageFactory = $fileStorageFactory;
        $this->namespace = $namespace;
        $this->eventEntity = $eventEntity;
        $this->eventDir = $eventDir;
    }

    protected function getSubDir(): string
    {
        return $this->eventDir
            . '/' . Strings::padLeft((string)$this->eventEntity->getId(), 5, '0')
            . '/' . $this->namespace;
    }

    /**
     * @return FileStorage
     */
    public function getFileStorage(): FileStorage
    {
        if (null === $this->fileStorage) {
            $subdir = $this->getSubDir();
            $this->fileStorage = $this->fileStorageFactory->create($subdir, true);
        }

        return $this->fileStorage;
    }

    public function addAttachmentsToMessage(Message $message): void
    {
        foreach ($this->getFileStorage()->getAllFiles() as $filePath) {
            $message->addAttachment($filePath);
        }
    }
}
