<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 20.1.17
 * Time: 22:09
 */

namespace App\Model;


use Nette\Application\LinkGenerator;
use Nette\Mail\Message;
use Nette\Object;

class EmailMessageFactory extends Object {

    const CONFIG_PARAM = 'email';
    const CONFIG_PARAM_EMAIL_ADDRESS = 'address';
    const CONFIG_PARAM_EMAIL_NAME = 'name';
    const CONFIG_PARAM_FROM = 'from';
    const CONFIG_PARAM_REPLY_TO = 'replyTO';

    /** @var $systemConfigurations SystemConfigurations */
    private $systemConfigurations;

    /** @var  LinkGenerator */
    private $linkGenerator;

    public function __construct(SystemConfigurations $systemConfigurations, LinkGenerator $linkGenerator) {
        $this->systemConfigurations = $systemConfigurations;
        $this->linkGenerator = $linkGenerator;
    }

    public function create() {
        $message = new Message();
        $params = $this->systemConfigurations->getParameters(self::CONFIG_PARAM);
        list($address, $name) = $this->getAddresNamePair($params[self::CONFIG_PARAM_FROM]);
        $message->setFrom($address, $name);
        if (isset($params[self::CONFIG_PARAM_REPLY_TO])) {
            if (!is_array($params[self::CONFIG_PARAM_REPLY_TO]) || $this->isAddresNamePair($params[self::CONFIG_PARAM_REPLY_TO])) {
                $params[self::CONFIG_PARAM_REPLY_TO] = [$params[self::CONFIG_PARAM_REPLY_TO]];
            }
            foreach ($params[self::CONFIG_PARAM_REPLY_TO] as $replyTo) {
                list($address, $name) = $this->getAddresNamePair($replyTo);
                $message->addReplyTo($replyTo);
            }
        }
        return $message;
    }

    /**
     * @param $param string|string[]
     * @return bool
     */
    protected function isAddresNamePair($param) {
        return is_array($param) && isset($param[self::CONFIG_PARAM_EMAIL_ADDRESS]);
    }

    /**
     * @param $param string|string[]
     * @return string[] address,name
     */
    protected function getAddresNamePair($param) {
        if ($this->isAddresNamePair($param)) {
            return [
                $param[self::CONFIG_PARAM_EMAIL_ADDRESS],
                isset($param[self::CONFIG_PARAM_EMAIL_NAME]) && $param[self::CONFIG_PARAM_EMAIL_NAME] ? $param[self::CONFIG_PARAM_EMAIL_NAME] : NULL
            ];
        }
        return [$param, NULL];
    }

    /**
     * @return string
     */
    public function getSystemHost(){
        $this->systemConfigurations->getParameters('host')['domain'];
    }

    /**
     * @return string
     */
    public function getSystemName(){
        $this->systemConfigurations->getParameters('host')['name'];
    }

    /**
     * @param $dest
     * @param array $params
     * @return string
     */
    public function link($dest,$params = []){
        return $this->linkGenerator->link($dest,$params);
    }

}