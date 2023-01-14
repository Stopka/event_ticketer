<?php

declare(strict_types=1);

namespace Ticketer\Modules\FrontModule\Responses;

use Ticketer\Model\Database\Entities\ApplicationEntity;

class ApplicationPdfResponseTemplate
{
    public function __construct(
        public string $baseDir,
        public string $id,
        public ApplicationEntity $application,
        public string $birth,
        public string $address,
        public string $tricko,
        public bool $bus,
    ) {
    }
}
