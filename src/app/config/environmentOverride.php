<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 8.10.18
 * Time: 22:07
 */

$envNeon = 'ConfigJsonOverride';
if (array_key_exists($envNeon, $_SERVER)) {
    $envParams = json_decode($_SERVER[$envNeon], true);
    if (!$envParams) {
        $envParams = [];
    }
} else {
    $envParams = [];
}
return $envParams;
