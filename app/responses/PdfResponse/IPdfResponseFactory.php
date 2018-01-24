<?php

namespace App\Responses\PdfResponse;

use Nette\Bridges\ApplicationLatte\Template;

/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 3.1.18
 * Time: 16:24
 */
interface IPdfResponseFactory {

    /**
     * @param Template|string $source
     * @return PdfResponse
     */
    public function create($source): PdfResponse;

}