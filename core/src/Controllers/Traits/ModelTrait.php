<?php

declare(strict_types=1);

namespace MXRVX\Telegram\Bot\Sender\Controllers\Traits;

use Psr\Http\Message\ResponseInterface;

trait ModelTrait
{
    /**
     * GET ACTIONS
     */
    public function get(): ResponseInterface
    {
        $modx = $this->modx;

        $c = $this->createQuery();
        $c = $this->prepareQuery($c);

        if ($pk = $this->getPrimaryColumnValue()) {
            $c->where($pk);
            $c->limit(1);
            $c = $this->beforeGet($c);

            $row = [];

            $stmt = $c->prepare();
            if ($stmt instanceof \PDOStatement) {
                if ($stmt->execute() && $modx->getCount($this->model, $c)) {
                    $row = $stmt->fetch(\PDO::FETCH_ASSOC);
                } else {
                    $modx->log(\modX::LOG_LEVEL_ERROR, \sprintf('Error: %s SQL: %s', \var_export($stmt->errorInfo(), true), $c->toSQL()));
                }
            } else {
                $modx->log(\modX::LOG_LEVEL_ERROR, \sprintf('Error: %s SQL: %s', (string) $modx->errorInfo(), $c->toSQL()));
            }

            if ($row) {
                $data = $this->prepareRow($row);

                return $this->success($data);
            }

            return $this->failure('Could not find a record', 404);
        }

        $c = $this->beforeCount($c);
        $c = $this->addSorting($c);
        $c = $this->afterCount($c);

        $rows = [];
        $stmt = $c->prepare();

        if ($stmt instanceof \PDOStatement) {
            if ($stmt->execute()) {
                /** @var array<array-key, mixed> $row */
                while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                    $rows[] = $this->prepareRow($row);
                }
            } else {
                $modx->log(\modX::LOG_LEVEL_ERROR, \sprintf('Error: %s SQL: %s', \var_export($stmt->errorInfo(), true), $c->toSQL()));
            }
        } else {
            $modx->log(\modX::LOG_LEVEL_ERROR, \sprintf('Error: %s SQL: %s', (string) $modx->errorInfo(), $c->toSQL()));
        }

        $total = (int) $this->getProperty('total');
        $data = $this->prepareList([
            'total' => !empty($total) ? $total : \count($rows),
            'results' => $rows,
        ]);

        return $this->success($data);
    }

    /**
     * PUT ACTIONS
     */
    public function put(): ResponseInterface
    {
        $record = $this->modx->newObject($this->model);
        if ($record instanceof \xPDOObject) {
            $data = \array_replace($record->toArray('', true), $this->getProperties(), \array_fill_keys($this->getPrimaryKey(), null));
            $record->fromArray($data, '', true, true);

            if ($check = $this->beforeSave($record)) {
                return $check;
            }

            if (!$record->save()) {
                return $this->failure();
            }

            $record = $this->afterSave($record);
            $data = $record->toArray('', true);
            return $this->success($this->prepareRow($data));
        }
        return $this->failure();
    }

    /**
     * PATCH ACTIONS
     */
    public function patch(): ResponseInterface
    {
        if (!$pk = $this->getPrimaryKeyValue()) {
            return $this->failure('You must specify the primary key of object');
        }

        /** @var \xPDOObject $record */
        $record = $this->modx->getObject($this->model, $pk);
        if ($record instanceof \xPDOObject) {
            $data = \array_replace($record->toArray('', true), $this->getProperties());
            $record->fromArray($data, '', false, false);

            if ($check = $this->beforeSave($record)) {
                return $check;
            }

            if (!$record->save()) {
                return $this->failure();
            }

            $record = $this->afterSave($record);
            $data = $record->toArray('', true);

            return $this->success($this->prepareRow($data));
        }

        return $this->failure('Could not find a record', 404);
    }

    /**
     * DELETE ACTIONS
     */
    public function delete(): ResponseInterface
    {
        if (!$pk = $this->getPrimaryKeyValue()) {
            return $this->failure('You must specify the primary key of object');
        }

        /** @var \xPDOObject $record */
        $record = $this->modx->getObject($this->model, $pk);
        if ($record instanceof \xPDOObject) {
            if ($check = $this->beforeDelete($record)) {
                return $check;
            }

            if (!$record->remove()) {
                return $this->failure();
            }

            $record = $this->afterDelete($record);
            $data = $record->toArray('', true);

            return $this->success($this->prepareRow($data));
        }

        return $this->failure('Could not find a record', 404);
    }

    public function prepareList(array $array): array
    {
        return $array;
    }

    /**
     * @psalm-suppress MixedArgument
     * @psalm-suppress MixedAssignment
     * @psalm-suppress RedundantCondition
     *
     * @return array<array-key, mixed>
     */
    public function prepareRow(array $array): array
    {
        $expanded = [];

        foreach ($array as $k => $v) {
            // Extract JSON fields
            if (!empty($v) && \is_string($v) && ($v[0] === '[' || $v[0] === '{')) {
                $tmp = \json_decode($v, true);

                if (\is_array($tmp)) {
                    $array[$k] = $tmp;
                }
            }

            if (\is_string($k) && false !== $d = \strpos($k, '.')) {
                $kbefore = \substr($k, 0, $d);
                $kafter = \substr($k, 1 + $d);

                $expanded[$kbefore] = $kbefore;

                if (!isset($array[$kbefore]) || !\is_array($array[$kbefore])) {
                    $array[$kbefore] = [];
                }

                if (\is_array($array[$kbefore])) {
                    $array[$kbefore][$kafter] = $v;
                    unset($array[$k]);
                }

            }
        }

        foreach ($expanded as $k) {
            if (isset($array[$k]) && empty(\array_filter($array[$k]))) {
                $array[$k] = null;
            }
        }

        $expand = $this->getArrayProperty('expand');

        if (\in_array('actions', $expand, true)) {
            $array['actions'] = $this->getActions($array);
        }

        return $array;
    }

    public function getActions(array $array): array
    {
        return [];
    }

    protected function createQuery(): \xPDOQuery
    {
        $c = $this->modx->newQuery($this->model);
        $c->setClassAlias($this->alias);
        $c->select(
            $this->modx->getSelectColumns($this->model, $this->alias, '', [], false),
        );

        return $c;
    }

    protected function prepareQuery(\xPDOQuery $c): \xPDOQuery
    {
        return $c;
    }

    protected function beforeGet(\xPDOQuery $c): \xPDOQuery
    {
        return $c;
    }

    protected function beforeCount(\xPDOQuery $c): \xPDOQuery
    {
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

        return $c;
    }

    /**
     * @psalm-suppress MixedArgument
     * @psalm-suppress MixedAssignment
     * @psalm-suppress MixedArrayAssignment
     */
    protected function afterCount(\xPDOQuery $c): \xPDOQuery
    {
        $modx = $this->modx;

        $primaryKey = $this->getPrimaryKey();
        $primaryColumn = $this->getPrimaryColumn();

        $c->groupby(\implode(',', $primaryColumn));

        /**
         * @var array{
         *     command: string,
         *     distinct: string,
         *     columns: string,
         *     from: array{
         *         tables: array,
         *         joins: array
         *     },
         *     set: array,
         *     where: array,
         *     groupby: array,
         *     sortby: array,
         *     having: array,
         *     orderby: array,
         *     offset: string,
         *     limit: string
         * } $q->query
         */
        $q = clone $c;

        $columns = $primaryColumn;

        /** @var array<array<string, mixed>> $sorts */
        if ($sorts = $q->query['sortby'] ?? []) {
            foreach ($sorts as $sort) {
                if (\is_array($sort)) {
                    $column = $sort['column'] ?? '';
                    if (!empty($column) && \is_string($column)) {
                        $columns[] = $column;
                    }
                }
            }
        }

        /** @var string[] $columns */
        $columns = \array_values(\array_unique($columns));
        $q->query['columns'] = ['SQL_CALC_FOUND_ROWS ' . \implode(',', $columns)];

        foreach ($primaryColumn as $column) {
            $q->query['sortby'][] = [
                'column' => $column,
                'direction' => 'asc',
            ];
            $q->query['groupby'][] = [
                'column' => $column,
                'direction' => '',
            ];
        }

        $limit = (int) $this->getProperty('limit');
        if ($this->maxLimit !== 0) {
            $limit = \min($limit > 0 ? $limit : $this->maxLimit, $this->maxLimit);
        }

        $offset = (int) $this->getProperty('start', 0);

        if ($limit > 0) {
            $q->limit($limit, $offset);
        }

        /** @var string[] $ids */
        $ids = [];
        $total = 0;
        $stmt = $q->prepare();
        if ($stmt instanceof \PDOStatement) {
            if ($stmt->execute()) {
                $ids = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                $ids = \array_map(static function (array $item) use ($primaryKey) {
                    return \array_intersect_key($item, \array_flip($primaryKey));
                }, $ids);

                /** @var \PDOStatement|false $countStmt */
                $countStmt = $modx->query('SELECT FOUND_ROWS()');
                if ($countStmt instanceof \PDOStatement) {
                    $total = (int) $countStmt->fetchColumn();
                }
            } else {
                $modx->log(\modX::LOG_LEVEL_ERROR, \sprintf('Error: %s SQL: %s', \var_export($stmt->errorInfo(), true), $q->toSQL()));
            }
        } else {
            $modx->log(\modX::LOG_LEVEL_ERROR, \sprintf('Error: %s SQL: %s', (string) $modx->errorInfo(), $q->toSQL()));
        }

        $this->setProperty('total', $total);

        if (empty($total)) {
            $ids = [\array_fill_keys($primaryKey, '')];
        }

        $primarySet = \array_map(static function ($row) use ($primaryKey) {
            $values = [];
            foreach ($primaryKey as $k) {
                if (isset($row[$k])) {
                    $values[] = \is_string($row[$k]) ? "'" . \addslashes($row[$k]) . "'" : $row[$k];
                } else {
                    $values[] = '';
                }
            }
            return '(' . \implode(', ', $values) . ')';
        }, $ids);

        $c->query['where'] = [
            [
                new \xPDOQueryCondition(
                    [
                        'sql' => \sprintf('(%s) IN (%s)', \implode('","', $primaryColumn), \implode(',', $primarySet)),
                        'conjunction' => 'AND',
                    ],
                ),
            ],
        ];


        foreach ($this->getPrimaryKeyWithColumn() as $key => $column) {
            /** @var string[] $sortValues */
            $sortValues = \array_reverse(\array_column($ids, $key));
            $c->query['sortby'] = [
                [
                    'column' => \sprintf('FIELD (%s, %s)', $column, '"' . \implode('","', $sortValues) . '"'),
                    'direction' => 'DESC',
                ],
            ];
        }

        return $c;
    }

    protected function addSorting(\xPDOQuery $c): \xPDOQuery
    {
        if ($sort = (string) $this->getProperty('sort', $this->defaultSortField)) {
            if (\mb_strpos($sort, '.') !== false) {
                [$alias, $field] = \explode('.', $sort);
            } else {
                $alias = $this->alias;
                $field = $sort;
            }

            $sort = \sprintf('`%s`.`%s`', $alias, $field);
            $c->sortby(
                $sort,
                \strtolower((string) $this->getProperty('dir', $this->defaultSortDirection)) === 'desc' ? 'desc' : 'asc',
            );
        }

        return $c;
    }

    protected function beforeSave(\xPDOObject $record): ?ResponseInterface
    {
        return null;
    }

    protected function afterSave(\xPDOObject $record): \xPDOObject
    {
        return $record;
    }

    protected function beforeDelete(\xPDOObject $record): ?ResponseInterface
    {
        return null;
    }

    protected function afterDelete(\xPDOObject $record): \xPDOObject
    {
        return $record;
    }

    /**
     * @return array<string, string>
     */
    protected function getPrimaryKeyWithColumn(): array
    {
        $keys = [];
        foreach (\is_array($this->primaryKey) ? $this->primaryKey : [$this->primaryKey] as $key) {
            $keys[(string) $key] = \sprintf('`%s`.`%s`', $this->alias, $key);
        }

        return $keys;
    }

    /**
     * @return string[]
     */
    protected function getPrimaryKey(): array
    {
        return \array_keys($this->getPrimaryKeyWithColumn());
    }

    /**
     * @return string[]
     */
    protected function getPrimaryColumn(): array
    {
        return \array_values($this->getPrimaryKeyWithColumn());
    }

    protected function getPrimaryKeyValue(?array $properties = null): array
    {
        $values = [];
        $keys = $this->getPrimaryKey();
        foreach ($keys as $key) {
            if (isset($properties)) {
                $values[$key] = (string) ($properties[$key] ?? '');
            } else {
                if ($this->route) {
                    $values[$key] = $this->route->getArgument($key, (string) $this->getProperty($key));
                }
            }
        }

        $values = \array_filter($values, static function ($v) {
            return $v !== null && $v !== '';
        });

        return \count($values) === \count($keys) ? $values : [];
    }

    protected function getPrimaryColumnValue(?array $properties = null): array
    {
        $values = [];
        $keys = $this->getPrimaryKeyWithColumn();
        foreach ($keys as $key => $column) {
            if (isset($properties)) {
                $values[$column] = (string) ($properties[$key] ?? '');
            } else {
                if ($this->route) {
                    $values[$column] = $this->route->getArgument($key, (string) $this->getProperty($key));
                }
            }
        }

        $values = \array_filter($values, static function ($v) {
            return $v !== null && $v !== '';
        });

        return \count($values) === \count($keys) ? $values : [];
    }
}
