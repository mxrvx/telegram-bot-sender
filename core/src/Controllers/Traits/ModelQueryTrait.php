<?php

declare(strict_types=1);

namespace MXRVX\Telegram\Bot\Sender\Controllers\Traits;

trait ModelQueryTrait
{
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
}
