<?php

declare(strict_types=1);

namespace MXRVX\Telegram\Bot\Sender;

use Longman\TelegramBot\Exception\TelegramException;
use MXRVX\Schema\System\Settings\SchemaConfigInterface;
use MXRVX\Autoloader\ClassLister;
use MXRVX\Telegram\Bot\App as Telegram;

class App
{
    public const NAMESPACE = 'mxrvx-telegram-bot-sender';
    public const ASSETS_URL = MODX_ASSETS_URL . 'components/' . self::NAMESPACE . '/';
    public const API_URL = self::ASSETS_URL . 'api/connector/';

    public SchemaConfigInterface $config;

    public function __construct(public \modX $modx, public Telegram $telegram)
    {
        $this->config = Config::make($modx->config);
    }

    public static function injectDependencies(\modX $modx): void
    {
        self::injectModelsWithNamespace($modx);
    }

    public static function getNamespaceCamelCase(): string
    {
        return \lcfirst(\str_replace(' ', '', \ucwords(\str_replace('-', ' ', App::NAMESPACE))));
    }

    /**
     * @throws TelegramException
     */
    public function getTelegram(): Telegram
    {
        return $this->telegram;
    }

    public function log(string $message): void
    {
        if (\method_exists($this->modx, 'log')) {
            $this->modx->log(\xPDO::LOG_LEVEL_ERROR, $message);
        }
    }

    private static function injectModelsWithNamespace(\modX $modx): void
    {
        $baseNamespace = \substr(self::class, 0, (int) \strrpos(self::class, '\\'));
        $modelNamespace = \sprintf('%s\Models\\', $baseNamespace);
        $modelPath = MODX_CORE_PATH . 'components/' . self::NAMESPACE . '/src/Models/' . self::NAMESPACE . '/' . self::NAMESPACE . '/';
        $modelPrefix = self::getNamespaceCamelCase();

        /** @var array<int, class-string> $namespaceClasses */
        $namespaceClasses = ClassLister::findByRegex('/^' . \preg_quote($modelNamespace, '/') . '(?!.*_mysql$).+$/');
        $namespaceClasses = \array_filter($namespaceClasses, static function ($class) {
            return !\str_starts_with($class, 'Model');
        });
        foreach ($namespaceClasses as $namespaceClass) {
            if (isset($modx->map[$namespaceClass])) {
                continue;
            }

            $shortClassName = \substr($namespaceClass, (int) \strrpos($namespaceClass, '\\') + 1);
            $legacyClassName = $modelPrefix . $shortClassName;

            if (!isset($modx->map[$legacyClassName])) {
                /** @psalm-suppress DeprecatedMethod */
                $modx->loadClass($legacyClassName, $modelPath, true, false);
            }

            if (isset($modx->map[$legacyClassName])) {
                $modx->map[$namespaceClass] = $modx->map[$legacyClassName];
            }
        }
    }
}
