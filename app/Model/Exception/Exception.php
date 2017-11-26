<?php
namespace App\Model\Exception;

use Kdyby\Translation\ITranslator;

/**
 * Description of RuntimeException
 *
 * @author stopka
 */
class Exception extends \RuntimeException {

    /** @var  string[] */
    private $full_message;

    /**
     * @param \string|\array $message textová zpráva, nebo pole [zpráva,count,params] pro použití v překladu
     * @param \Exception $previous
     */
    public function __construct($message, $previous = NULL) {
        if(is_array($message)){
            if(count($message)==3){
                list($message,$count,$params)=$message;
            }else{
                list($message,$params)=$message;
                $count=NULL;
            }
        }else{
            $params=[];
            $count=NULL;
        }
        if($previous){
            $params['reason']=$previous->getMessage();
        }
        $this->full_message=[$message,$count,$params];
        parent::__construct($message, $this->code, $previous);
    }

    public function getFullMessage(){
        return $this->full_message;
    }

    /**
     * Vrátí lokalizovanou zprávu o chybě
     * @param ITranslator $translator
     * @return \string
     */
    public function getTranslatedMessage(ITranslator $translator) {
        $e=$this->getPrevious();
        $message=$this->getFullMessage();
        if(is_subclass_of($e,'\App\Model\Exceptions')){
            /** @var Exception $e */
            $submessage=$e->getTranslatedMessage($translator);
            $message[2]['reason']=$submessage;
        }
        return $translator->translate($message);
    }

    /**
     * Přidá do message další parametr pro překlad zpráv
     * určeno pro volání parent konstruktoru
     * @param $message přijatá zpráva
     * @param $name jméno parametru
     * @param $param parametr samotný
     */
    protected function addParamToMessage($message,$name,$param){
        $message[2][$name]=$param;
        return $message;
    }
}


