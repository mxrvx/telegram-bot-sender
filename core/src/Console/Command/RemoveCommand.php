<?php

declare(strict_types=1);

namespace MXRVX\Telegram\Bot\Sender\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use MXRVX\Telegram\Bot\Sender\App;

class RemoveCommand extends Command
{
    protected static $defaultName = 'remove';
    protected static $defaultDescription = 'Remove "' . App::NAMESPACE . '" extra from MODX';

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $modx = $this->app->modx;

        $corePath = MODX_CORE_PATH . 'components/' . App::NAMESPACE;
        if (\is_dir($corePath)) {
            \unlink($corePath);
            $output->writeln('<info>Removed symlink for `core`</info>');
        }
        $assetsPath = MODX_ASSETS_PATH . 'components/' . App::NAMESPACE;
        if (\is_dir($assetsPath)) {
            \unlink($assetsPath);
            $output->writeln('<info>Removed symlink for `assets`</info>');
        }
        /** @var \modNamespace $namespace */
        if ($namespace = $modx->getObject(\modNamespace::class, ['name' => App::NAMESPACE])) {
            $namespace->remove();
            $output->writeln(\sprintf('<info>Removed namespace `%s`</info>', App::NAMESPACE));
        }
        /** @var \modMenu $menu */
        if ($menu = $modx->getObject(\modMenu::class, ['namespace' => App::NAMESPACE])) {
            $menu->remove();
            $output->writeln(\sprintf('<info>Removed menu `%s`</info>', App::NAMESPACE));
        }

        $output->writeln('<info>Cleared MODX cache</info>');

        return Command::SUCCESS;
    }
}
