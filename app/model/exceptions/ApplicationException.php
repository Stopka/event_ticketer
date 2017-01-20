<?php
namespace App\Model\Exceptions;

/**
 * Description of RuntimeException
 *
 * @author stopka
 */
class ApplicationException extends Exception {

    public function __construct($message, $e = NULL) {
        parent::__construct($message, $e);
    }
}


