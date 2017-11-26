<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 26.11.17
 * Time: 19:19
 */

namespace App\Model;


use Nette\Object;

/**
 * Class CronService
 * @package App\Model
 */
class CronService extends Object {
    public $onCronRun = Array();

    public function run(){
        $this->onCronRun();
    }
}