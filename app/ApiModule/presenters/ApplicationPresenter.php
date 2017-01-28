<?php

namespace App\ApiModule\Presenters;

use App\Model;
use Nette\Application\Responses\TextResponse;
use Tracy\Debugger;


class ApplicationPresenter extends BasePresenter {

    /**
     * @var Model\Facades\OrderFacade
     * @inject
     */
    public $orderFacade;

    public function renderSendEmails($id) {
        if(!Debugger::$productionMode){
            $order = $this->orderFacade->getOrder($id);
            $this->orderFacade->sendRegistrationEmail($order);
        }
        $this->sendResponse(new TextResponse('OK'));
    }

}
