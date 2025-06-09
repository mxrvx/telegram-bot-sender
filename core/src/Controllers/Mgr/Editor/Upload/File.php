<?php

declare(strict_types=1);

namespace MXRVX\Telegram\Bot\Sender\Controllers\Mgr\Editor\Upload;

use MXRVX\Telegram\Bot\Sender\Contracts\FileFactory as ContractFile;
use MXRVX\Telegram\Bot\Sender\Controllers\Controller;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;
use Slim\Psr7\UploadedFile;

class File extends Controller
{
    public function post(): ResponseInterface
    {
        /** @var null|UploadedFile $uploadedFile */
        $uploadedFile = null;
        if ($this->request instanceof ServerRequestInterface) {
            /** @var UploadedFileInterface[] $uploadedFiles */
            $uploadedFiles = $this->request->getUploadedFiles();
            $uploadedFile = \current($uploadedFiles);
        }

        if ($uploadedFile instanceof UploadedFile) {
            $file = $this->createFile($uploadedFile);
            $validation = $this->validateFile($file);
            if ($validation !== null) {
                return $this->failure($validation);
            }

            $file = $this->saveFile($file);
            if (!$file) {
                return $this->failure('Failed to save file');
            }

            return $this->success([
                'file' => [
                    'url' => $file->getDestinationUrl(),
                ],
            ]);
        }


        return $this->failure('Could not load file');
    }

    protected function createFile(UploadedFile $file): ContractFile
    {
        return new ContractFile($file);
    }

    protected function validateFile(ContractFile $file): ?string
    {
        // Пример проверки размера (максимум 5 Мб)
        /*$maxSize = 5 * 1024 * 1024;
        if ($file->getSize() > $maxSize) {
            return 'File size exceeds the maximum allowed size of 5MB.';
        }

        // Пример проверки MIME-типа
        $allowedTypes = ['image/jpeg', 'image/png', 'video/mp4'];
        if (!in_array($file->getMimeType(), $allowedTypes, true)) {
            return 'Invalid file type.';
        }*/

        return null;
    }

    protected function saveFile(ContractFile $file): ?ContractFile
    {
        return $file->save();
    }
}
