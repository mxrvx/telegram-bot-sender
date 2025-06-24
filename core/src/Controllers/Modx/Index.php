<?php


declare(strict_types=1);

namespace MXRVX\Telegram\Bot\Sender\Controllers\Modx;

use MXRVX\Telegram\Bot\Sender\App;
use MXRVX\Telegram\Bot\Sender\AssetsManager;
use MXRVX\Telegram\Bot\Sender\Tools\Lexicon;

class Index extends Controller
{
    public function loadCustomCssJs(): void
    {
        $locale = $this->modx->getOption('manager_language', $_SESSION, $this->modx->getOption('cultureKey')) ?: 'en';

        $config = [
            'locale' => $locale,
            'lexicon' => Lexicon::items($locale),
            'api_url' => App::API_URL,
            'grid_post_fields' => $this->getGridFieldsForPost(),
            'grid_user_fields' => $this->getGridFieldsForUser(),
        ];

        $this->addHtml(\sprintf('<script>window["%s"]=%s;</script>', App::NAMESPACE, \json_encode($config, \JSON_THROW_ON_ERROR | \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE)));
        AssetsManager::registerAssets($this);
    }

    public function getGridFieldsForPost(): array
    {
        $fields = $this->app->config->getSettingValue('grid_post_fields');

        return \is_array($fields) ? $fields : ['id'];
    }

    public function getGridFieldsForUser(): array
    {
        $fields = $this->app->config->getSettingValue('grid_user_fields');

        return \is_array($fields) ? $fields : ['id'];
    }
}
