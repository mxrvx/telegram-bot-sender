<?php

declare(strict_types=1);

namespace MXRVX\Telegram\Bot\Sender\Services;

use Longman\TelegramBot\Request;
use MXRVX\Telegram\Bot\Sender\App;
use MXRVX\Telegram\Bot\Sender\Models\Post;
use MXRVX\Telegram\Bot\Sender\Models\PostUser;
use MXRVX\Telegram\Bot\Models\User;
use MXRVX\Telegram\Bot\Sender\Tools\Blocks;

/**
 * @psalm-import-type DataParagraphStructure from Blocks
 * @psalm-import-type DataImageStructure from Blocks
 * @psalm-import-type DataVideoStructure from Blocks
 * @psalm-import-type BlockStructure from Blocks
 * @psalm-import-type BlockDefiniteStructure from Blocks
 */
class Sender
{
    private App $app;
    private \modX $modx;
    private PostUser $postUser;
    private ?Post $post = null;
    private ?User $user = null;

    public function __construct(App $app, PostUser $postUser)
    {
        $this->app = $app;
        $this->modx = $app->modx;
        $this->postUser = $postUser;
        if (($post = $postUser->getOne('Post')) && ($post instanceof Post)) {
            $this->post = $post;
        }
        if (($user = $postUser->getOne('User')) && ($user instanceof User)) {
            $this->user = $user;
        }
    }

    public function run(): void
    {
        $blocks = [];
        if ($this->post instanceof Post) {
            /** @var array $content */
            $content = $this->post->get('content');
            if (\is_array($content)) {
                /** @var array $blocks */
                $blocks = $content['blocks'] ?? [];
            }
        }

        if (\is_array($blocks)) {
            /** @var array<BlockDefiniteStructure> $blocks */
            $blocks = Blocks::get($blocks);
        } else {
            $blocks = [];
        }

        $chatId = (int) $this->user?->get('id');

        if (empty($chatId) || empty($blocks)) {
            $this->saveResult(false);
            return;
        }

        $results = [];
        foreach ($blocks as $block) {
            $results[] = match ($block['type'] ?? '') {
                'paragraph' => $this->sendMessage($chatId, $block),
                'image' => $this->sendImage($chatId, $block),
                'video' => $this->sendVideo($chatId, $block),
            };
        }

        if (\count(\array_filter($results, static fn($v) => $v === true))) {
            $this->saveResult(true);
        } else {
            $this->saveResult(false);
        }
    }

    protected function sendMessage(int $chatId, array $block): bool
    {
        $result = false;
        if (Blocks::validateParagraphBlock($block)) {
            $response = Request::sendMessage([
                'chat_id' => $chatId,
                'text' => \trim($block['data']['text'] ?? ''),
                'parse_mode' => 'HTML',
            ]);

            $result = $response->isOk();
            if (!$result) {
                $this->modx->log(\modX::LOG_LEVEL_ERROR, \var_export($response->printError(true), true));
            }
        }

        return $result;
    }

    protected function sendImage(int $chatId, array $block): bool
    {
        $result = false;
        if (Blocks::validateImageBlock($block)) {
            $url = $block['data']['file']['url'];
            $path = \parse_url($url, PHP_URL_PATH);
            $path = MODX_BASE_PATH . \ltrim($path, '/');
            if (!\file_exists($path)) {
                return false;
            }

            $response = Request::sendPhoto([
                'chat_id' => $chatId,
                'photo' => Request::encodeFile($path),
                'caption' => $block['data']['caption'] ?? '',
            ]);

            $result = $response->isOk();
            if (!$result) {
                $this->modx->log(\modX::LOG_LEVEL_ERROR, \var_export($response->printError(true), true));
            }
        }

        return $result;
    }

    /**
     * @throws \Longman\TelegramBot\Exception\TelegramException
     */
    protected function sendVideo(int $chatId, array $block): bool
    {
        $result = false;
        if (Blocks::validateVideoBlock($block)) {
            $url = $block['data']['file']['url'];
            $path = \parse_url($url, PHP_URL_PATH);
            $path = MODX_BASE_PATH . \ltrim($path, '/');
            if (!\file_exists($path)) {
                return false;
            }

            $response = Request::sendVideo([
                'chat_id' => $chatId,
                'video' => Request::encodeFile($path),
                'caption' => $block['data']['caption'] ?? '',
            ]);

            $result = $response->isOk();
            if (!$result) {
                $this->modx->log(\modX::LOG_LEVEL_ERROR, \var_export($response->printError(true), true));
            }
        }

        return $result;
    }

    protected function saveResult(bool $success = true): void
    {
        $this->postUser->set('is_send', true);
        $this->postUser->set('is_success', $success);
        $this->postUser->save();
    }
}
