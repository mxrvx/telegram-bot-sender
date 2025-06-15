<?php

declare(strict_types=1);

if (!\defined('MODX_CORE_PATH')) {

    $dir = __DIR__;
    while (!\str_ends_with($dir, DIRECTORY_SEPARATOR)) {
        $dir = \dirname($dir);

        $file = \implode(DIRECTORY_SEPARATOR, [$dir, 'core', 'config', 'config.inc.php']);
        if (\file_exists($file)) {
            require $file;
            break;
        }
    }
    unset($dir);

    if (!\defined('MODX_CORE_PATH')) {
        exit('Could not load MODX core');
    }
}

$file = MODX_CORE_PATH . 'vendor/autoload.php';
if (\file_exists($file)) {
    require $file;
}

unset($file);

/** @psalm-suppress MissingFile */
require_once __DIR__ . '/deprecated.php';

/** @var \modX $modx */
if (!isset($modx)) {
    /** @psalm-suppress MissingFile */
    if (!\class_exists(\modX::class) && \file_exists(MODX_CORE_PATH . 'model/modx/modx.class.php')) {
        require MODX_CORE_PATH . 'model/modx/modx.class.php';
    }
    $modx = \modX::getInstance();
    $modx->initialize();
}

\MXRVX\Telegram\Bot\Sender\App::injectDependencies($modx);
/** @var \DI\Container $container */
$container = $container ?? \MXRVX\Autoloader\App::getInstance($modx)->getContainer();
if (!$container->has(\MXRVX\Telegram\Bot\Sender\App::class)) {
    $container->set(\MXRVX\Telegram\Bot\Sender\App::class, \DI\autowire());
}
