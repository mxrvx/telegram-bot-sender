<?php


declare(strict_types=1);

namespace MXRVX\Telegram\Bot\Sender\Tools;

class Caster
{
    public static function int(mixed $value): int
    {
        return match (true) {
            $value === null => 0,
            \is_bool($value) => $value ? 1 : 0,
            \is_int($value) => $value,
            \is_float($value) => (int) $value,
            \is_string($value) => \is_numeric(\trim($value)) ? (int) \trim($value) : 0,
            default => 0,
        };
    }

    public static function intPositive(mixed $value): int
    {
        $value = self::int($value);
        return \max($value, 0);
    }

    public static function bool(mixed $value): bool
    {
        $value = match (true) {
            \is_bool($value) => $value,
            \is_int($value) => \in_array($value, [0, 1], true) ? $value === 1 : null,
            \is_string($value) => match (\trim(\strtolower($value))) {
                '1', 'true' => true,
                '0', 'false' => false,
                default => null,
            },

            default => null,
        };

        return $value ?? false;
    }

    public static function string(mixed $value, int $length = 191, string $encoding = 'UTF-8'): string
    {
        $value = match (true) {
            \is_string($value) => \trim($value),
            \is_int($value), \is_float($value), \is_bool($value) => (string) $value,
            default => null,
        };

        if (\is_string($value) && $length > 0) {
            $value = \mb_substr($value, 0, $length, $encoding);
        }

        return $value ?? '';
    }

    public static function array(mixed $value): array
    {
        return match (true) {
            \is_array($value) => $value,
            $value === null => [],
            \is_object($value) => (array) $value,
            \is_string($value) && ($value[0] === '[' || $value[0] === '{') => (static function (string $str) {
                /** @var array|null $decoded */
                $decoded = \json_decode($str, true);
                return (\json_last_error() === JSON_ERROR_NONE && \is_array($decoded)) ? $decoded : [];
            })($value),
            default => [$value],
        };
    }

    public static function phone(mixed $value, int $length = 15): string
    {
        $value = self::string($value);

        $value = (string) \preg_replace('/[^0-9+]/', '', $value);

        if ($length > 0) {
            $value = \mb_substr($value, 0, $length, 'UTF-8');
        }

        return $value;
    }
}
