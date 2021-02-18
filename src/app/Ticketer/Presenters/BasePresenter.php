<?php

declare(strict_types=1);

namespace Ticketer\Presenters;

use Nette\Application\BadRequestException;
use Nette\Http\IResponse;
use Ramsey\Uuid\Exception\InvalidArgumentException;
use Ticketer\Controls\TFlashTranslatedMessage;
use Ticketer\Controls\TInjectTranslator;
use Ticketer\Model\Database\Daos\AdministratorDao;
use Ticketer\Model\Database\Entities\AdministratorEntity;
use Nette;
use Ticketer\Model\Dtos\Uuid;
use Ticketer\Templates\BaseTemplate;

/**
 * Base presenter for all application presenters.
 * @method BaseTemplate getTemplate()
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter
{
    use TInjectTranslator;
    use TFlashTranslatedMessage;

    /** @var string|null */
    public ?string $locale;

    private AdministratorDao $administratorDao;

    protected ?AdministratorEntity $administratorEntity = null;

    public function __construct(BasePresenterDependencies $dependencies)
    {
        parent::__construct();
        $this->administratorDao = $dependencies->getAdministratorDao();
        $this->injectTranslator($dependencies->getTransaltor());
    }

    /**
     * @throws Nette\Application\AbortException
     */
    public function startup(): void
    {
        parent::startup();
        if ($this->getUser()->isLoggedIn()) {
            $userUuid = Uuid::fromString($this->getUser()->getId());
            $administratorEntity = $this->administratorDao->getAdministrator($userUuid);
            if (null === $administratorEntity) {
                $this->getUser()->logout(true);
                $this->redirect('this');
            }
            $this->administratorEntity = $administratorEntity;
        }
    }

    public function beforeRender(): void
    {
        parent::beforeRender();
        $this->getTemplate()->administratorEntity = $this->administratorEntity;
    }

    /**
     * @param string $uuidString
     * @param string $errorMessage
     * @return Uuid
     * @throws BadRequestException
     */
    protected function deserializeUuid(string $uuidString, string $errorMessage = 'Invalid identificator'): Uuid
    {
        try {
            return Uuid::fromString($uuidString);
        } catch (InvalidArgumentException $exception) {
            throw new BadRequestException($errorMessage, IResponse::S404_NOT_FOUND, $exception);
        }
    }
}
