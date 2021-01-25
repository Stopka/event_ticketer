<?php

declare(strict_types=1);

namespace Ticketer\Modules\AdminModule\Presenters;

use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Ticketer\Model\Dtos\Uuid;
use Ticketer\Modules\AdminModule\Controls\Forms\IOptionFormWrapperFactory;
use Ticketer\Modules\AdminModule\Controls\Forms\OptionFormWrapper;
use Ticketer\Modules\AdminModule\Controls\Grids\IOptionsGridWrapperFactory;
use Ticketer\Modules\AdminModule\Controls\Grids\OptionsGridWrapper;
use Ticketer\Controls\FlashMessageTypeEnum;
use Ticketer\Model\Database\Daos\AdditionDao;
use Ticketer\Model\Database\Daos\OptionDao;
use Ticketer\Model\Database\Entities\AdditionEntity;
use Ticketer\Model\Database\Entities\OptionEntity;

class OptionPresenter extends BasePresenter
{

    private OptionDao $optionDao;

    private AdditionDao $additionDao;

    private IOptionsGridWrapperFactory $optionsGridWrapperFactory;

    private IOptionFormWrapperFactory $optionFormWrapperFactory;

    public function __construct(
        BasePresenterDependencies $dependencies,
        OptionDao $optionDao,
        AdditionDao $additionDao,
        IOptionsGridWrapperFactory $optionsGridWrapperFactory,
        IOptionFormWrapperFactory $optionFormWrapperFactory
    ) {
        parent::__construct($dependencies);
        $this->optionDao = $optionDao;
        $this->additionDao = $additionDao;
        $this->optionsGridWrapperFactory = $optionsGridWrapperFactory;
        $this->optionFormWrapperFactory = $optionFormWrapperFactory;
    }

    /**
     * @param string $id
     * @throws AbortException
     * @throws BadRequestException
     */
    public function actionDefault(string $id): void
    {
        $uuid = $this->deserializeUuid($id);
        $addition = $this->additionDao->getAddition($uuid);
        if (null === $addition) {
            $this->flashTranslatedMessage('Addition.NotFound', FlashMessageTypeEnum::ERROR());
            $this->redirect("Homepage:");
        }
        $this->getMenu()->setLinkParam(AdditionEntity::class, $addition);
        /** @var OptionsGridWrapper $optionsGrid */
        $optionsGrid = $this->getComponent('optionsGrid');
        $optionsGrid->setAdditionEntity($addition);
        $this->template->addition = $addition;
    }

    /**
     * @param string $id
     * @throws AbortException
     * @throws BadRequestException
     */
    public function actionAdd(string $id): void
    {
        $uuid = $this->deserializeUuid($id);
        $addition = $this->additionDao->getAddition($uuid);
        if (null === $addition) {
            $this->flashTranslatedMessage('Addition.NotFound', FlashMessageTypeEnum::ERROR());
            $this->redirect("Homepage:");
        }
        $this->getMenu()->setLinkParam(AdditionEntity::class, $addition);
        /** @var OptionFormWrapper $optionForm */
        $optionForm = $this->getComponent('optionForm');
        $optionForm->setAdditionEntity($addition);
        $this->template->option = null;
        $this->template->addition = $addition;
    }

    /**
     * @param string $id
     * @throws AbortException
     * @throws BadRequestException
     */
    public function actionEdit(string $id): void
    {
        $uuid = $this->deserializeUuid($id);
        $option = $this->optionDao->getOption($uuid);
        if (null === $option) {
            $this->flashTranslatedMessage('Addition.NotFound', FlashMessageTypeEnum::ERROR());
            $this->redirect("Homepage:");
        }
        $this->getMenu()->setLinkParam(OptionEntity::class, $option);
        /** @var OptionFormWrapper $optionForm */
        $optionForm = $this->getComponent('optionForm');
        $optionForm->setOptionEntity($option);
        $this->template->option = $option;
        $this->template->addition = $option->getAddition();
    }

    public function createComponentOptionsGrid(): OptionsGridWrapper
    {
        return $this->optionsGridWrapperFactory->create();
    }

    public function createComponentOptionForm(): OptionFormWrapper
    {
        return $this->optionFormWrapperFactory->create();
    }
}
