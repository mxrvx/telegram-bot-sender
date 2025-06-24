<?php

declare(strict_types=1);

namespace MXRVX\Telegram\Bot\Sender\Controllers\Mgr;

use MXRVX\Telegram\Bot\Sender\Controllers\ModelController;
use MXRVX\Telegram\Bot\Sender\Models\Post;
use MXRVX\Telegram\Bot\Sender\Models\PostUser;

class Posts extends ModelController
{
    /** @var class-string<Post> */
    protected string $model = Post::class;

    protected string $alias = 'post';

    /** @var string|array<string> */
    protected array|string $primaryKey = Post::FIELD_ID;

    protected string $defaultSortField = Post::FIELD_ID;
    protected string $defaultSortDirection = 'desc';
    protected int $maxLimit = 100;

    /** @var array<string> */
    protected array $searchFields = [Post::FIELD_TITLE];

    public function getActions(array $array): array
    {
        $actions = [];

        $actions[] = [
            'action' => 'edit',
            'menu' => true,
            'button' => true,
            'icon' => 'icon-edit',
        ];
        $actions[] = [
            'action' => 'copy',
            'multiple' => false,
            'menu' => true,
            'icon' => 'icon-copy',
        ];

        if (empty($array['is_active'])) {
            $actions[] = [
                'action' => 'turnon',
                'multiple' => true,
                'menu' => true,
                'button' => true,
                'icon' => 'icon-toggle-off',
            ];
        } else {
            $actions[] = [
                'action' => 'turnoff',
                'multiple' => true,
                'menu' => true,
                'button' => true,
                'icon' => 'icon-toggle-on',
            ];

        }

        if (!empty($array['is_active']) && empty($array['is_send'])) {
            $actions[] = [
                'action' => 'send',
                'multiple' => true,
                'menu' => true,
                'button' => true,
                'icon' => 'icon-send',
            ];
        }

        $actions[] = [
            'action' => 'sep',
            'menu' => true,
        ];

        $actions[] = [
            'action' => 'delete',
            'multiple' => false,
            'menu' => true,
            'icon' => 'icon-trash',
        ];

        return $actions;
    }

    protected function beforeCount(\xPDOQuery $c): \xPDOQuery
    {
        $c = parent::beforeCount($c);

        $c->leftJoin(PostUser::class, 'PostUsers');

        $fields = [
            'total' => 'COUNT(PostUsers.user_id)',
            'total_send' => 'COUNT(IF(PostUsers.is_send = 1, PostUsers.post_id, NULL))',
            'total_success_send' => 'COUNT(IF(PostUsers.is_send = 1 AND PostUsers.is_success = 1, PostUsers.post_id, NULL))',
        ];
        $c->select(\implode(', ', \array_map(
            static fn($alias, $expr) => "$expr AS $alias",
            \array_keys($fields),
            $fields,
        )));

        return $c;
    }

    protected function operationSend(array $ids): array
    {
        return $this->processOperation('patch', $ids, ['is_send' => true]);
    }

    protected function operationDelete(array $ids): array
    {
        return $this->processOperation('delete', $ids);
    }

    protected function operationCopy(array $ids): array
    {
        $pk = $this->getPrimaryKeyValue((array) \reset($ids));

        /** @var Post $record */
        $record = $this->model::getInstance($pk);
        if ($record) {
            $record
                ->setTitle(\sprintf('Копия: %s', $record->getTitle()))
                ->setIsActive(true)
                ->setIsSend(false)
                ->setCreatedAt(\time())
                ->setUpdatedAt(null)
                ->setSendedAt(null);

            $data = $record->toMetaData();

            return $this->processOperation('put', $ids, $data);
        }

        return ['success' => true];
    }
}
