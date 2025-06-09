<?php

/** @var Slim\App $app */
use MXRVX\Telegram\Bot\Sender\Controllers;
use MXRVX\Telegram\Bot\Sender\Middlewares\Mgr;
use Slim\Routing\RouteCollectorProxy;

$group = $app->group(
    '/api/connector/mgr',
    static function (RouteCollectorProxy $group) {
        $group->any('/version/', Controllers\Mgr\Version::class);
        $group->any('/posts/[{id:\d+}/]', Controllers\Mgr\Posts::class);
        $group->any('/users/[{id:\d+}/]', Controllers\Mgr\Users::class);

        $group->group('/editor', function (RouteCollectorProxy $group) {
            $group->any('/autocomplete/resource/', Controllers\Mgr\Editor\Autocomplete\Resource::class);
            $group->any('/upload/image/', Controllers\Mgr\Editor\Upload\Image::class);
            $group->any('/upload/video/', Controllers\Mgr\Editor\Upload\Video::class);
        });
    }
)->add(Mgr::class);
