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
    public ?Telegram $telegram = null;

    public function __construct(public \modX $modx)
    {
        $this->config = Config::make($modx->config);
        $this->loadModelsWithNamespace();
    }

    public function setTelegram(Telegram $telegram): self
    {
        $this->telegram = $telegram;
        return $this;
    }

    /**
     * @throws TelegramException
     */
    public function getTelegram(): ?Telegram
    {
        return $this->telegram;
    }

    public function log(string $message): void
    {
        if (\method_exists($this->modx, 'log')) {
            $this->modx->log(\xPDO::LOG_LEVEL_ERROR, $message);
        }
    }

    public function loadModelsWithNamespace(): void
    {
        $baseNamespace = \substr(self::class, 0, (int) \strrpos(self::class, '\\'));
        $modelNamespace = \sprintf('%s\Models\\', $baseNamespace);
        $modelPath = MODX_CORE_PATH . 'components/' . self::NAMESPACE . '/src/Models/' . self::NAMESPACE . '/' . self::NAMESPACE . '/';
        $modelPrefix = $this->namespaceToCamelCase(self::NAMESPACE);

        /** @var array<int, class-string> $namespaceClasses */
        $namespaceClasses = ClassLister::findByRegex('/^' . \preg_quote($modelNamespace, '/') . '(?!.*_mysql$).+$/');
        foreach ($namespaceClasses as $namespaceClass) {
            if (isset($this->modx->map[$namespaceClass])) {
                continue;
            }

            $shortClassName = \substr($namespaceClass, (int) \strrpos($namespaceClass, '\\') + 1);
            $legacyClassName = $modelPrefix . $shortClassName;

            if (!isset($this->modx->map[$legacyClassName])) {
                /** @psalm-suppress DeprecatedMethod */
                $this->modx->loadClass($legacyClassName, $modelPath, true, false);
            }

            if (isset($this->modx->map[$legacyClassName])) {
                $this->modx->map[$namespaceClass] = $this->modx->map[$legacyClassName];
            }
        }
    }

    protected function namespaceToCamelCase(string $namespace): string
    {
        return \lcfirst(\str_replace(' ', '', \ucwords(\str_replace('-', ' ', $namespace))));
    }
}
