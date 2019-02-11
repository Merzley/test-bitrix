<?php
spl_autoload_register(function ($class) {
    static $strBaseNamespace = 'Local';
    static $strBaseNamespaceLen = 5;
    static $strBaseDir = __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'model';

    if (strpos($class, $strBaseNamespace) !== 0)
        return;

    $class = substr($class, $strBaseNamespaceLen);

    if (DIRECTORY_SEPARATOR === '/')
        $class = str_replace('\\', '/', $class);

    $strFileName = $strBaseDir.$class.'.php';

    if (file_exists($strFileName)) {
        require_once $strFileName;
    }
});