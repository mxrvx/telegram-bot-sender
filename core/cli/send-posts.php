<?php

declare(strict_types=1);

use MXRVX\Telegram\Bot\Sender\App;
use MXRVX\Telegram\Bot\Sender\PostUserManager;

/** @var \modX $modx */
/** @psalm-suppress MissingFile */
require \dirname(__DIR__) . '/bootstrap.php';

$app = $modx->services[App::class] ?? null;
if ($app instanceof App) {
    PostUserManager::load($app);
}
