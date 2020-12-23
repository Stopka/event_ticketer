<?php

declare(strict_types=1);

namespace Ticketer\Responses\PdfResponse;

use Contributte\PdfResponse\PdfResponse as ContributePdfResponse;
use Nette\Bridges\ApplicationLatte\Template;

/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 3.1.18
 * Time: 16:24
 */
class PdfResponse extends ContributePdfResponse
{

    /** @var PdfResponseDependencies */
    private $dependencies;

    /**
     * PdfResponse constructor.
     * @param PdfResponseDependencies $dependencies
     * @param Template|string $source
     */
    public function __construct(PdfResponseDependencies $dependencies, $source)
    {
        parent::__construct($source);
        $this->dependencies = $dependencies;
    }

    /**
     * @return mixed[]
     */
    protected function getMPDFConfig(): array
    {
        $result = parent::getMPDFConfig();
        $result['tempDir'] = $this->dependencies->getTempDir();

        return $result;
    }
}
