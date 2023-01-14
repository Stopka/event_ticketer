<?php

declare(strict_types=1);

namespace Ticketer\Modules\AdminModule\Responses;

interface IApplicationsExportResponseFactory
{
    public function create(): ApplicationsExportResponse;
}
