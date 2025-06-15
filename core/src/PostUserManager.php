<?php

declare(strict_types=1);

namespace MXRVX\Telegram\Bot\Sender;

use MXRVX\Telegram\Bot\Sender\Models\PostUser;
use MXRVX\Telegram\Bot\Sender\Services\Sender;

class PostUserManager
{
    public static function load(App $app, ?\Closure $callback = null, int $limit = 100): void
    {
        if ($callback === null) {
            $callback = [self::class, 'sendPost'];
        }

        $modx = $app->modx;
        $c = $modx->newQuery(PostUser::class);
        $c->where([
            'is_send' => false,
        ]);
        $c->sortby('created_at', 'ASC');
        $c->select('post_id, user_id');
        $page = 1;

        while (true) {
            $offset = ($page - 1) * $limit;

            $q = clone $c;
            $q->limit($limit, $offset);
            $q->prepare();
            /** @var \PDOStatement $stmt */
            $stmt = $modx->prepare($q->toSQL());
            if ($stmt instanceof \PDOStatement) {
                if ($stmt->execute()) {
                    if ($stmt->rowCount() > 0) {
                        while ($pk = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                            if ($o = $modx->getObject(PostUser::class, $pk, false)) {
                                try {
                                    $callback($app, $o);
                                } catch (\Throwable  $e) {
                                    $modx->log(\modX::LOG_LEVEL_ERROR, \var_export($e->getMessage(), true));
                                }
                            }
                        }
                    } else {
                        break;
                    }
                } else {
                    $modx->log(\xPDO::LOG_LEVEL_ERROR, \var_export($stmt->errorInfo(), true));
                    break;
                }

                $stmt->closeCursor();
            } else {
                $modx->log(\xPDO::LOG_LEVEL_ERROR, \var_export($modx->errorInfo(), true));
                break;
            }

            ++$page;
        }
    }

    public static function clean(App $app, ?\Closure $callback = null): void
    {
        if ($callback === null) {
            $callback = [self::class, 'cleanEvent'];
        }

        try {
            $callback($app);
        } catch (\Throwable  $e) {
            $app->modx->log(\modX::LOG_LEVEL_ERROR, \var_export($e->getMessage(), true));
        }
    }

    public static function sendPost(App $app, PostUser $o): void
    {
        (new Sender($app, $o))->run();
    }

    public static function cleanEvent(App $app): void
    {
        $modx = $app->modx;

        $modx->removeCollection(PostUser::class, [
            'is_send' => true,
        ]);
    }
}
