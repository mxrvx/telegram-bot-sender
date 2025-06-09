<?php

declare(strict_types=1);


/** @var array<array-key, array<array-key,string>|string> $_tmp */
$_tmp = [
    'grid_post_fields' => 'Поля таблицы `Посты`',
    'grid_post_fields_desc' => 'Список видимых полей таблицы `Посты`, через запятую',
    'grid_user_fields' => 'Поля таблицы `Юзеры`',
    'grid_user_fields_desc' => 'Список видимых полей таблицы `Юзеры`, через запятую',
];

/** @var array<array-key, string> $_tmp */
$_tmp = MXRVX\Telegram\Bot\Sender\Tools\Lexicon::flatten($_tmp, 'setting_' . MXRVX\Telegram\Bot\Sender\App::NAMESPACE);

/** @var array<array-key, string> $_lang */
if (isset($_lang)) {
    $_lang = \array_merge($_lang, $_tmp);
}

unset($_tmp);
