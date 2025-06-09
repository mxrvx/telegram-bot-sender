<?php

declare(strict_types=1);

namespace MXRVX\Telegram\Bot\Sender\Models;

class PostUser extends \xPDOObject
{
    public function save($cacheFlag = null)
    {
        if (parent::isNew()) {
            parent::set('created_at', \time());
        }

        if (parent::isDirty('is_send') && (int) parent::get('is_send') === 1) {
            parent::set('sended_at', \time());
        }

        return parent::save($cacheFlag);
    }
}
