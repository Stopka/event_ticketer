<?php

namespace App\Controls\Forms;

use App\Model\Exception\Exception;
use Nette;
use Stopka\NetteFormRenderer\Forms\Rendering\BetterFormRenderer;


abstract class FormWrapper extends Nette\Application\UI\Control{
    /**
     * @var null|string path to template
     */
    private $template_path = __DIR__.'/FormWrapper.latte';

    /**
     * @return Nette\Forms\IFormRenderer
     */
    protected function getFormRenderer() {
        return new BetterFormRenderer();
    }

    /**
     * @return Form
     */
    protected function createForm() {
        return new Form();
    }

    /**
     * @return Form
     */
    protected function createComponentForm() {
        $form = $this->createForm();
        $form->setRenderer($this->getFormRenderer());
        $this->appendFormControls($form);
        return $form;
    }

    /**
     * @param Form $form
     */
    abstract protected function appendFormControls(Form $form);

    /**
     * @param Form $form
     * @param string $label
     * @param callable $callback
     * @return Nette\Forms\Controls\SubmitButton
     */
    protected function appendSubmitControls(Form $form, $label, $callback = NULL) {
        $form->setCurrentGroup();
        $submit = $form->addPrimarySubmit('submit', $label);
        if ($callback) {
            $submit->onClick[] = $this->getButtonClickCallback($callback);
        }
        return $submit;
    }

    /**
     * @param callable $callback
     * @return \Closure
     */
    private function getButtonClickCallback($callback) {
        return function (Nette\Forms\Controls\SubmitButton $button) use ($callback) {
            try{
                call_user_func($callback,$button);
            }catch (Exception $e){
                $button->getForm()->addError($e->getMessage());
            }
        };
    }

    /**
     * @param array ...$args
     */
    public function render(...$args) {
        $this->template->setFile($this->template_path);
        $this->template->render(...$args);
    }

    /**
     * @param $template_path string
     */
    protected function setTemplate($template_path) {
        $this->template_path = $template_path;
    }

}
