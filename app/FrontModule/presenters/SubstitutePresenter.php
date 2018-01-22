<?php

namespace App\FrontModule\Presenters;

use App\Controls\Forms\CartFormWrapper;
use App\Controls\Forms\ICartFormWrapperFactory;
use App\Model\Persistence\Dao\SubstituteDao;
use App\Model\Persistence\Entity\SubstituteEntity;


class SubstitutePresenter extends BasePresenter {

    /** @var ICartFormWrapperFactory */
    public $cartFormWrapperFactory;

    /** @var SubstituteDao */
    public $substituteDao;

    /**
     * SubstitutePresenter constructor.
     * @param ICartFormWrapperFactory $cartFormWrapperFactory
     * @param SubstituteDao $substituteDao
     */
    public function __construct(ICartFormWrapperFactory $cartFormWrapperFactory, SubstituteDao $substituteDao) {
        parent::__construct();
        $this->cartFormWrapperFactory = $cartFormWrapperFactory;
        $this->substituteDao = $substituteDao;
    }

    /**
     * @param string $id
     * @throws \Nette\Application\AbortException
     */
    public function actionDefault(string $id) {
        $this->redirect('register', $id);
    }

    /**
     * @param string $id
     * @throws \Nette\Application\AbortException
     */
    public function actionRegister(string $id) {
        $substitute = $this->substituteDao->getReadySubstituteByUid($id);
        if (!$substitute) {
            $this->flashTranslatedMessage('Error.Substitute.NotFound', self::FLASH_MESSAGE_TYPE_WARNING);
            $this->redirect('Homepage:');
        }
        $this->getMenu()->setLinkParam(SubstituteEntity::class, $substitute);
        /** @var CartFormWrapper $cartFormWrapper */
        $cartFormWrapper = $this->getComponent('cartForm');
        $cartFormWrapper->setSubstitute($substitute);
        $event = $substitute->getEvent();
        $this->template->event = $event;
    }

    /**
     * @return CartFormWrapper
     */
    protected function createComponentCartForm() {
        return $this->cartFormWrapperFactory->create();
    }

}
