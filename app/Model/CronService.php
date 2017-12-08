<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 26.11.17
 * Time: 19:19
 */

namespace App\Model;


use Nette\SmartObject;

/**
 * Class CronService
 * @package App\Model
 */
class CronService {
    use SmartObject;
    public $onCronRun = Array();

    public function run(){
        $this->onCronRun();
    }
}