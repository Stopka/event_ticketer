<?php

namespace App\Controls\Forms;


use Minetro\Forms\reCAPTCHA\ReCaptchaField;
use Minetro\Forms\reCAPTCHA\ReCaptchaHolder;
use Nette\Application\ApplicationException;
use Stopka\NetteFormRenderer\Forms\IFormOptionKeys;

class Form extends \Nette\Application\UI\Form implements IFormOptionKeys {
    use TContainerExtension;

    /** @var string */
    private $recaptcha_name;

    /**
     * @param  string $name Field name
     * @param  string $label Html label
     * @return ReCaptchaField
     */
    public function addReCaptcha($name = 'captcha', $label = NULL) {
        if ($this->recaptcha_name) {
            throw new ApplicationException('Form already contains captcha');
        }
        $this->recaptcha_name = $name;
        $recaptcha = $this[$name] = new ReCaptchaField(ReCaptchaHolder::getSiteKey(), $label);
        $recaptcha->setOmitted()
            ->setRequired();
        $this->addRecaptchaValidator();
        return $recaptcha;
    }

    /**
     * Vytvoří potvrzovací tlačítko s třídou primary
     * @param \string $name
     * @param \string $caption
     * @return \Nette\Forms\Controls\SubmitButton
     */
    public function addPrimarySubmit($name, $caption) {
        return $this->addSubmit($name, $caption)
            ->setAttribute('class', 'btn-primary');
    }

}
