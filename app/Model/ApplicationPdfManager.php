<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 20.1.17
 * Time: 20:18
 */

namespace App\Model;

use App\Model\Persistence\Entity\ApplicationEntity;
use App\Model\Persistence\Entity\EventEntity;
use Nette\SmartObject;
use Nette\Utils\Strings;

class ApplicationPdfManager {
    use SmartObject;

    const PATH_BASE = '/pdf_applications';
    const PATH_SOURCES = '/sources';
    const PATH_DESTINATIONS = '/destinations';
    const PATH_OTHERS = '/others';
    const INFO_PDF_ITEM = 'pdfPathExtension';
    const INFO_FILES_ITEM = 'files';

    /** @var  FileStorage */
    private $fileStorage;

    public function __construct(FileStorage $fileStorage) {
        $this->fileStorage = $fileStorage;
        $this->createDirs();
    }

    /**
     * @param \App\Model\Persistence\Entity\EventEntity $event
     * @return string[]
     */
    public function getFilePaths(EventEntity $event){
        $files = $event->getInternalInfoItem(self::INFO_FILES_ITEM);
        if(!$files){
            $files = [];
        }
        $result = [];
        foreach ($files as $file){
            $result[] = $this->getFilePath($file);
        }
        return $result;
    }

    /**
     * @param $file string
     * @return string
     */
    protected function getFilePath($file){
        return $this->getOthersPath().$file;
    }


    public function getPdfPath(ApplicationEntity $application) {
        $this->createPdf($application);
        return $this->getDestinationPdfFilePath($application);
    }

    private function createDirs(){
        $this->fileStorage->createDir(self::PATH_BASE);
        $this->fileStorage->createDir(self::PATH_BASE.self::PATH_SOURCES);
        $this->fileStorage->createDir(self::PATH_BASE.self::PATH_DESTINATIONS);
        $this->fileStorage->createDir(self::PATH_BASE.self::PATH_OTHERS);
    }

    /**
     * @return string
     */
    private function getBasePath(){
        return $this->fileStorage->getFullPath(self::PATH_BASE);
    }

    /**
     * @return string
     */
    private function getSourcePath(){
        return $this->getBasePath().self::PATH_SOURCES;
    }

    /**
     * @return string
     */
    private function getDestinationPath(){
        return $this->getBasePath().self::PATH_DESTINATIONS;
    }

    /**
     * @return string
     */
    private function getOthersPath(){
        return $this->getBasePath().self::PATH_OTHERS;
    }

    /**
     * @param ApplicationEntity $applicationEntity
     * @return string
     */
    protected function getSourcePdfFilePath(ApplicationEntity $applicationEntity) {
        $path_extension = '';
        foreach ($applicationEntity->getChoices() as $choice){
            $info = $choice->getOption()->getInternalInfoItem(self::INFO_PDF_ITEM);
            if($info){
                $path_extension .= $info;
            }
        }
        return $this->getSourcePath().'/'.$applicationEntity->getCart()->getEvent()->getId().$path_extension.'.pdf';
    }

    /**
     * @param ApplicationEntity $applicationEntity
     * @return string
     */
    protected function getDestinationPdfFilePath(ApplicationEntity $applicationEntity) {
        return $this->getDestinationPath().'/'.$applicationEntity->getId().'.pdf';
    }

    public function createPdf(ApplicationEntity $application) {
        $source = file_get_contents($this->getSourcePdfFilePath($application));
        $dest = str_replace('(888)', '(' . Strings::padLeft($application->getId(),3,'0') . ')', $source);
        file_put_contents($this->getDestinationPdfFilePath($application), $dest);
    }

}