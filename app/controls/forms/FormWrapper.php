<?php

namespace App\Controls\Forms;

use Nette;
use Stopka\NetteFormRenderer\FormRenderer;


abstract class FormWrapper extends Nette\Application\UI\Control {

    /**
     * @var null|string path to template
     */
    private $template_path = null;

    /**
     * @return FormRenderer
     */
    protected function getFormRenderer() {
        return new FormRenderer();
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

    protected function appendSubmitControls(Form $form, $label) {
        $form->setCurrentGroup();
        $form->addSubmit('submit', $label);
    }

    public function render(...$args) {
        if (!$this->template_path) {
            /** @var Form $form */
            $form = $this->getComponent('form');
            $form->render(...$args);
            return;
        }
        $this->template->setFile($this->template_path);
        $this->template->render();
    }

    /**
     * @param $template_path string
     */
    protected function setTemplate($template_path) {
        $this->template_path = $template_path;
    }

}
