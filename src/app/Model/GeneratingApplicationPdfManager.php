<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 20.1.17
 * Time: 20:18
 */

namespace App\Model;

use App\FrontModule\Responses\ApplicationPdfResponse;
use App\Model\Persistence\Entity\ApplicationEntity;
use App\Model\Persistence\Entity\IEntity;
use Nette\Mail\Message;
use Nette\SmartObject;
use Nette\Utils\Strings;

class GeneratingApplicationPdfManager implements IApplicationPdfManager {
    use SmartObject;
    const PATH_BASE = '/GeneratingPdfApplications';

    /** @var  FileStorageFactory */
    private $fileStorageFactory;

    /** @var FileStorage */
    private $destinationStorage;

    /** @var string */
    private $basePath;

    /** @var ApplicationPdfResponse */
    private $applicationPdfResponse;

    public function __construct(
        FileStorageFactory $fileStorageFactory,
        string $basePath = self::PATH_BASE,
        ApplicationPdfResponse $applicationPdfResponse
    ) {
        $this->fileStorageFactory = $fileStorageFactory;
        $this->basePath = $basePath;
        $this->applicationPdfResponse = $applicationPdfResponse;
    }

    /**
     * @return FileStorage
     */
    public function getDestinationStorage(): FileStorage {
        if (!$this->destinationStorage) {
            $path = $this->basePath;
            $this->destinationStorage = $this->fileStorageFactory->create($path);
        }
        return $this->destinationStorage;
    }


    public function getGeneratedApplicationPdfPath(ApplicationEntity $application) {
        $this->createPdf($application);
        return $this->getDestinationPdfFilePath($application);
    }

    public function addMessageAttachment(Message $message, ApplicationEntity $applicationEntity) {
        $fileName = $this->getIdString($applicationEntity) . '.pdf';
        $filePath = $this->getGeneratedApplicationPdfPath($applicationEntity);
        $fileContent = @file_get_contents($filePath);
        $message->addAttachment($fileName, $fileContent);
    }

    protected function getIdString(IEntity $entity) {
        return Strings::padLeft($entity->getId(), 10, '0');
    }

    /**
     * @param ApplicationEntity $applicationEntity
     * @return string
     */
    protected function getDestinationPdfFilePath(ApplicationEntity $applicationEntity) {
        $path = '/' . $this->getFileName($applicationEntity);
        return $this->getDestinationStorage()->getFullPath($path);
    }

    protected function getFileName(ApplicationEntity $applicationEntity) {
        return $this->getIdString($applicationEntity) . '.pdf';
    }

    public function createPdf(ApplicationEntity $application) {
        $response = $this->applicationPdfResponse;
        $response->setApplication($application);
        $path = $this->getDestinationStorage()->getFullPath();
        $filename = $this->getFileName($application);
        $response->save($path, $filename);
    }

}