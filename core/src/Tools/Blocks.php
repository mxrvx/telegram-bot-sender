<?php


declare(strict_types=1);

namespace MXRVX\Telegram\Bot\Sender\Tools;

/**
 * @psalm-type DataParagraphStructure = array{
 *     text: string
 * }
 *
 * @psalm-type DataImageStructure = array{
 *     caption: string,
 *     file: array{url: string}
 * }
 *
 * @psalm-type DataVideoStructure = array{
 *     caption: string,
 *     file: array{url: string}
 * }
 *
 * @psalm-type BlockStructure = array{
 *     type: string,
 *     data: array
 * }
 *
 * @psalm-type BlockDefiniteStructure = array{
 *     type: string,
 *     data: DataParagraphStructure|DataImageStructure|DataVideoStructure
 * }
 *
 */
class Blocks
{
    public const PARAGRAPH = 'paragraph';
    public const IMAGE = 'image';
    public const VIDEO = 'video';

    /**
     * @return array<BlockDefiniteStructure>
     */
    public static function get(array $blocks): array
    {
        /** @var array<BlockDefiniteStructure> $blocks */
        $blocks = self::filter($blocks);
        $blocks = self::clear($blocks);
        $blocks = self::merge($blocks);
        return $blocks;
    }

    /**
     * @psalm-assert-if-true BlockStructure $block
     */
    public static function validateBlock(array $block): bool
    {
        return isset($block['type'], $block['data']) && \is_string($block['type']) && \is_array($block['data']);
    }

    /**
     * @psalm-assert-if-true array{
     *     type: string,
     *     data: DataVideoStructure
     * } $block
     */
    public static function validateVideoBlock(array $block): bool
    {
        return self::validateBlock($block) && $block['type'] === self::VIDEO && isset($block['data']['file']) && isset($block['data']['file']['url']) && \is_string($block['data']['file']['url']);
    }

    /**
     * @psalm-assert-if-true array{
     *     type: string,
     *     data: DataImageStructure
     * } $block
     */
    public static function validateImageBlock(array $block): bool
    {
        return self::validateBlock($block) && $block['type'] === self::IMAGE && isset($block['data']['file']) && isset($block['data']['file']['url']) && \is_string($block['data']['file']['url']);
    }

    /**
     * @psalm-assert-if-true array{
     *     type: string,
     *     data: DataParagraphStructure
     * } $block
     */
    public static function validateParagraphBlock(array $block): bool
    {
        return self::validateBlock($block) && $block['type'] === self::PARAGRAPH && isset($block['data']['text']) && \is_string($block['data']['text']);
    }

    /**
     * @psalm-assert-if-true BlockDefiniteStructure $block
     */
    public static function validateDefiniteBlock(array $block): bool
    {
        if (self::validateBlock($block)) {
            return match ($block['type']) {
                self::PARAGRAPH => self::validateParagraphBlock($block),
                self::IMAGE => self::validateImageBlock($block),
                self::VIDEO => self::validateVideoBlock($block),
                default => false,
            };
        }

        return false;
    }

    /**
     * @return array<BlockDefiniteStructure>
     */
    public static function filter(array $blocks = []): array
    {
        /** @var array<BlockDefiniteStructure> $blocks */
        $blocks = \array_filter($blocks, static function (array $block) {
            return self::validateDefiniteBlock($block) && \in_array($block['type'], [self::PARAGRAPH, self::IMAGE, self::VIDEO], true);
        });

        return $blocks;
    }

    /**
     * @param array<BlockDefiniteStructure> $blocks
     * @return array<BlockDefiniteStructure>
     */
    public static function clear(array $blocks): array
    {
        foreach ($blocks as &$block) {
            if (self::validateParagraphBlock($block)) {
                $block['data']['text'] = \trim($block['data']['text']);
            }
        }

        return $blocks;
    }

    /**
     * @param array<BlockDefiniteStructure> $blocks
     * @return array<BlockDefiniteStructure>
     */
    public static function merge(array $blocks = []): array
    {
        /** @var array<BlockDefiniteStructure> $result */
        $result = [];

        $paragraphBuffer = [];
        foreach ($blocks as $block) {

            if (self::validateParagraphBlock($block)) {
                $text = $block['data']['text'] ?? '';
                if (\trim($text) !== '') {
                    $paragraphBuffer[] = $text;
                }
            } else {
                if (!empty($paragraphBuffer)) {
                    $result[] = [
                        'type' => self::PARAGRAPH,
                        'data' => [
                            'text' => \implode("\n", $paragraphBuffer),
                        ],
                    ];
                    $paragraphBuffer = [];
                }
                $result[] = $block;
            }
        }

        if (!empty($paragraphBuffer)) {
            $result[] = [
                'type' => self::PARAGRAPH,
                'data' => [
                    'text' => \implode("\n", $paragraphBuffer),
                ],
            ];
        }

        return $result;
    }
}
