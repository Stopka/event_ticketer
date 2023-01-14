<?php

declare(strict_types=1);

namespace Ticketer\Responses\PdfResponse;

use Nette\Application\UI\ITemplate;

/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 3.1.18
 * Time: 16:24
 */
interface PdfResponseFactoryInterface
{
    /**
     * @param ITemplate|string $source
     * @return PdfResponse
     */
    public function create($source): PdfResponse;
}
