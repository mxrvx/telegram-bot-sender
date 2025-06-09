<?php

declare(strict_types=1);
$xpdo_meta_map['mxrvxTelegramBotSenderPostUser'] =  [
    'package' => 'mxrvx-telegram-bot-sender',
    'version' => '1.1',
    'table' => 'mxrvx_telegram_bot_sender_post_users',
    'extends' => 'xPDOObject',
    'tableMeta' =>
     [
         'engine' => 'InnoDB',
     ],
    'fields' =>
     [
         'post_id' => null,
         'user_id' => null,
         'is_send' => 0,
         'is_success' => 0,
         'created_at' => 0,
         'sended_at' => 0,
     ],
    'fieldMeta' =>
     [
         'post_id' =>
          [
              'dbtype' => 'int',
              'precision' => '20',
              'phptype' => 'integer',
              'null' => false,
              'attributes' => 'unsigned',
              'index' => 'pk',
          ],
         'user_id' =>
          [
              'dbtype' => 'bigint',
              'precision' => '20',
              'phptype' => 'integer',
              'null' => false,
              'attributes' => 'unsigned',
              'index' => 'pk',
          ],
         'is_send' =>
          [
              'dbtype' => 'tinyint',
              'precision' => '1',
              'phptype' => 'boolean',
              'attributes' => 'unsigned',
              'null' => false,
              'default' => 0,
          ],
         'is_success' =>
          [
              'dbtype' => 'tinyint',
              'precision' => '1',
              'phptype' => 'boolean',
              'attributes' => 'unsigned',
              'null' => false,
              'default' => 0,
          ],
         'created_at' =>
          [
              'dbtype' => 'int',
              'precision' => '20',
              'phptype' => 'timestamp',
              'null' => true,
              'default' => 0,
          ],
         'sended_at' =>
          [
              'dbtype' => 'int',
              'precision' => '20',
              'phptype' => 'timestamp',
              'null' => true,
              'default' => 0,
          ],
     ],
    'indexes' =>
     [
         'PRIMARY' =>
          [
              'alias' => 'PRIMARY',
              'primary' => true,
              'unique' => true,
              'type' => 'BTREE',
              'columns' =>
               [
                   'post_id' =>
                    [
                        'length' => '',
                        'collation' => 'A',
                        'null' => false,
                    ],
                   'user_id' =>
                    [
                        'length' => '',
                        'collation' => 'A',
                        'null' => false,
                    ],
               ],
          ],
         'is_send' =>
          [
              'alias' => 'is_send',
              'primary' => false,
              'unique' => false,
              'type' => 'BTREE',
              'columns' =>
               [
                   'is_send' =>
                    [
                        'length' => '',
                        'collation' => 'A',
                        'null' => false,
                    ],
               ],
          ],
         'is_success' =>
          [
              'alias' => 'is_success',
              'primary' => false,
              'unique' => false,
              'type' => 'BTREE',
              'columns' =>
               [
                   'is_success' =>
                    [
                        'length' => '',
                        'collation' => 'A',
                        'null' => false,
                    ],
               ],
          ],
         'created_at' =>
          [
              'alias' => 'created_at',
              'primary' => false,
              'unique' => false,
              'type' => 'BTREE',
              'columns' =>
               [
                   'created_at' =>
                    [
                        'length' => '',
                        'collation' => 'A',
                        'null' => false,
                    ],
               ],
          ],
         'sended_at' =>
          [
              'alias' => 'sended_at',
              'primary' => false,
              'unique' => false,
              'type' => 'BTREE',
              'columns' =>
               [
                   'sended_at' =>
                    [
                        'length' => '',
                        'collation' => 'A',
                        'null' => false,
                    ],
               ],
          ],
     ],
    'aggregates' =>
     [
         'Post' =>
          [
              'class' => 'mxrvxTelegramBotSenderPost',
              'local' => 'post_id',
              'foreign' => 'id',
              'cardinality' => 'one',
              'owner' => 'foreign',
          ],
         'User' =>
          [
              'class' => 'mxrvxTelegramBotUser',
              'local' => 'user_id',
              'foreign' => 'id',
              'cardinality' => 'one',
              'owner' => 'foreign',
          ],
     ],
];
