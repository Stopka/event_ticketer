<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 20.1.17
 * Time: 20:18
 */

namespace App\Model;

use App\Model\Persistence\Entity\ApplicationEntity;
use App\Model\Persistence\Entity\IEntity;
use Nette\Mail\Message;
use Nette\SmartObject;
use Nette\Utils\Strings;

class SimpleApplicationPdfManager implements IApplicationPdfManager {
    use SmartObject;
    const PATH_BASE = '/SimplePdfApplications';
    const PATH_SOURCES = '/Sources';
    const PATH_DESTINATIONS = '/Destinations';

    /** @var  FileStorageFactory */
    private $fileStorageFactory;

    /** @var FileStorage */
    private $sourceStorage;

    /** @var FileStorage */
    private $destinationStorage;

    /** @var string */
    private $sourcePath;

    /** @var string */
    private $basePath;


    /** @var string */
    private $destinationPath;

    public function __construct(
        FileStorageFactory $fileStorageFactory,
        string $basePath = self::PATH_BASE,
        string $sourceSubPath = self::PATH_SOURCES,
        string $destinationSubPath = self::PATH_DESTINATIONS
    ) {
        $this->fileStorageFactory = $fileStorageFactory;
        $this->basePath = $basePath;
        $this->sourcePath = $sourceSubPath;
        $this->destinationPath = $destinationSubPath;
    }

    /**
     * @return FileStorage
     */
    public function getSourceStorage(): FileStorage {
        if (!$this->sourceStorage) {
            $path = $this->basePath . $this->sourcePath;
            $this->sourceStorage = $this->fileStorageFactory->create($path);
        }
        return $this->sourceStorage;
    }

    /**
     * @return FileStorage
     */
    public function getDestinationStorage(): FileStorage {
        if (!$this->destinationStorage) {
            $path = $this->basePath . $this->destinationPath;
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
        $filePath = $file_path = $this->getGeneratedApplicationPdfPath($applicationEntity);
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
    protected function getSourcePdfFilePath(ApplicationEntity $applicationEntity) {
        $id = $this->getIdString($applicationEntity->getEvent());
        $path_extension = '';
        foreach ($applicationEntity->getChoices() as $choice) {
            if ($choice->getOption()->getAddition()->getName() == "Doprava") {
                if ($choice->getOption()->getName() == "Autobus") {
                    $path_extension .= '_bus';
                }
                if ($choice->getOption()->getName() == "Individuální") {
                    $path_extension .= '_individual';
                }
            }
        }
        $path = '/' . $id . $path_extension . '.pdf';
        return $this->getSourceStorage()->getFullPath($path);
    }

    /**
     * @param ApplicationEntity $applicationEntity
     * @return string
     */
    protected function getDestinationPdfFilePath(ApplicationEntity $applicationEntity) {
        $path = '/' . $this->getIdString($applicationEntity) . '.pdf';
        return $this->getDestinationStorage()->getFullPath($path);
    }

    public function createPdf(ApplicationEntity $application) {
        $source = file_get_contents($this->getSourcePdfFilePath($application));
        $dest = str_replace('(888)', '(' . Strings::padLeft($application->getId(), 3, '0') . ')', $source);
        file_put_contents($this->getDestinationPdfFilePath($application), $dest);
    }

}