<?php

declare(strict_types=1);

namespace Ticketer\Model;

use Ticketer\Model\Database\Entities\ApplicationEntity;
use Nette\Mail\Message;

interface IApplicationPdfManager
{
    public function getGeneratedApplicationPdfPath(ApplicationEntity $application): string;

    public function addMessageAttachment(Message $message, ApplicationEntity $applicationEntity): void;
}
