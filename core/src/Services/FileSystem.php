<?php

declare(strict_types=1);

namespace MXRVX\Telegram\Bot\Sender\Services;

use League\Flysystem\Filesystem as BaseFilesystem;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\Local\LocalFilesystemAdapter;
use MXRVX\Telegram\Bot\Sender\App;

class FileSystem
{
    protected BaseFilesystem $filesystem;

    public function __construct()
    {
        $this->filesystem = new BaseFilesystem($this->getAdapter());
    }

    public function getBaseFilesystem(): BaseFilesystem
    {
        return $this->filesystem;
    }

    public function getFullPath(string $path): string
    {
        return \implode('/', [$this->getRoot(), $path]);
    }

    protected function getAdapter(): FilesystemAdapter
    {
        return new LocalFilesystemAdapter($this->getRoot());
    }

    protected function getRoot(): string
    {
        return MODX_ASSETS_PATH . 'components/' . App::NAMESPACE . '/upload';
    }
}
