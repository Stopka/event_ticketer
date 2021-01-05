<?php

declare(strict_types=1);

namespace Ticketer\Controls\Forms;

use Contributte\FormMultiplier\Multiplier;
use Contributte\ReCaptcha\Forms\InvisibleReCaptchaField;
use Contributte\ReCaptcha\Forms\ReCaptchaField;
use Nette\Application\UI\Form as NetteForm;
use Nette\Forms\Controls\SubmitButton;
use Stopka\NetteFormRenderer\Forms\IFormOptionKeys;

/**
 * Class Form
 * @package Ticketer\Controls\Forms
 * @method ReCaptchaField addReCaptcha()
 * @method InvisibleReCaptchaField addInvisibleReCaptcha()
 */
class Form extends NetteForm implements IFormOptionKeys
{
    use TContainerExtension;

    /**
     * Vytvoří potvrzovací tlačítko s třídou primary
     * @param string $name
     * @param string $caption
     * @return SubmitButton
     */
    public function addPrimarySubmit($name, $caption)
    {
        return $this->addSubmit($name, $caption)
            ->setHtmlAttribute('class', 'btn-primary');
    }
}
