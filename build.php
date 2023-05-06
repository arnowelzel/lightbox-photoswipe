#!/usr/bin/php
<?php

namespace LightboxPhotoSwipe;

use MatthiasMullie\Minify;

require(__DIR__ . '/vendor/autoload.php');

if (!defined('PHP_VERSION_ID')) {
    $version = explode('.', PHP_VERSION);
    define('PHP_VERSION_ID', ($version[0] * 10000 + $version[1] * 100 + $version[2]));
}
if (PHP_VERSION_ID < 70400) {
    echo "=================================================================\n";
    echo "Warning! You are using a very old PHP version!\n";
    echo "This is no supported any may cause errors or unpredicted results!\n";
    echo "Please use at least PHP 7.4!\n";
    echo "=================================================================\n";
}

/**
 * Helper class to build the frontend assets
 */
class Build
{
    /**
     * Build minified frontend assets
     */
    public function buildAssets()
    {
        // Frontend script:
        // Combine all frontend scripts to one minified file

        echo "Building frontend script (PhotoSwipe 4)\n";

        $minifyJs = new Minify\JS();
        $minifyJs->add('src/lib/photoswipe.js');
        $minifyJs->add('src/lib/photoswipe-ui-default.js');
        $minifyJs->add('src/js/frontend.js');
        $minifyJs->minify('assets/ps4/scripts.js');

        echo "Building frontend scripts (PhotoSwipe 5)\n";

        $minifyJs = new Minify\JS();
        $minifyJs->add('assets/ps5/frontend.js');
        $minifyJs->minify('assets/ps5/frontend.min.js');

        $minifyJs = new Minify\JS();
        $minifyJs->add('assets/ps5/dynamic-caption/photoswipe-dynamic-caption-plugin.esm.js');
        $minifyJs->minify('assets/ps5/dynamic-caption/photoswipe-dynamic-caption-plugin.esm.min.js');

        $minifyJs = new Minify\JS();
        $minifyJs->add('assets/ps5/auto-hide-ui/photoswipe-auto-hide-ui.esm.js');
        $minifyJs->minify('assets/ps5/auto-hide-ui/photoswipe-auto-hide-ui.esm.min.js');

        $minifyJs = new Minify\JS();
        $minifyJs->add('assets/ps5/fullscreen/photoswipe-fullscreen.esm.js');
        $minifyJs->minify('assets/ps5/fullscreen/photoswipe-fullscreen.esm.min.js');

        // Skins:
        // Combine all styles for each skine to one minifed file which includes all images as data URIs

        $sourcePhotoswipe = file_get_contents('src/lib/photoswipe.css');
        foreach (['classic', 'classic-solid', 'default', 'default-solid'] as $skin) {
            echo sprintf("Building style for PhotoSwipe 4 skin %s\n", $skin);

            $minifyCss = new Minify\CSS();
            $source = $sourcePhotoswipe.file_get_contents(sprintf('src/lib/skins/%s/skin.css', $skin));
            $matches = [];
            if (preg_match_all('/url\\((.+)\\)/m', $source, $matches)) {
                $num = 0;
                while ($num < count($matches[1])) {
                    $fileName = $matches[1][$num];
                    $posExt = strpos($fileName, '.');
                    if ($posExt) {
                        $ext = substr($fileName, $posExt + 1);
                        switch ($ext) {
                            case 'svg':
                                $mimeType = 'image/svg+xml';
                                break;
                            default:
                                $mimeType = 'image/'.$ext;
                                break;
                        }
                        $data = sprintf(
                            'data:%s;base64,%s',
                            $mimeType,
                            base64_encode(
                                file_get_contents(sprintf('src/lib/skins/%s/%s', $skin, $fileName))
                            )
                        );
                        $source = str_replace($matches[0][$num], sprintf('url(\'%s\')', $data), $source);
                    }
                    $num++;
                }
            }
            $minifyCss->add($source);
            $minifyCss->minify(sprintf('assets/ps4/styles/%s.css', $skin));
        }

        // Styles for PhotoSwipe 5
        echo sprintf("Building style for PhotoSwipe 5\n");

        $minifyCss = new Minify\CSS();
        $minifyCss->addFile('assets/ps5/lib/photoswipe-local.css');
        $minifyCss->addFile('assets/ps5/dynamic-caption/photoswipe-dynamic-caption-plugin.css');
        $minifyCss->minify('assets/ps5/styles/main.css');
    }
}

$build = new Build();
$build->buildAssets();
