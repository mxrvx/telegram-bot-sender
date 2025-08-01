<?php

declare(strict_types=1);

namespace MXRVX\Telegram\Bot\Sender\Controllers\Mgr\Editor\Upload;

use MXRVX\Telegram\Bot\Sender\Factories\FileFactory;

class Image extends File
{
    protected function validateFile(FileFactory $file): ?string
    {
        $validation = parent::validateFile($file);
        if ($validation !== null) {
            return $validation;
        }

        $maxSize = 5 * 1024 * 1024;
        if ($file->getSize() > $maxSize) {
            return 'File size exceeds the maximum allowed size of 5MB.';
        }

        if (!$file->isImage()) {
            return 'Invalid file type.';
        }

        return null;
    }
}
