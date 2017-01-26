<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 20.1.17
 * Time: 20:18
 */

namespace App\Model\Facades;


use App\Model\Entities\ApplicationEntity;

class PdfApplicationFacade extends BaseFacade {

    /**
     * @var string
     */
    private $dir;

    public function __construct($dir) {
        $this->dir = $dir;
        $this->createDirs();
    }


    public function getPdfPath(ApplicationEntity $application) {

    }

    private function createDirs(){
        $this->createDir($this->getBasePath());
        $this->createDir($this->getSourcePath());
        $this->createDir($this->getDestinationPath());
    }

    private function createDir($dir){
        if (!is_dir($dir)) {
            umask(0);
            mkdir($dir, 0777);
        }
    }

    /**
     * @return string
     */
    private function getBasePath(){
        return $this->dir.'/pdf_applications';
    }

    /**
     * @return string
     */
    private function getSourcePath(){
        return $this->getBasePath().'/sources';
    }

    /**
     * @return string
     */
    private function getDestinationPath(){
        return $this->getBasePath().'/destinations';
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