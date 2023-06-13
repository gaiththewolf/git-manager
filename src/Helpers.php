<?php

if (!function_exists('isPassworPrompt')) {
    function isPassworPrompt(string $output): bool
    {
        $passText = ["passphrase", "key", "id_rsa", ".ssh"];
        foreach ($passText as $pass) {
            if (strpos($output, $pass) !== false) {
                return true;
            }
        }
        return false;
    }
}

if (!function_exists('isComposerCMD')) {
    function isComposerCMD(...$args): bool
    {
        foreach ($args as $cmd) {
            if (is_array($cmd)) {
                if (isComposerCMD(...$cmd)) {
                    return true;
                }
            } elseif (strpos(strtolower($cmd), "composer") !== false) {
                return true;
            }
        }
        return false;
    }
}