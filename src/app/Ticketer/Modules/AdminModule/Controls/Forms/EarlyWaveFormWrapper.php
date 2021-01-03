<?php

declare(strict_types=1);

namespace Ticketer\Modules\AdminModule\Controls\Forms;

use DateTimeImmutable;
use Nette\Application\AbortException;
use Ticketer\Controls\FlashMessageTypeEnum;
use Ticketer\Controls\Forms\Form;
use Ticketer\Controls\Forms\FormWrapperDependencies;
use Ticketer\Model\Database\Entities\EarlyWaveEntity;
use Ticketer\Model\Database\Entities\EventEntity;
use Ticketer\Model\Database\Managers\EarlyWaveManager;
use Nette\Forms\Controls\SubmitButton;

class EarlyWaveFormWrapper extends FormWrapper
{

    private EarlyWaveManager $earlyWaveManager;

    private ?EventEntity $eventEntity = null;

    private ?EarlyWaveEntity $earlyWaveEntity = null;

    public function __construct(FormWrapperDependencies $formWrapperDependencies, EarlyWaveManager $earlyWaveManager)
    {
        parent::__construct($formWrapperDependencies);
        $this->earlyWaveManager = $earlyWaveManager;
    }

    public function setEventEntity(?EventEntity $eventEntity): void
    {
        $this->eventEntity = $eventEntity;
    }

    public function getEventEntity(): ?EventEntity
    {
        return $this->eventEntity;
    }

    public function setEarlyWaveEntity(?EarlyWaveEntity $earlyWaveEntity): void
    {
        $this->earlyWaveEntity = $earlyWaveEntity;
        if (null !== $earlyWaveEntity) {
            $this->setEventEntity($earlyWaveEntity->getEvent());
        }
    }

    /**
     * @param Form $form
     */
    protected function appendFormControls(Form $form): void
    {
        $this->appendEarlyWaveControls($form);
        $this->appendSubmitControls(
            $form,
            null !== $this->earlyWaveEntity ? 'Form.Action.Edit' : 'Form.Action.Create',
            [$this, 'submitClicked']
        );
        $this->loadData($form);
    }

    protected function loadData(Form $form): void
    {
        if (null === $this->earlyWaveEntity) {
            return;
        }
        $values = $this->earlyWaveEntity->getValueArray();
        $form->setDefaults($values);
    }

    /**
     * @param array<mixed> $values
     * @return array<mixed>
     */
    protected function preprocessData(array $values): array
    {
        return $values;
    }

    protected function appendEarlyWaveControls(Form $form): void
    {
        $form->addGroup("Entity.Singular.EarlyWave")
            ->setOption($form::OPTION_KEY_LOGICAL, true);
        $form->addText('name', 'Attribute.Name')
            ->setRequired(false);
        $form->addDate('startDate', 'Attribute.Event.StartDate')
            ->setOption($form::OPTION_KEY_DESCRIPTION, 'Form.EarlyWave.Description.StartDate')
            ->setDefaultValue(new DateTimeImmutable())
            ->setRequired(true)//    ->addRule($form::VALID, 'Form.Rule.Date')
        ;
    }

    /**
     * @param SubmitButton $button
     * @throws AbortException
     */
    protected function submitClicked(SubmitButton $button): void
    {
        $form = $button->getForm();
        if (null === $form || null === $this->eventEntity) {
            return;
        }
        /** @var array<mixed> $values */
        $values = $form->getValues('array');
        $values = $this->preprocessData($values);
        if (null !== $this->earlyWaveEntity) {
            $this->earlyWaveManager->editWaveFromWaveForm($values, $this->earlyWaveEntity);
            $this->getPresenter()->flashTranslatedMessage(
                'Form.EarlyWave.Message.Edit.Success',
                FlashMessageTypeEnum::SUCCESS()
            );
            $this->getPresenter()->redirect('EarlyWave:default', [$this->eventEntity->getId()->toString()]);
        } else {
            $this->earlyWaveManager->createWaveFromWaveForm($values, $this->eventEntity);
            $this->getPresenter()->flashTranslatedMessage(
                'Form.EarlyWave.Message.Create.Success',
                FlashMessageTypeEnum::SUCCESS()
            );
            $this->getPresenter()->redirect('EarlyWave:default', [$this->eventEntity->getId()->toString()]);
        }
    }
}
