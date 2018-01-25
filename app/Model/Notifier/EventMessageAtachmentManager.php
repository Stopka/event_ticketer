<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 24.1.18
 * Time: 23:50
 */

namespace App\Model\Notifier;


use App\Model\FileStorage;
use App\Model\FileStorageFactory;
use App\Model\Persistence\Entity\EventEntity;
use Nette\Mail\Message;
use Nette\SmartObject;
use Nette\Utils\Strings;

class EventMessageAtachmentManager {
    use SmartObject;

    /** @var FileStorageFactory */
    private $fileStorageFactory;

    /** @var FileStorage */
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

    protected function getSubDir(): string {
        return $this->eventDir . '/' . Strings::padLeft($this->eventEntity->getId(), 5, '0') . '/' . $this->namespace;
    }

    /**
     * @return FileStorage
     */
    public function getFileStorage(): FileStorage {
        if (!$this->fileStorage) {
            $subdir = $this->getSubDir();
            $this->fileStorage = $this->fileStorageFactory->create($subdir, true);
        }
        return $this->fileStorage;
    }

    public function addAttachmentsToMessage(Message $message) {
        foreach ($this->getFileStorage()->getAllFiles() as $filePath) {
            $message->addAttachment($filePath);
        }
    }

}