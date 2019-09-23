<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 20.1.17
 * Time: 20:18
 */

namespace App\Model;

use App\Model\Persistence\Entity\ApplicationEntity;
use Nette\Mail\Message;

interface IApplicationPdfManager {

    public function getGeneratedApplicationPdfPath(ApplicationEntity $application);

    public function addMessageAttachment(Message $message, ApplicationEntity $applicationEntity);

}