<?php

declare(strict_types=1);

namespace Ticketer\Modules\FrontModule\Controls\Forms;

use Ticketer\Controls\FlashMessageTypeEnum;
use Ticketer\Controls\Forms\Form;
use Ticketer\Controls\Forms\FormWrapperDependencies;
use Ticketer\Model\Database\Entities\EarlyEntity;
use Ticketer\Model\Database\Entities\EventEntity;
use Ticketer\Model\Database\Managers\SubstituteManager;
use Nette\Forms\Controls\SubmitButton;

class SubstituteFormWrapper extends FormWrapper
{
    /** @var  SubstituteManager */
    private $substituteManager;

    /** @var  EarlyEntity|null */
    private $early;

    /** @var  EventEntity */
    private $event;

    public function __construct(FormWrapperDependencies $formWrapperDependencies, SubstituteManager $substituteDao)
    {
        parent::__construct($formWrapperDependencies);
        $this->substituteManager = $substituteDao;
    }

    /**
     * @param EarlyEntity $early
     */
    public function setEarly(EarlyEntity $early): void
    {
        $this->early = $early;
        $wave = $early->getEarlyWave();
        if (null === $wave) {
            return;
        }
        $event = $wave->getEvent();
        if (null === $event) {
            return;
        }
        $this->event = $event;
    }

    /**
     * @param EventEntity $event
     */
    public function setEvent(EventEntity $event): void
    {
        $this->early = null;
        $this->event = $event;
    }

    protected function loadData(Form $form): void
    {
        if (null !== $this->early) {
            $form->setDefaults($this->early->getValueArray());
        }
    }

    /**
     * @param Form $form
     */
    protected function appendFormControls(Form $form): void
    {
        $form->addText('firstName', 'Attribute.Person.FirstName', null, 255)
            ->setRequired()
            ->addRule($form::MAX_LENGTH, null, 255);
        $form->addText('lastName', 'Attribute.Person.LastName', null, 255)
            ->setRequired()
            ->addRule($form::MAX_LENGTH, null, 255);
        $form->addText('email', 'Attribute.Person.Email')
            ->setRequired()
            ->addRule($form::EMAIL);
        $form->addText('count', 'Attribute.Count')
            ->setHtmlType('number')
            ->setDefaultValue(1)
            ->setRequired()
            ->addRule($form::INTEGER)
            ->addRule($form::RANGE, null, [1, 5]);
        $this->appendSubmitControls($form, 'Form.Action.Register', [$this, 'saveClicked']);
        $this->loadData($form);
    }

    public function saveClicked(SubmitButton $button): void
    {
        $form = $button->getForm();
        if (null === $form) {
            return;
        }
        /** @var array<mixed> $values */
        $values = $form->getValues('array');
        $this->substituteManager->createSubtituteFromForm($values, $this->event, $this->early);
        $this->getPresenter()->flashTranslatedMessage(
            'Form.Substitute.Message.Create.Success',
            FlashMessageTypeEnum::SUCCESS()
        );
        $this->getPresenter()->redirect('Homepage:');
    }
}
