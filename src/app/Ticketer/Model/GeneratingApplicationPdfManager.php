<?php

declare(strict_types=1);

namespace Ticketer\Model;

use Ticketer\Model\Database\Entities\ApplicationEntity;
use Ticketer\Model\Database\Entities\EntityInterface;
use Nette\Mail\Message;
use Nette\SmartObject;
use Nette\Utils\Strings;
use Ticketer\Modules\FrontModule\Responses\ApplicationPdfResponse;

class GeneratingApplicationPdfManager implements IApplicationPdfManager
{
    use SmartObject;

    protected const PATH_BASE = '/GeneratingPdfApplications';

    /** @var  FileStorageFactory */
    private $fileStorageFactory;

    /** @var FileStorage|null */
    private $destinationStorage;

    /** @var string */
    private $basePath;

    /** @var ApplicationPdfResponse */
    private $applicationPdfResponse;

    public function __construct(
        FileStorageFactory $fileStorageFactory,
        ApplicationPdfResponse $applicationPdfResponse,
        string $basePath = self::PATH_BASE
    ) {
        $this->fileStorageFactory = $fileStorageFactory;
        $this->basePath = $basePath;
        $this->applicationPdfResponse = $applicationPdfResponse;
    }

    /**
     * @return FileStorage
     */
    public function getDestinationStorage(): FileStorage
    {
        if (null === $this->destinationStorage) {
            $path = $this->basePath;
            $this->destinationStorage = $this->fileStorageFactory->create($path, true);
        }

        return $this->destinationStorage;
    }


    public function getGeneratedApplicationPdfPath(ApplicationEntity $application): string
    {
        $this->createPdf($application);

        return $this->getDestinationPdfFilePath($application);
    }

    public function addMessageAttachment(Message $message, ApplicationEntity $applicationEntity): void
    {
        $fileName = $this->getIdString($applicationEntity) . '.pdf';
        $filePath = $this->getGeneratedApplicationPdfPath($applicationEntity);
        $fileContent = @file_get_contents($filePath);
        if (false === $fileContent) {
            return;
        }
        $message->addAttachment($fileName, $fileContent);
    }

    protected function getIdString(EntityInterface $entity): string
    {
        return Strings::padLeft((string)$entity->getId(), 10, '0');
    }

    /**
     * @param ApplicationEntity $applicationEntity
     * @return string
     */
    protected function getDestinationPdfFilePath(ApplicationEntity $applicationEntity): string
    {
        $path = '/' . $this->getFileName($applicationEntity);

        return $this->getDestinationStorage()->getFullPath($path);
    }

    protected function getFileName(ApplicationEntity $applicationEntity): string
    {
        return $this->getIdString($applicationEntity) . '.pdf';
    }

    public function createPdf(ApplicationEntity $application): void
    {
        $response = $this->applicationPdfResponse;
        $response->setApplication($application);
        $filePath = $this->getDestinationStorage()->getFullPath('/' . $this->getFileName($application));
        $response->saveToFilePath($filePath);
    }
}
