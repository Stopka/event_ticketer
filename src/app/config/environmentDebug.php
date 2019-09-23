<?php
$debugEnvVar = "ConfigDebug";
$debugEnvKey = "debugMode";
if (array_key_exists($debugEnvVar, $_SERVER)) {
    $debugConfig = json_decode($_SERVER[$debugEnvVar], true);
    if ($debugConfig && array_key_exists($debugEnvKey, $debugConfig)) {
        return $debugConfig[$debugEnvKey];
    }
}
return null;
