<?php

declare(strict_types=1);

namespace MXRVX\Telegram\Bot\Sender;

use MXRVX\Schema\System\Settings;
use MXRVX\Schema\System\Settings\SchemaConfig;

class Config extends SchemaConfig
{
    public static function make(array $config): SchemaConfig
    {
        $schema = Settings\Schema::define(App::NAMESPACE)
            ->withSettings(
                [
                    Settings\Setting::define(
                        key: 'grid_post_fields',
                        value: ['id', 'title', 'status', 'created_at', 'updated_at', 'sended_at'],
                        xtype: 'textfield',
                        typecast: Settings\TypeCaster::ARRAY_STRING,
                    ),
                    Settings\Setting::define(
                        key: 'grid_user_fields',
                        value: ['id', 'username', 'status' ,'first_name', 'last_name', 'created_at', 'updated_at'],
                        xtype: 'textfield',
                        typecast: Settings\TypeCaster::ARRAY_STRING,
                    ),
                ],
            );
        return SchemaConfig::define($schema)->withConfig($config);
    }
}
