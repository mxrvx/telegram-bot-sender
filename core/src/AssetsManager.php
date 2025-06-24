<?php

declare(strict_types=1);

namespace MXRVX\Telegram\Bot\Sender;

/**
 * @psalm-type ManifestItemStructure = array{
 * file: string,
 * name: string,
 * src: string,
 * isEntry: bool,
 *     css: array<string>,
 * }
 *
 * @psalm-type ManifestStructure = array<string, ManifestItemStructure>
 */
class AssetsManager
{
    public static function registerAssets(\modX|\modExtraManagerController $instance, bool $noCss = false): void
    {
        $context = $instance instanceof \modX ? 'web' : 'mgr';
        $assets = self::getAssetsFromManifest($context);

        if ($assets) {
            //@NOTE: Production mode
            $jsMethod = $context === 'mgr' ? 'addHtml' : 'regClientHTMLBlock';
            $cssMethod = $context === 'mgr' ? 'addCss' : 'regClientCss';
            foreach ($assets as $file) {
                if (\str_ends_with($file, '.js')) {
                    $instance->$jsMethod('<script type="module" src="' . $file . '"></script>');
                } elseif (!$noCss) {
                    $instance->$cssMethod($file);
                }
            }
        } else {
            //@NOTE: Development mode
            $port = \getenv('NODE_DEV_PORT') ?: '9090';
            $connection = @\fsockopen('node', (int) $port);
            if (@\is_resource($connection)) {
                $server = \explode(':', MODX_HTTP_HOST);
                $baseUrl = MODX_ASSETS_URL . 'components/' . App::NAMESPACE . '/';
                $vite = MODX_URL_SCHEME . $server[0] . ':' . $port . $baseUrl;
                if ($instance instanceof \modX) {
                    $instance->regClientHTMLBlock('<script type="module" src="' . $vite . '@vite/client"></script>');
                    $instance->regClientHTMLBlock('<script type="module" src="' . $vite . 'src/web.ts"></script>');
                } else {
                    $instance->addHtml('<script type="module" src="' . $vite . '@vite/client"></script>');
                    $instance->addHtml('<script type="module" src="' . $vite . 'src/mgr.ts"></script>');
                }
            }
        }
    }

    /**
     * @return array<string>
     */
    protected static function getAssetsFromManifest(string $context): array
    {
        $baseUrl = MODX_ASSETS_URL . 'components/' . App::NAMESPACE . '/src/' . $context . '/';
        $manifest = MODX_ASSETS_PATH . 'components/' . App::NAMESPACE . '/src/' . $context . '/manifest.json';

        $assets = [];
        if (\file_exists($manifest) && \is_string($content = @\file_get_contents($manifest))) {

            /** @var ManifestStructure $files */
            $files = \json_decode($content, true);
            if (\json_last_error() !== JSON_ERROR_NONE) {
                $files = [];
            }

            foreach ($files as $name => $file) {
                if (!empty($file['css'])) {
                    foreach ($file['css'] as $css) {
                        $assets[] = $baseUrl . $css;
                    }
                }

                if (\str_contains($name, '.ts')) {
                    $assets[] = $baseUrl . $file['file'];
                }

            }
        }

        return $assets;
    }
}
