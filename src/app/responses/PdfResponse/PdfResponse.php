<?php

namespace App\Responses\PdfResponse;

use Nette\Bridges\ApplicationLatte\Template;

/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 3.1.18
 * Time: 16:24
 */
class PdfResponse extends \Joseki\Application\Responses\PdfResponse {

    /** @var PdfResponseDependencies */
    private $dependencies;

    /**
     * PdfResponse constructor.
     * @param string $tempDir
     * @param Template|string $source
     */
    public function __construct(PdfResponseDependencies $dependencies, $source) {
        parent::__construct($source);
        $this->dependencies = $dependencies;
    }


    protected function getMPDFConfig() {
        $result = parent::getMPDFConfig();
        $result['tempDir'] = $this->dependencies->getTempDir();
        return $result;
    }

}