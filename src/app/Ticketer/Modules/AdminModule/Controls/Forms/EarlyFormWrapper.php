<?php

declare(strict_types=1);

namespace Ticketer\Modules\AdminModule\Controls\Forms;

use DateInput;
use DateTime;
use Exception;
use Nette\Forms\Controls\SelectBox;
use Ticketer\Controls\FlashMessageTypeEnum;
use Ticketer\Controls\Forms\Form;
use Ticketer\Controls\Forms\FormWrapperDependencies;
use Ticketer\Model\Database\Entities\EarlyWaveEntity;
use Ticketer\Model\DateFormatter;
use Ticketer\Model\Database\Daos\EarlyWaveDao;
use Ticketer\Model\Database\Entities\EarlyEntity;
use Ticketer\Model\Database\Entities\EventEntity;
use Ticketer\Model\Database\Managers\EarlyManager;
use Nette\Forms\Controls\SubmitButton;
use Ticketer\Model\Dtos\Uuid;
use Ticketer\Modules\AdminModule\Controls\Forms\Inputs\UuidSelect;

class EarlyFormWrapper extends FormWrapper
{
    private const EARLY_WAVE_GROUP_ID = 'earlyWaveControlGroup';

    private EarlyManager $earlyManager;

    private ?EventEntity $eventEntity = null;

    private ?EarlyEntity $earlyEntity = null;

    private EarlyWaveDao $earlyWaveDao;

    private DateFormatter $dateFormatter;


    public function __construct(
        FormWrapperDependencies $formWrapperDependencies,
        EarlyManager $earlyManager,
        EarlyWaveDao $earlyWaveDao,
        DateFormatter $dateFormatter
    ) {
        parent::__construct($formWrapperDependencies);
        $this->earlyManager = $earlyManager;
        $this->earlyWaveDao = $earlyWaveDao;
        $this->dateFormatter = $dateFormatter;
    }

    public function setEventEntity(?EventEntity $eventEntity): void
    {
        $this->eventEntity = $eventEntity;
    }

    public function getEventEntity(): ?EventEntity
    {
        return $this->eventEntity;
    }

    public function setEarlyEntity(?EarlyEntity $earlyEntity): void
    {
        $this->earlyEntity = $earlyEntity;
        if (null !== $earlyEntity) {
            $wave = $earlyEntity->getEarlyWave();
            if (null !== $wave) {
                $this->setEventEntity($wave->getEvent());
            }
        }
    }

    /**
     * @param Form $form
     */
    protected function appendFormControls(Form $form): void
    {
        $this->appendEarlyControls($form);
        $this->appendSubmitControls(
            $form,
            null !== $this->earlyEntity ? 'Form.Action.Edit' : 'Form.Action.Create',
            [$this, 'submitClicked']
        );
        $this->loadData($form);
    }

    protected function loadData(Form $form): void
    {
        if (null === $this->earlyEntity) {
            return;
        }
        $values = $this->earlyEntity->getValueArray(null, ['earlyWave']);
        $wave = $this->earlyEntity->getEarlyWave();
        $values['earlyWaveId'] = null !== $wave ? $wave->getId() : null;
        $form->setDefaults($values);
    }

    /**
     * @return array<string,string> earlyID => earlyTitle
     */
    protected function getEarlyFormSelectArray(): array
    {
        $result = [null => 'Form.Early.EarlyWave.CreateNew'];
        $waves = $this->earlyWaveDao->getEventEearlyWaves($this->eventEntity);
        foreach ($waves as $wave) {
            $result[$wave->getId()->toString()] = $wave->getName() . ' - ' . $this->dateFormatter->getDateString(
                $wave->getStartDate()
            );
        }

        return $result;
    }

    protected function appendEarlyControls(Form $form): void
    {
        $form->addGroup("Entity.Singular.Early")
            ->setOption($form::OPTION_KEY_LOGICAL, true);
        $form->addText('firstName', 'Attribute.Person.FirstName')
            ->setRequired(false);
        $form->addText('lastName', 'Attribute.Person.LastName')
            ->setRequired(false);
        $form->addEmail('email', 'Attribute.Person.Email')
            ->setRequired(true);
        $form->addSelect(
            'earlyWaveId',
            'Entity.Singular.EarlyWave',
            $this->getEarlyFormSelectArray()
        )
            ->setOption($form::OPTION_KEY_DESCRIPTION, "Form.Early.Description.EarlyWave")
            ->setDefaultValue(null)
            ->setRequired(false)
            ->addCondition($form::FILLED)
            ->toggle('earlyWaveControlGroup', false);
        $form->addGroup("Form.Early.Group.NewEarlyWave")
            ->setOption($form::OPTION_KEY_ID, self::EARLY_WAVE_GROUP_ID);
        $wave = $form->addContainer('earlyWave');
        $wave->addText('name', 'Attribute.Name')
            ->setRequired(false);
        /** @var SelectBox $earlyWaveControl */
        $earlyWaveControl = $form["earlyWaveId"];
        $wave->addDate('startDate', 'Attribute.Event.StartDate')
            ->setOption($form::OPTION_KEY_DESCRIPTION, 'Form.Early.Description.StartDate')
            ->setDefaultValue(new DateTime())
            ->setRequired(false)
            //->addRule($form::VALID, 'Form.Rule.Date')
            ->addConditionOn($earlyWaveControl, $form::FILLED)
            ->elseCondition()
            ->addRule($form::FILLED);
    }

    /**
     * @param SubmitButton $button
     * @throws Exception
     */
    protected function submitClicked(SubmitButton $button): void
    {
        $form = $button->getForm();
        if (null === $form || null === $this->eventEntity) {
            return;
        }
        /** @var array<mixed> $values */
        $values = $form->getValues('array');
        if (null !== $values['earlyWaveId']) {
            $values['earlyWaveId'] = Uuid::fromString($values['earlyWaveId']);
        }
        if (null !== $this->earlyEntity) {
            $this->earlyManager->editEarlyFromEarlyForm($values, $this->earlyEntity, $this->eventEntity);
            $this->getPresenter()->flashTranslatedMessage(
                'Form.Early.Message.Edit.Success',
                FlashMessageTypeEnum::SUCCESS()
            );
            $this->getPresenter()->redirect('Early:default', [$this->eventEntity->getId()->toString()]);
        } else {
            $this->earlyManager->createEarlyFromEarlyForm($values, $this->eventEntity);
            $this->getPresenter()->flashTranslatedMessage(
                'Form.Early.Message.Create.Success',
                FlashMessageTypeEnum::SUCCESS()
            );
            $this->getPresenter()->redirect('Early:default', [$this->eventEntity->getId()->toString()]);
        }
    }
}
