<?php

declare(strict_types=1);

namespace MXRVX\Telegram\Bot\Sender\Controllers\Mgr;

use MXRVX\Telegram\Bot\Sender\Controllers\ModelController;
use MXRVX\Telegram\Bot\Models\User;

class Users extends ModelController
{
    protected string $model = User::class;
    protected string $alias = 'user';

    /** @var string|array<string> */
    protected string|array $primaryKey = 'id';

    protected string $defaultSortField = 'created_at';
    protected string $defaultSortDirection = 'desc';
    protected int $maxLimit = 100;

    /** @var array<string> */
    protected array $searchFields = ['id','first_name','last_name','username'];

    /*public function getActions(array $array): array
    {
        $actions = [];
        $actions[] = [
            'action' => 'delete',
            'multiple' => false,
            'menu' => true,
            'icon' => 'icon-trash',
        ];

        return $actions;
    }*/

    /*protected function operationDelete(array $ids): array
    {
        return $this->processOperation('delete', $ids);
    }*/
}
