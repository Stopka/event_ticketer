<?php

declare(strict_types=1);

namespace Ticketer\Model\Notifiers;

trait TEmailService
{
    /** @var  EmailService */
    private $emailService;

    /**
     * @param EmailService $emailService
     */
    protected function injectEmailService(EmailService $emailService): void
    {
        $this->emailService = $emailService;
    }

    protected function getEmailService(): EmailService
    {
        return $this->emailService;
    }
}
