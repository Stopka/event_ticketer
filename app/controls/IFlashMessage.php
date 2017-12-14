<?php

namespace App\Controls;

/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 14.12.17
 * Time: 20:28
 */

interface IFlashMessage {
    const FLASH_MESSAGE_TYPE_ERROR = "error";
    const FLASH_MESSAGE_TYPE_WARNING = "warning";
    const FLASH_MESSAGE_TYPE_INFO = "info";
    const FLASH_MESSAGE_TYPE_SUCCESS = "success";
}