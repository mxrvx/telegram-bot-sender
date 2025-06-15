<?php

declare(strict_types=1);

/** @var \modX $modx */
/** @psalm-suppress MissingFile */
require \dirname(__DIR__) . '/autoloader.php';

/** @var \DI\Container $container */
$container = $container ?? \MXRVX\Autoloader\App::getInstance($modx)->getContainer();
/** @var \MXRVX\Telegram\Bot\Sender\App $app */
$app = $container->get(\MXRVX\Telegram\Bot\Sender\App::class);
\MXRVX\Telegram\Bot\Sender\PostUserManager::load($app);
