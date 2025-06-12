<?php

declare(strict_types=1);

namespace MXRVX\Telegram\Bot\Sender\Console;

use DI\Container;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\ListCommand;
use MXRVX\Telegram\Bot\Sender\App;
use MXRVX\Telegram\Bot\Sender\Console\Command\InstallCommand;
use MXRVX\Telegram\Bot\Sender\Console\Command\RemoveCommand;

class Console extends Application
{
    public function __construct(protected Container $container)
    {
        parent::__construct(App::NAMESPACE);
    }

    protected function getDefaultCommands(): array
    {
        return [
            new ListCommand(),
            new InstallCommand($this->container),
            new RemoveCommand($this->container),
        ];
    }
}
