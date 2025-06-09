<?php

declare(strict_types=1);

namespace MXRVX\Telegram\Bot\Sender\Models;

use MXRVX\Telegram\Bot\Models\User;

class Post extends \xPDOSimpleObject
{
    public function save($cacheFlag = null)
    {
        if (parent::isNew()) {
            parent::set('created_at', \time());
            parent::set('is_send', false);
            parent::set('updated_at', null);
            parent::set('sended_at', null);
        } else {
            parent::set('updated_at', \time());
        }

        if (parent::isDirty('is_send') && (int) parent::get('is_send') === 1) {
            if ($this->makeSend()) {
                parent::set('sended_at', \time());
            } else {
                parent::set('is_send', 0);
            }
        }

        $result = parent::save($cacheFlag);

        return $result;
    }

    public function makeSend(): bool
    {
        $blocks = parent::get('content')['blocks'] ?? [];
        if (empty($blocks)) {
            return false;
        }

        $postId = (int) parent::get('id');
        $tablePostUser = $this->xpdo->getTableName(PostUser::class);
        $tableUser = $this->xpdo->getTableName(User::class);
        $columns = $this->xpdo->getSelectColumns(PostUser::class);
        $statuses = "'" . \implode("','", [User::STATUS_UNKNOWN, User::STATUS_KICKED, User::STATUS_LEFT]) . "'";

        $sql = \sprintf(
            <<<SQL
                INSERT INTO %s (%s)
                SELECT %d, id, 0, 0, UNIX_TIMESTAMP(), 0
                FROM %s u
                WHERE u.status NOT IN (%s) AND
                NOT EXISTS (
                    SELECT 1 FROM %s pu WHERE pu.post_id = %d AND pu.user_id = u.id
                )
                SQL,
            $tablePostUser,
            $columns,
            $postId,
            $tableUser,
            $statuses,
            $tablePostUser,
            $postId,
        );

        $stmt = $this->xpdo->prepare($sql);
        if ($stmt instanceof \PDOStatement) {
            if ($stmt->execute()) {
                return true;
            }
            $this->xpdo->log(\xPDO::LOG_LEVEL_ERROR, \sprintf('Error: %s SQL: %s', \var_export($stmt->errorInfo(), true), $sql));
        } else {
            $this->xpdo->log(\xPDO::LOG_LEVEL_ERROR, \sprintf('Error: %s SQL: %s', $this->xpdo->errorInfo(), $sql));
        }

        return false;
    }
}
