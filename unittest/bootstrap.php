<?php

define('__ROOT__', realpath(__DIR__ . '/../'));
define('__WWW__', realpath(__DIR__ . '/../htdocs'));

// Application::preContainer() defines this from .env at runtime; the package
// config files read it, so tests have to stand in for it
define('DEBUG', true);

$_ENV = array_replace_recursive($_ENV, parse_ini_file(__DIR__ . '/support/env', true, INI_SCANNER_TYPED));

// two layouts: a standalone clone carries its own vendor/ directory; a clone
// developed in place inside an application's vendor tree finds its siblings
// two directories up (vendor/orange/*)
$frameworkSrc = is_dir(__DIR__ . '/../vendor')
    ? __DIR__ . '/../vendor/orange/framework/src'
    : __DIR__ . '/../../framework/src';

// MergeView extends the framework ViewAbstract and uses the Data service, which
// call logMsg()/isLogEnabled() - those helpers are normally loaded at runtime by
// Application::preContainer() via dynamic include_once, not composer autoload.
require $frameworkSrc . '/helpers/helpers.php';
require $frameworkSrc . '/helpers/errors.php';
require $frameworkSrc . '/helpers/wrappers.php';

// making these will make it so the defaults won't be loaded
if (!function_exists('orangeExceptionHandler')) {
    function orangeExceptionHandler()
    {
    }
}

if (!function_exists('orangeErrorHandler')) {
    function orangeErrorHandler()
    {
    }
}

