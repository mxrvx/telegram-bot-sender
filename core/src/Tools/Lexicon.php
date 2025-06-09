<?php


declare(strict_types=1);

namespace MXRVX\Telegram\Bot\Sender\Tools;

use MXRVX\Telegram\Bot\Sender\App;

class Lexicon
{
    /**
     * @param array<array-key, array<array-key,string>|string> $arr
     * @return array<array-key, string>
     */
    public static function flatten(array $arr, string $prefix = '', string $delimiter = '.'): array
    {
        $out = [];
        foreach ($arr as $k => $v) {
            $key = empty($prefix) ? $k : $prefix . $delimiter . $k;
            if (\is_array($v)) {
                $out += self::flatten($v, (string) $key, $delimiter);
            } elseif (\is_string($v)) {
                $out[$key] = $v;
            }
        }

        return $out;
    }

    public static function get(\modX $modx, string $locale = 'en', array|string $prefix = []): array
    {
        $entries = [];

        /** @psalm-suppress DeprecatedMethod */
        $lexicon = $modx->lexicon instanceof \modLexicon ? $modx->lexicon : $modx->getService('lexicon', 'modLexicon');

        if (isset($lexicon) && ($lexicon instanceof \modLexicon)) {
            $namespace = App::NAMESPACE;
            $lexicon->load($locale . ':' . $namespace . ':default');


            if (!empty($prefix)) {
                if (\is_string($prefix)) {
                    $prefixes = [$prefix];
                } else {
                    $prefixes = \array_map(static function ($key) {
                        return (string) $key;
                    }, $prefix);
                }

                foreach ($prefixes as $prefix) {
                    $entries += $modx->lexicon->fetch($namespace . '.' . $prefix);
                }

            } else {
                $entries = $modx->lexicon->fetch($namespace);
            }


            $keys = \array_map(static function ($key) use ($namespace) {
                return (string) \str_replace($namespace . '.', '', (string) $key);
            }, \array_keys($entries));

            $entries = \array_combine($keys, \array_values($entries));
        }

        return $entries;
    }
}
