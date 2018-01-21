<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 24.1.17
 * Time: 23:32
 */

namespace App\AdminModule\Responses;


use App\Model\Persistence\Entity\AdditionEntity;
use App\Model\Persistence\Entity\ApplicationEntity;
use App\Model\Persistence\Entity\EventEntity;
use Kdyby\Translation\ITranslator;
use Nette;
use Nette\Application\IResponse;
use Stopka\TableExporter\SettingException;
use Stopka\TableExporter\SpreadsheetResponse;

class ApplicationsExportResponse implements IResponse {

    /** @var EventEntity[] */
    private $eventEntity;

    /** @var  ApplicationEntity[] */
    private $applications;

    /** @var ITranslator */
    private $translator;

    /**
     * ApplicationsExportResponse constructor.
     * @param EventEntity $eventEntity
     * @param ApplicationEntity[] $applications
     */
    public function __construct(EventEntity $eventEntity, array $applications, ITranslator $translator) {
        $this->eventEntity = $eventEntity;
        $this->applications = $applications;
        $this->translator = $translator;
    }

    /**
     * @param Nette\Http\IRequest $httpRequest
     * @param Nette\Http\IResponse $httpResponse
     * @throws SettingException
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    function send(Nette\Http\IRequest $httpRequest, Nette\Http\IResponse $httpResponse) {
        $response = new SpreadsheetResponse($this->applications, SpreadsheetResponse::EXPORT_FORMAT_XLSX);
        $response->setFilenameWithDate('applications-');
        $response->setColumnDelimiter(';');
        //$response->setCharset('windows-1250');
        $response->addColumn('cart_id', 'Číslo objednávky')
            ->setCustomRenderer(function (ApplicationEntity $applicaiton) {
                return $applicaiton->getCart() ? $applicaiton->getCart()->getId() : "";
            });
        $response->addColumn('cart_email', 'Email')
            ->setCustomRenderer(function (ApplicationEntity $applicaiton) {
                return $applicaiton->getCart() ? $applicaiton->getCart()->getEmail() : "";
            });
        $response->addColumn('cart_firstName', 'Jméno rodiče')
            ->setCustomRenderer(function (ApplicationEntity $applicaiton) {
                return $applicaiton->getCart() ? $applicaiton->getCart()->getFirstName() : "";
            });
        $response->addColumn('cart_lastName', 'Příjmení rodiče')
            ->setCustomRenderer(function (ApplicationEntity $applicaiton) {
                return $applicaiton->getCart() ? $applicaiton->getCart()->getLastName() : "";
            });
        $response->addColumn('cart_phone', 'Telefon')
            ->setCustomRenderer(function (ApplicationEntity $applicaiton) {
                return $applicaiton->getCart() ? $applicaiton->getCart()->getPhone() : "";
            });
        $response->addColumn('cart_created', 'Vytvoření objednávky')
            ->setCustomRenderer(function (ApplicationEntity $applicaiton) {
                return $applicaiton->getCart() && $applicaiton->getCart()->getCreated() ? $applicaiton->getCart()->getCreated()->format('Y-m-d H:i:s') : "";
            });
        $response->addColumn('id', 'Id přihlášky')
            ->setCustomRenderer(function (ApplicationEntity $applicaiton) {
                return $applicaiton->getId();
            });
        $response->addColumn('state', 'Stav')
            ->setCustomRenderer(function (ApplicationEntity $applicaiton) {
                $states = ApplicationEntity::getAllStates();
                return $this->translator->translate($states[$applicaiton->getState()]);
            });
        $response->addColumn('firstName', 'Jméno')
            ->setCustomRenderer(function (ApplicationEntity $applicaiton) {
                return $applicaiton->getFirstName();
            });
        $response->addColumn('lastName', 'Příjmení')
            ->setCustomRenderer(function (ApplicationEntity $applicaiton) {
                return $applicaiton->getLastName();
            });
        $response->addColumn('gender', 'Pohlaví')
            ->setCustomRenderer(function (ApplicationEntity $applicaiton) {
                if ($applicaiton->getGender() === NULL) {
                    return '';
                }
                return $applicaiton->getGender() ? 'Žena' : 'Muž';
            });
        $response->addColumn('birthDate', 'Datum narození')
            ->setCustomRenderer(function (ApplicationEntity $applicaiton) {
                $date = $applicaiton->getBirthDate();
                return $date ? $date->format('Y-m-d') : '';
            });
        $response->addColumn('insuranceCompany', 'Zdravotní pojišťovna')
            ->setCustomRenderer(function (ApplicationEntity $applicaiton) {
                $ic = $applicaiton->getInsuranceCompany();
                return $ic ? $ic->getCode() . ' ' . $ic->getName() : '';
            });
        $response->addColumn('address', 'Adresa')
            ->setCustomRenderer(function (ApplicationEntity $applicaiton) {
                return $applicaiton->getAddress();
            });
        $response->addColumn('city', 'Město')
            ->setCustomRenderer(function (ApplicationEntity $applicaiton) {
                return $applicaiton->getCity();
            });
        $response->addColumn('zip', 'PSČ')
            ->setCustomRenderer(function (ApplicationEntity $applicaiton) {
                return $applicaiton->getZip();
            });
        $response->addColumn('friend', 'Umístění')
            ->setCustomRenderer(function (ApplicationEntity $applicaiton) {
                return $applicaiton->getFriend();
            });
        $response->addColumn('info', 'Info')
            ->setCustomRenderer(function (ApplicationEntity $applicaiton) {
                return $applicaiton->getInfo();
            });


        foreach ($this->eventEntity->getAdditions() as $addition) {
            if (!$addition->isVisibleIn(AdditionEntity::VISIBLE_EXPORT)) {
                continue;
            }
            $response->addColumn('a' . $addition->getId(), $addition->getName())
                ->setCustomRenderer(function (ApplicationEntity $application) use ($addition) {
                    $result = '';
                    $choices = $application->getChoices();
                    foreach ($choices as $choice) {
                        if ($choice->getOption()->getAddition()->getId() == $addition->getId()) {
                            $result .= $choice->getOption()->getName() . ($choice->isPayed() ? ' (OK)' : ' (!!)') . ';';
                        }
                    }
                    return $result;
                });
        }
        $response->send($httpRequest, $httpResponse);
    }


}