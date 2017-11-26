<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 20.1.17
 * Time: 22:09
 */

namespace App\Model\Notifier;



trait TEmailService {
    /** @var  EmailService */
    private $emailService;

    /**
     * @param EmailService $emailService
     */
    protected function injectEmailService(EmailService $emailService): void {
        $this->emailService = $emailService;
    }

    protected function getEmailService(): EmailService {
        return $this->emailService;
    }
}