<?php

declare(strict_types=1);
$xpdo_meta_map['mxrvxTelegramBotSenderPost'] =  [
    'package' => 'mxrvx-telegram-bot-sender',
    'version' => '1.1',
    'table' => 'mxrvx_telegram_bot_sender_posts',
    'extends' => 'xPDOSimpleObject',
    'tableMeta' =>
     [
         'engine' => 'InnoDB',
     ],
    'fields' =>
     [
         'title' => '',
         'is_active' => 1,
         'is_send' => 0,
         'created_at' => 0,
         'updated_at' => 0,
         'sended_at' => 0,
         'content' => null,
     ],
    'fieldMeta' =>
     [
         'title' =>
          [
              'dbtype' => 'varchar',
              'precision' => '191',
              'phptype' => 'string',
              'null' => false,
              'default' => '',
          ],
         'is_active' =>
          [
              'dbtype' => 'tinyint',
              'precision' => '1',
              'phptype' => 'boolean',
              'attributes' => 'unsigned',
              'null' => false,
              'default' => 1,
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
         'created_at' =>
          [
              'dbtype' => 'int',
              'precision' => '20',
              'phptype' => 'timestamp',
              'null' => true,
              'default' => 0,
          ],
         'updated_at' =>
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
         'content' =>
          [
              'dbtype' => 'text',
              'phptype' => 'json',
              'null' => true,
          ],
     ],
    'indexes' =>
     [
         'title' =>
          [
              'alias' => 'title',
              'primary' => false,
              'unique' => false,
              'type' => 'BTREE',
              'columns' =>
               [
                   'title' =>
                    [
                        'length' => '',
                        'collation' => 'A',
                        'null' => false,
                    ],
               ],
          ],
         'is_active' =>
          [
              'alias' => 'is_active',
              'primary' => false,
              'unique' => false,
              'type' => 'BTREE',
              'columns' =>
               [
                   'is_active' =>
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
         'updated_at' =>
          [
              'alias' => 'updated_at',
              'primary' => false,
              'unique' => false,
              'type' => 'BTREE',
              'columns' =>
               [
                   'updated_at' =>
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
    'composites' =>
     [
         'PostUsers' =>
          [
              'class' => 'mxrvxTelegramBotSenderPostUser',
              'local' => 'id',
              'foreign' => 'post_id',
              'cardinality' => 'many',
              'owner' => 'local',
          ],
     ],
];
