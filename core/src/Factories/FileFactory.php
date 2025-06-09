<?php

declare(strict_types=1);

namespace MXRVX\Telegram\Bot\Sender\Contracts;

use MXRVX\Telegram\Bot\Sender\Services\FileSystem;
use Psr\Http\Message\UploadedFileInterface;
use Slim\Psr7\Stream;
use Slim\Psr7\UploadedFile;

class FileFactory
{
    private UploadedFileInterface $file;
    private string $originalName;
    private string $mimeType;
    private string $fileName;
    private string $filePath;
    private string $fullPath;
    private int $size = 0;
    private int $width = 0;
    private int $height = 0;
    private ?string $destinationPath = null;
    private ?string $destinationUrl = null;
    private ?FileSystem $filesystem = null;

    public function __construct(string|UploadedFile $file, ?array $metadata = null)
    {
        $this->file = $this->normalizeFile($file, $metadata);
        $this->originalName = (string) $this->file->getClientFilename();
        $this->mimeType = (string) $this->file->getClientMediaType();
        $this->size = (int) $this->file->getSize();

        $this->fileName = $this->getSaveName($this->originalName, $this->mimeType);
        $this->filePath = $this->getSavePath($this->fileName, $this->mimeType);
        $this->fullPath = $this->filePath . '/' . $this->fileName;
    }

    public function save(): ?FileFactory
    {
        $fs = $this->getFilesystem()->getBaseFilesystem();

        if ($stream = $this->file->getStream()) {
            $stream->rewind();
            $stream = $stream->detach();
            $fs->writeStream($this->fullPath, $stream);
            if ($stream) {
                \fclose($stream);
            }

            if ($fs->fileExists($this->fullPath)) {
                if ($this->size <= 52428800 && $this->isImage() && $contents = $fs->read($this->fullPath)) {
                    if ($imageSize = \getimagesizefromstring($contents)) {
                        $this->width = $imageSize[0];
                        $this->height = $imageSize[1];
                    }
                }
                $this->destinationPath = $this->getFilesystem()->getFullPath($this->fullPath);
                $this->destinationUrl = \trim(MODX_SITE_URL, '/') . \str_replace(MODX_BASE_PATH, MODX_BASE_URL, $this->destinationPath);

                return $this;
            }
        }

        return null;
    }

    public function isImage(): bool
    {
        return \str_starts_with($this->mimeType, 'image/');
    }

    public function isVideo(): bool
    {
        return \str_starts_with($this->mimeType, 'video/');
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function getWidth(): int
    {
        return $this->width;
    }

    public function getHeight(): int
    {
        return $this->height;
    }

    public function getOriginalName(): string
    {
        return $this->originalName;
    }

    public function getMimeType(): string
    {
        return $this->mimeType;
    }

    public function getFileName(): string
    {
        return $this->fileName;
    }

    public function getFilePath(): string
    {
        return $this->filePath;
    }

    public function getFullPath(): string
    {
        return $this->fullPath;
    }

    public function getDestinationPath(): ?string
    {
        return $this->destinationPath;
    }

    public function getDestinationUrl(): ?string
    {
        return $this->destinationUrl;
    }

    public function getFilesystem(): FileSystem
    {
        if (!$this->filesystem) {
            $this->filesystem = new FileSystem();
        }

        return $this->filesystem;
    }

    protected function normalizeFile(string|UploadedFile $file, ?array $metadata = []): UploadedFile
    {
        if (\is_string($file)) {
            if (!\strpos($file, ';base64,')) {
                throw new \InvalidArgumentException('Could not parse base64 string');
            }
            $stream = \fopen('php://temp', 'rb+');
            [$mime, $data] = \explode(',', $file);
            \fwrite($stream, \base64_decode($data));
            \fseek($stream, 0);
            $stream = new Stream($stream);

            $file = new UploadedFile(
                $stream,
                !empty($metadata['name']) ? (string) $metadata['name'] : '',
                \str_replace(['data:', ';base64'], '', $mime),
                $stream->getSize(),
            );
        }

        return $file;
    }

    protected function getSaveName(?string $filename = null, ?string $mime = null): string
    {
        $ext = null;
        if ($filename && $tmp = \pathinfo($filename, PATHINFO_EXTENSION)) {
            $ext = \strtolower($tmp);
        }
        if (!$ext && $mime && ($tmp = \explode('/', \strtolower($mime))) && \count($tmp) === 2) {
            $ext = $tmp[1];
        }

        $name = \uniqid('', true);
        if ($ext) {
            if ($ext === 'jpeg') {
                $ext = 'jpg';
            }
            $name .= '.' . $ext;
        }

        return $name;
    }

    protected function getSavePath(string $filename, ?string $mime = null): string
    {
        return \strlen($filename) >= 3
            ? \implode('/', [$filename[0], $filename[1], $filename[2]])
            : '';
    }
}
