#!/usr/bin/php
<?php

namespace LightboxPhotoSwipe;

use MatthiasMullie\Minify;

require(__DIR__ . '/vendor/autoload.php');

/**
 * Helper class to build the frontend assets
 */
class Build
{
    /**
     * Build minified frontend assets
     */
    public function buildAssets(): void
    {
        // Frontend script:
        // Combine all frontend scripts to one minified file

        echo "Building frontend script\n";

        $minifyJs = new Minify\JS();
        $minifyJs->add('src/lib/photoswipe.js');
        $minifyJs->add('src/lib/photoswipe-ui-default.js');
        $minifyJs->add('src/js/frontend.js');
        $minifyJs->minify('assets/scripts.js');

        // Skins:
        // Combine all styles for each skine to one minifed file which includes all images as data URIs

        $sourcePhotoswipe = file_get_contents('src/lib/photoswipe.css');
        foreach (['classic', 'classic-solid', 'default', 'default-solid'] as $skin) {
            echo sprintf("Building style for skin %s\n", $skin);

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
            $minifyCss->minify(sprintf('assets/styles/%s.css', $skin));
        }
    }
}

$build = new Build();
$build->buildAssets();
