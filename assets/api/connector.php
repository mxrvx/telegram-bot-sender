<?php

declare(strict_types=1);

use DI\Bridge\Slim\Bridge;
use MXRVX\Telegram\Bot\Sender\App;
use MXRVX\Telegram\Bot\Sender\Middlewares\Context;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Slim\Psr7\Factory\ResponseFactory;

$file = dirname(__DIR__,2) . '/core/bootstrap.php';
if (file_exists($file)) {
    /** @var \modX $modx */
    require $file;
} else {
    exit('Could not load Bootstrap');
}

if (isset($_SERVER['QUERY_STRING'])) {
    while (str_contains($_SERVER['QUERY_STRING'], '&amp;')) {
        $_SERVER['QUERY_STRING'] = html_entity_decode($_SERVER['QUERY_STRING']);
    }
}

/** @var \modX $modx */
$container = new DI\Container();
$container->set(ResponseFactoryInterface::class, function(ContainerInterface $container) {
    return $container->get(ResponseFactory::class);
});
$container->set(\modX::class, $modx);
$container->set('modx', $modx);
$container->set(App::class, $modx->services[App::class] ??= new App($modx));

$app = Bridge::create($container);

$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();
$app->setBasePath('/assets/components/' . App::NAMESPACE);
$app->add(Context::class);

$routes = __DIR__ . '/routes.php';
if (file_exists($routes)) {
    require $routes;
}

try {
    $app->run();
} catch (\Slim\Exception\HttpNotFoundException $e) {
    http_response_code(404);
    echo json_encode('Not Found');
} catch (\Throwable $e) {
    $modx->log(\modX::LOG_LEVEL_ERROR, $e->getMessage());
    http_response_code(500);
    echo json_encode('Internal Server Error');
}
