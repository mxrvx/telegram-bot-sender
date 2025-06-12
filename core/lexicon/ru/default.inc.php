<?php

declare(strict_types=1);

/** @psalm-suppress MissingFile */
require_once MODX_CORE_PATH . 'vendor/autoload.php';

/** @var array<array-key, array<array-key,string>|string> $_tmp */
$_tmp = [
    'menu' => [
        'index' => [
            'text' => 'TelegramBotSender',
            'description' => '',
        ],
    ],
    'version' => [
        'current' => 'версия: {version}',
        'available' => 'доступна: {version}',
    ],
    'actions' => [
        'add' => 'Добавить',
        'create' => 'Создать',
        'cancel' => 'Отмена',
        'delete' => 'Удалить',
        'edit' => 'Редактировать',
        'copy' => 'Копировать',
        'remove' => 'Убрать',
        'save' => 'Сохранить',
        'submit' => 'Отправить',
        'close' => 'Закрыть',
        'ok' => 'Ok',
        'view' => 'Просмотр',
        'export' => 'Экспорт',
        'import' => 'Импорт',
        'send' => 'Отправить',
        'turnoff' => 'Выключить',
        'turnon' => 'Включить',
    ],
    'components' => [
        'confirm' => [
            'title' => 'Требуется подтверждение!',
            'message' => 'Вы уверены?',
        ],
    ],
    'models' => [
        'post' => [
            'title_one' => 'Пост',
            'title_many' => 'Посты',
            'id' => 'Id',
            'title' => 'Заголовок',
            'created_at' => 'Дата создания',
            'updated_at' => 'Дата обновления',
            'sended_at' => 'Дата отправки',
            'status' => 'Статус',
            'total' => 'Всего',
            'total_send' => 'Отправлено',
            'total_success_send' => 'Успешно',
        ],
        'user' => [
            'title_one' => 'Юзер',
            'title_many' => 'Юзеры',
            'id' => 'Id',
            'first_name' => 'Имя',
            'last_name' => 'Фамилия',
            'username' => 'Никнейм',
            'status' => 'Статус',
            'created_at' => 'Дата создания',
            'updated_at' => 'Дата обновления',
        ],
    ],
];

/** @var array<array-key, string> $_tmp */
$_tmp = \MXRVX\Telegram\Bot\Sender\Tools\Lexicon::flatten($_tmp, \MXRVX\Telegram\Bot\Sender\App::NAMESPACE);

/** @var array<array-key, string> $_lang */
if (isset($_lang)) {
    $_lang = \array_merge($_lang, $_tmp);
}

unset($_tmp);
