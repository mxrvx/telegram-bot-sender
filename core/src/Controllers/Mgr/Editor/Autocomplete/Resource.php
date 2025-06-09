<?php

declare(strict_types=1);

namespace MXRVX\Telegram\Bot\Sender\Controllers\Mgr\Editor\Autocomplete;

use MXRVX\Telegram\Bot\Sender\Controllers\Controller;
use Psr\Http\Message\ResponseInterface;

class Resource extends Controller
{
    protected string $model = \modResource::class;
    protected string $alias = 'resource';

    /** @var string[] */
    protected array $searchFields = ['pagetitle', 'longtitle'];

    public function get(): ResponseInterface
    {
        $modx = $this->modx;
        $c = $modx->newQuery($this->model);
        $c->setClassAlias($this->alias);
        $c->select(
            $modx->getSelectColumns($this->model, $this->alias, '', ['id', 'pagetitle', 'longtitle'], false),
        );

        $query = \trim((string) $this->getProperty('query', ''));

        if ($query !== '') {
            $conditions = [];
            $or = '';


            foreach ($this->searchFields as $field) {
                if (\is_string($field)) {
                    if (!\str_contains($field, '.')) {
                        $field = \sprintf('`%s`.`%s`', $this->alias, $field);
                    }
                    $conditions[$or . $field . ':LIKE'] = '%' . $query . '%';
                    $or = 'OR:';
                }
            }

            $c->where([$conditions]);
        }

        $rows = [];
        $stmt = $c->prepare();

        if ($stmt instanceof \PDOStatement) {
            if ($stmt->execute()) {
                while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                    $rows[] = $this->prepareDataOut($row);
                }
            } else {
                $modx->log(\modX::LOG_LEVEL_ERROR, \sprintf('Error: %s SQL: %s', \var_export($stmt->errorInfo(), true), $c->toSQL()));
            }
        } else {
            $modx->log(\modX::LOG_LEVEL_ERROR, \sprintf('Error: %s SQL: %s', (string) $modx->errorInfo(), $c->toSQL()));
        }

        return $this->success([
            'results' => $rows,
        ]);
    }

    public function prepareDataOut(array $row): array
    {
        $row['name'] = (string) ($row['pagetitle'] ?? '');
        if (!empty($row['id'])) {
            $row['href'] = $this->modx->makeUrl((int) $row['id'], '', '', 'full');
        }

        return $row;
    }
}
