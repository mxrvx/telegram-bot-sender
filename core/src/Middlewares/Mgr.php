<?php

declare(strict_types=1);

namespace MXRVX\Telegram\Bot\Sender\Middlewares;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;

class Mgr implements MiddlewareInterface
{
    public function __construct(protected \modX $modx) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @psalm-suppress DocblockTypeContradiction @var \modUser|null $user */
        $user = $this->modx->user ?? null;
        if ($this->modx->context->checkPolicy('load') && $user instanceof \modUser && $user->hasSessionContext('mgr')) {
            return $handler->handle($request);
        }

        $response = new Response();
        $response->getBody()->write(\json_encode('Access Denied', JSON_THROW_ON_ERROR));

        return $response
            ->withStatus(401)
            ->withHeader('Content-Type', 'application/json; charset=utf-8');
    }
}
