<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 24.1.17
 * Time: 23:32
 */

namespace App\AdminModule\Responses;


use App\Model\Entities\ApplicationEntity;
use App\Model\Entities\EventEntity;
use Nette;
use Nette\Application\IResponse;
use Stopka\TableExporter\ExportResponse;

class ApplicationsExportResponse implements IResponse {

    /** @var EventEntity[] */
    private $eventEntity;

    /** @var  ApplicationEntity[] */
    private $applications;

    /**
     * ApplicationsExportResponse constructor.
     * @param EventEntity $eventEntity
     * @param ApplicationEntity[] $applications
     */
    public function __construct(EventEntity $eventEntity, $applications) {
        $this->eventEntity = $eventEntity;
        $this->applications = $applications;
    }

    function send(Nette\Http\IRequest $httpRequest, Nette\Http\IResponse $httpResponse) {
        $response = new ExportResponse($this->applications,ExportResponse::EXPORT_FORMAT_CSV);
        $response->setFilenameWithDate('přihlášky-');
        $response->addColumn('order_id','Číslo objednávky')
            ->setCustomRenderer(function(ApplicationEntity $applicaiton){
                return $applicaiton->getOrder()->getId();
            });
        $response->addColumn('order_email','Email')
            ->setCustomRenderer(function(ApplicationEntity $applicaiton){
                return $applicaiton->getOrder()->getEmail();
            });
        $response->addColumn('order_firstName','Jméno rodiče')
            ->setCustomRenderer(function(ApplicationEntity $applicaiton){
                return $applicaiton->getOrder()->getFirstName();
            });
        $response->addColumn('order_lastName','Příjmení rodiče')
            ->setCustomRenderer(function(ApplicationEntity $applicaiton){
                return $applicaiton->getOrder()->getLastName();
            });
        $response->addColumn('order_phone','Telefon')
            ->setCustomRenderer(function(ApplicationEntity $applicaiton){
                return $applicaiton->getOrder()->getPhone();
            });
        $response->addColumn('order_created','Vytvoření objednávky')
            ->setCustomRenderer(function(ApplicationEntity $applicaiton){
                return $applicaiton->getOrder()->getCreated()->format('Y-m-d H:i:s');
            });
        $response->addColumn('id','Id přihlášky')
            ->setCustomRenderer(function(ApplicationEntity $applicaiton){
                return $applicaiton->getId();
            });
        $response->addColumn('state','Stav')
            ->setCustomRenderer(function(ApplicationEntity $applicaiton){
                $states = [
                    ApplicationEntity::STATE_CANCELLED => 'Zrušeno',
                    ApplicationEntity::STATE_WAITING => 'Nové',
                    ApplicationEntity::STATE_RESERVED => 'Rezervováno',
                    ApplicationEntity::STATE_FULFILLED => 'Splněno'
                ];
                return $states[$applicaiton->getState()];
            });
        $response->addColumn('firstName','Jméno')
            ->setCustomRenderer(function(ApplicationEntity $applicaiton){
                return $applicaiton->getFirstName();
            });
        $response->addColumn('lastName','Příjmení')
            ->setCustomRenderer(function(ApplicationEntity $applicaiton){
                return $applicaiton->getLastName();
            });
        $response->addColumn('birthDate','Datum narození')
            ->setCustomRenderer(function(ApplicationEntity $applicaiton){
                $date = $applicaiton->getBirthDate();
                return $date?$date->format('Y-m-d'):'';
            });
        $response->addColumn('birthIdDate','Datum narození r. č.')
            ->setCustomRenderer(function(ApplicationEntity $applicaiton){
                return $applicaiton->getBirthIdDate();
            });
        $response->addColumn('birthCode','Kod rodného čísla')
            ->setCustomRenderer(function(ApplicationEntity $applicaiton){
                return $applicaiton->getBirthCode();
            });
        $response->addColumn('address','Adresa')
            ->setCustomRenderer(function(ApplicationEntity $applicaiton){
                return $applicaiton->getAddress();
            });
        $response->addColumn('city','Město')
            ->setCustomRenderer(function(ApplicationEntity $applicaiton){
                return $applicaiton->getCity();
            });
        $response->addColumn('zip','PSČ')
            ->setCustomRenderer(function(ApplicationEntity $applicaiton){
                return $applicaiton->getZip();
            });
        $response->addColumn('gender','Pohlaví')
            ->setCustomRenderer(function(ApplicationEntity $applicaiton){
                if($applicaiton->getGender()===NULL){
                    return '';
                }
                return $applicaiton->getGender()?'Žena':'Muž';
            });
        foreach ($this->eventEntity->getAdditions() as $addition){
            $response->addColumn('a'.$addition->getId(),$addition->getName())
                ->setCustomRenderer(function(ApplicationEntity $application) use ($addition){
                    $result = '';
                    $choices = $application->getChoices();
                    foreach ($choices as $choice){
                        if($choice->getOption()->getAddition()->getId() == $addition->getId()){
                            $result.=$choice->getOption()->getName().($choice->isPayed()?' (OK)':' (!!)').';';
                        }
                    }
                    return $result;
                });
        }
        return $response->send($httpRequest,$httpResponse);
    }


}