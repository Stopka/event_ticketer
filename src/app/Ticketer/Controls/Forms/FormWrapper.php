<?php

declare(strict_types=1);

namespace Ticketer\Controls\Forms;

use Closure;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\SubmitButton;
use Ticketer\Controls\Control;
use Ticketer\Model\Exceptions\FormControlException;
use Ticketer\Model\Exceptions\TranslatedException;
use Nette;
use Stopka\NetteFormRenderer\Forms\Rendering\BetterFormRenderer;

abstract class FormWrapper extends Control
{
    /**
     * @var string path to template
     */
    private $templatePath = __DIR__ . '/FormWrapper.latte';

    /**
     * FormWrapper constructor.
     * @param FormWrapperDependencies $formWrapperDependencies
     */
    public function __construct(FormWrapperDependencies $formWrapperDependencies)
    {
        parent::__construct($formWrapperDependencies->getControlDependencies());
    }

    /**
     * @return Nette\Forms\IFormRenderer
     */
    protected function getFormRenderer()
    {
        return new BetterFormRenderer();
    }

    /**
     * @return Form
     */
    protected function createForm()
    {
        return new Form();
    }

    /**
     * @return Form
     */
    protected function createComponentForm()
    {
        $form = $this->createForm();
        $form->setTranslator($this->getTranslator());
        $form->setRenderer($this->getFormRenderer());
        $this->appendFormControls($form);

        return $form;
    }

    /**
     * @param Form $form
     */
    abstract protected function appendFormControls(Form $form): void;

    /**
     * @param Form $form
     * @param string $label
     * @param callable|null $callback
     * @return SubmitButton
     */
    protected function appendSubmitControls(Form $form, $label, $callback = null)
    {
        $form->setCurrentGroup();
        $submit = $form->addPrimarySubmit('submit', $label);
        if (null !== $callback) {
            $submit->onClick[] = $this->getButtonClickCallback($callback);
        }

        return $submit;
    }

    /**
     * @param callable $callback
     * @return callable(SubmitButton):void
     */
    private function getButtonClickCallback($callback)
    {
        return function (SubmitButton $button) use ($callback): void {
            /** @var Form $form */
            $form = $button->getForm();
            try {
                call_user_func($callback, $button);
            } catch (FormControlException $e) {
                $controlName = $e->getControlPath();
                /** @var \Throwable $exception */
                $exception = $e->getPrevious();
                if ($exception instanceof TranslatedException) {
                    /** @var TranslatedException $exception */
                    $errorPublished = false;
                    foreach ($form->getComponents(true, BaseControl::class) as $component) {
                        if (!$component instanceof BaseControl) {
                            continue;
                        }
                        $path = $component->lookupPath(Form::class);
                        if ($controlName === $path) {
                            $errorPublished = true;
                            $component->addError($exception->getTranslatedMessage($this->getTranslator()));
                            break;
                        }
                    }
                    if (!$errorPublished) {
                        $form->addError($exception->getTranslatedMessage($this->getTranslator()));
                    }
                } else {
                    throw $exception;
                }
            } catch (TranslatedException $e) {
                $form->addError($e->getTranslatedMessage($this->getTranslator()));
            }
        };
    }

    /**
     * @param array<mixed> ...$args
     */
    public function render(...$args): void
    {
        $template = $this->getTemplate();
        $template->setFile($this->templatePath);
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $template->render(...$args);
    }

    /**
     * @param string $templatePath
     */
    protected function setTemplate($templatePath): void
    {
        $this->templatePath = $templatePath;
    }
}
