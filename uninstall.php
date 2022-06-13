<?php
namespace LightboxPhotoSwipe;

use LightboxPhotoSwipe\LightboxPhotoSwipe;

require(__DIR__ . '/vendor/autoload.php');

if(!defined('WP_UNINSTALL_PLUGIN')) exit();

$lightbox_photoswipe = new LightboxPhotoSwipe(__FILE__);
$lightbox_photoswipe->uninstallPluginData();
