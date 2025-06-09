<?php

declare(strict_types=1);

namespace MXRVX\Telegram\Bot\Sender\Controllers\Traits;

use MXRVX\Telegram\Bot\Sender\Controllers\Controller;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Psr7\Response;

trait ModelOperationTrait
{
    public function post(): ResponseInterface
    {
        if (!$operation = (string) $this->getProperty('operation', false)) {
            return $this->failure('No operation defined.');
        }

        if (!$operationMethod = $this->getOperationMethod($operation)) {
            return $this->failure(\sprintf('Operation `%s` not found.', $operation));
        }

        /** @var array $data */
        $data = $operationMethod($this->getArrayProperty('ids'));

        return $this->success([
            'results' => $data,
        ]);
    }

    protected function getOperationMethodName(string $action): string
    {
        $parts = \explode('_', $action);
        $parts = \array_map('ucfirst', $parts);
        return 'operation' . \implode('', $parts);
    }

    protected function getOperationMethod(string $action): ?callable
    {
        $operationMethodName = $this->getOperationMethodName($action);
        if (\method_exists($this, $operationMethodName)) {
            return [$this, $operationMethodName];
        }
        return null;
    }

    protected function processOperation(string $method, array $ids, array $args = []): array
    {
        $results = [];

        /** @var Controller $controller */
        $controller = $this->container->get(static::class);
        if (!\method_exists($controller, $method)) {
            return $results;
        }


        $properties = \array_merge($this->getProperties(), $args);
        unset($properties['ids']);

        $processSingle = function (array $data = []) use ($controller, $method, $properties): array {
            try {
                if ($this->request instanceof ServerRequestInterface) {
                    $request = clone $this->request;
                    $request = $request->withMethod($method)->withParsedBody($data);
                    $controller->initController($request, new Response());
                }

                $controller->setProperties(\array_merge($properties, $data));

                /** @var ResponseInterface $result */
                $result = $controller->{$method}();
                if ($result instanceof ResponseInterface) {
                    $status = $result->getStatusCode();
                    return \array_merge($data, ['success' => $status >= 200 && $status < 300]);
                }
                return \array_merge($data, ['success' => false]);
            } catch (\Throwable $e) {
                return \array_merge($data, ['success' => false, 'error' => $e->getMessage()]);
            }
        };

        /** @var array[] $ids */
        if (!empty($ids)) {
            foreach ($ids as $pk) {
                if (\is_array($pk)) {
                    $results[] = $processSingle($pk);
                }
            }
        } else {
            $results[] = $processSingle();
        }

        unset($controller);
        $this->response = new Response();

        return $results;
    }

    protected function operationTurnOn(array $ids): array
    {
        return $this->processOperation('patch', $ids, ['is_active' => true]);
    }

    protected function operationTurnOff(array $ids): array
    {
        return $this->processOperation('patch', $ids, ['is_active' => false]);
    }

    protected function operationRemove(array $ids): array
    {
        return $this->processOperation('delete', $ids);
    }
}
