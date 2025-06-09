<?php


declare(strict_types=1);

namespace MXRVX\Telegram\Bot\Sender\Controllers\Modx;

use MXRVX\Telegram\Bot\Sender\App;

abstract class Controller extends \modExtraManagerController
{
    protected App $app;
    protected ?string $version = null;

    public function __construct(\modX &$modx, $config = [])
    {
        parent::__construct($modx, $config);
        $this->app = $modx->services[App::class] ??= new App($modx);

        /** @var \MXRVX\Autoloader\App $autoloader */
        $autoloader = $modx->services[\MXRVX\Autoloader\App::AUTOLOADER] ?? null;
        if ($autoloader instanceof \MXRVX\Autoloader\App) {
            if ($package = $autoloader->manager()->getPackage(App::NAMESPACE)) {
                $this->version = $package->version;
            }
        }
    }

    public function getVersionHash(): string
    {
        return '?v=' . \dechex(\crc32($this->version ?? \time()));
    }

    public function getLanguageTopics()
    {
        return [App::NAMESPACE . ':default'];
    }

    public function checkPermissions()
    {
        return true;
    }

    public function getPageTitle()
    {
        return App::NAMESPACE;
    }

    public function getTemplateFile()
    {
        return '';
    }

    public function addCss($script): void
    {
        parent::addCss($script . $this->getVersionHash());
    }

    public function addJavascript($script): void
    {
        parent::addJavascript($script . $this->getVersionHash());
    }

    public function addLastJavascript($script): void
    {
        parent::addLastJavascript($script . $this->getVersionHash());
    }
}
