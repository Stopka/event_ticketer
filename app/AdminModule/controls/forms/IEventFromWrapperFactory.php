<?php

namespace App\AdminModule\Controls\Forms;

interface IEventFromWrapperFactory {

    /**
     * @return EventFormWrapper
     */
    public function create(): EventFormWrapper;
}
