<?php

declare(strict_types=1);

namespace Ticketer\Modules\AdminModule\Responses;

use Nette\Http\IRequest;
use Nette\Http\IResponse as HttpIResponse;
use Nette\Localization\ITranslator;
use Ticketer\Model\Database\Enums\ApplicationStateEnum;
use Ticketer\Model\Database\Enums\GenderEnum;
use Ticketer\Model\Database\Entities\AdditionEntity;
use Ticketer\Model\Database\Entities\ApplicationEntity;
use Ticketer\Model\Database\Entities\EventEntity;
use Nette\Application\IResponse;
use Ticketer\Responses\SpreadsheetResponse\FormatEnum;
use Ticketer\Responses\SpreadsheetResponse\SpreadsheetResponse;

/**
 * Class ApplicationsExportResponse
 * @package Ticketer\Modules\AdminModule\Responses
 */
class ApplicationsExportResponse implements IResponse
{

    /** @var EventEntity */
    private $eventEntity;

    /** @var  ApplicationEntity[] */
    private $applications;

    /** @var ITranslator */
    private $translator;

    /**
     * ApplicationsExportResponse constructor.
     * @param EventEntity $eventEntity
     * @param ApplicationEntity[] $applications
     * @param ITranslator $translator
     */
    public function __construct(EventEntity $eventEntity, array $applications, ITranslator $translator)
    {
        $this->eventEntity = $eventEntity;
        $this->applications = $applications;
        $this->translator = $translator;
    }

    /**
     * @param IRequest $httpRequest
     * @param HttpIResponse $httpResponse
     */
    public function send(IRequest $httpRequest, HttpIResponse $httpResponse): void
    {
        $response = new SpreadsheetResponse($this->applications, FormatEnum::XLSX(), $this->translator);
        $response->setFilenameWithDate('applications-');
        $response->setColumnDelimiter(';');
        $response->addColumn('cart_id', 'Číslo objednávky')
            ->setRenderer(
                function (ApplicationEntity $application): string {
                    $cart = $application->getCart();

                    return null !== $cart ? (string)$cart->getId() : '';
                }
            );
        $response->addColumn('cart_email', 'Email')
            ->setRenderer(
                function (ApplicationEntity $application): string {
                    $cart = $application->getCart();
                    if (null === $cart) {
                        return '';
                    }

                    return $cart->getEmail() ?? '';
                }
            );
        $response->addColumn('cart_firstName', 'Jméno rodiče')
            ->setRenderer(
                function (ApplicationEntity $application): string {
                    $cart = $application->getCart();

                    return null !== $cart ? (string)$cart->getFirstName() : '';
                }
            );
        $response->addColumn('cart_lastName', 'Příjmení rodiče')
            ->setRenderer(
                function (ApplicationEntity $application): string {
                    $cart = $application->getCart();

                    return null !== $cart ? (string)$cart->getLastName() : '';
                }
            );
        $response->addColumn('cart_phone', 'Telefon')
            ->setRenderer(
                function (ApplicationEntity $application): string {
                    $cart = $application->getCart();

                    return null !== $cart ? (string)$cart->getPhone() : '';
                }
            );
        $response->addColumn('cart_created', 'Vytvoření objednávky')
            ->setRenderer(
                function (ApplicationEntity $application): string {
                    $cart = $application->getCart();
                    $created = null !== $cart ? $cart->getCreated() : null;

                    return null !== $created ? $created->format('Y-m-d H:i:s') : '';
                }
            );
        $response->addColumn('id', 'Id přihlášky')
            ->setRenderer(
                function (ApplicationEntity $applicaiton): string {
                    return (string)$applicaiton->getId();
                }
            );
        $response->addColumn('state', 'Stav')
            ->setRenderer(
                function (ApplicationEntity $applicaiton): string {
                    $states = ApplicationStateEnum::getLabels();

                    return $this->translator->translate($states[$applicaiton->getState()->getValue()]);
                }
            );
        $response->addColumn('firstName', 'Jméno')
            ->setRenderer(
                function (ApplicationEntity $applicaiton): string {
                    return (string)$applicaiton->getFirstName();
                }
            );
        $response->addColumn('lastName', 'Příjmení')
            ->setRenderer(
                function (ApplicationEntity $applicaiton): string {
                    return (string)$applicaiton->getLastName();
                }
            );
        $response->addColumn('gender', 'Pohlaví')
            ->setRenderer(
                function (ApplicationEntity $applicaiton): string {
                    $gender = $applicaiton->getGender();
                    switch ($gender) {
                        case GenderEnum::MALE():
                            return 'Muž';
                        case GenderEnum::FEMALE():
                            return 'Žena';
                    }

                    return '';
                }
            );
        $response->addColumn('birthDate', 'Datum narození')
            ->setRenderer(
                function (ApplicationEntity $applicaiton): string {
                    $date = $applicaiton->getBirthDate();

                    return null !== $date ? $date->format('d. m.') : '';
                }
            );
        $response->addColumn('birthYear', 'Rok narození')
            ->setRenderer(
                function (ApplicationEntity $applicaiton): string {
                    $date = $applicaiton->getBirthDate();

                    return null !== $date ? $date->format('Y') : '';
                }
            );
        $response->addColumn('insuranceCompany', 'Zdravotní pojišťovna')
            ->setRenderer(
                function (ApplicationEntity $applicaiton): string {
                    $ic = $applicaiton->getInsuranceCompany();

                    return null !== $ic ? $ic->getCode() : '';
                }
            );
        $response->addColumn('city', 'Město')
            ->setRenderer(
                function (ApplicationEntity $applicaiton): string {
                    return (string)$applicaiton->getCity();
                }
            );
        $response->addColumn('address', 'Adresa')
            ->setRenderer(
                function (ApplicationEntity $applicaiton): string {
                    return (string)$applicaiton->getAddress();
                }
            );
        $response->addColumn('zip', 'PSČ')
            ->setRenderer(
                function (ApplicationEntity $applicaiton): string {
                    return (string)$applicaiton->getZip();
                }
            );
        $response->addColumn('friend', 'Umístění')
            ->setRenderer(
                function (ApplicationEntity $applicaiton): string {
                    return (string)$applicaiton->getFriend();
                }
            );
        $response->addColumn('info', 'Info')
            ->setRenderer(
                function (ApplicationEntity $applicaiton): string {
                    return (string)$applicaiton->getInfo();
                }
            );


        foreach ($this->eventEntity->getAdditions() as $addition) {
            if (!$addition->getVisibility()->isExport()) {
                continue;
            }
            $response->addColumn('a' . $addition->getId(), (string)$addition->getName())
                ->setRenderer(
                    function (ApplicationEntity $application) use ($addition): string {
                        $result = '';
                        $choices = $application->getChoices();
                        foreach ($choices as $choice) {
                            $choiceOption = $choice->getOption();
                            if (null === $choiceOption) {
                                continue;
                            }
                            $choiceAddition = $choiceOption->getAddition();
                            if (null === $choiceAddition || $choiceAddition->getId() !== $addition->getId()) {
                                continue;
                            }
                            $result .= $choiceOption->getName() . ($choice->isPayed() ? ' (OK)' : ' (!!)') . ';';
                        }

                        return $result;
                    }
                );
        }
        $response->send($httpRequest, $httpResponse);
    }
}
