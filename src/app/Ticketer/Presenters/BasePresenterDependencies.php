<?php

declare(strict_types=1);

namespace Ticketer\Presenters;

use Nette\Localization\ITranslator;
use Ticketer\Model\Database\Daos\AdministratorDao;

class BasePresenterDependencies
{
    private AdministratorDao $administratorDao;

    private ITranslator $transaltor;

    public function __construct(AdministratorDao $administratorDao, ITranslator $translator)
    {
        $this->administratorDao = $administratorDao;
        $this->transaltor = $translator;
    }

    /**
     * @return AdministratorDao
     */
    public function getAdministratorDao(): AdministratorDao
    {
        return $this->administratorDao;
    }

    /**
     * @return ITranslator
     */
    public function getTransaltor(): ITranslator
    {
        return $this->transaltor;
    }
}
