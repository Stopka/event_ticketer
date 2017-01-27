<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 20.1.17
 * Time: 20:18
 */

namespace App\Model\Facades;


use App\Model\Entities\ApplicationEntity;
use App\Model\FileStorage;

class PdfApplicationFacade extends BaseFacade {

    const PATH_BASE = '/pdf_applications';
    const PATH_SOURCES = '/sources';
    const PATH_DESTINATIONS = '/destinations';

    /** @var  FileStorage */
    private $fileStorage;

    public function __construct(FileStorage $fileStorage) {
        $this->fileStorage = $fileStorage;
        $this->createDirs();
    }


    public function getPdfPath(ApplicationEntity $application) {

    }

    private function createDirs(){
        $this->fileStorage->createDir(self::PATH_BASE);
        $this->fileStorage->createDir(self::PATH_BASE.self::PATH_SOURCES);
        $this->fileStorage->createDir(self::PATH_BASE.self::PATH_DESTINATIONS);
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
     * @param ApplicationEntity $applicationEntity
     * @return string
     */
    protected function getSourcePdfFilePath(ApplicationEntity $applicationEntity) {
        $path_extension = '';
        foreach ($applicationEntity->getChoices() as $choice){
            $info = $choice->getOption()->getInternalInfo();
            if($info&&isset($info['pdf'])){
                $path_extension .= $info['pdf'];
            }
        }
        return $this->getSourcePath().'/'.$applicationEntity->getOrder()->getEvent()->getId().$path_extension.'.pdf';
    }

    /**
     * @param ApplicationEntity $applicationEntity
     * @return string
     */
    protected function getDestinationPdfFilePath(ApplicationEntity $applicationEntity) {
        return $this->getDestinationPath().'/'.$applicationEntity->getId().'.pdf';
    }

    public function createPdf(ApplicationEntity $application) {
        $source = file_get_contents($this->getSourcePath());
        $dest = str_replace('(888)', '(' . $application->getId() . ')', $source);
        file_put_contents($this->getDestinationPdfFilePath($application), $dest);
    }

}