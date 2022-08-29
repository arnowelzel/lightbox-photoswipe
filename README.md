# Lightbox with PhotoSwipe

This plugin integrates an extended version of PhotoSwipe 4 or the official release of PhotoSwipe 5 to WordPress.

More about PhotoSwipe see here: [http://photoswipe.com](http://photoswipe.com)

For more information about how to install Lightbox with PhotoSwipe see the [WordPress plugin repository](https://wordpress.org/plugins/lightbox-photoswipe/).

## Plugins for PhotoSwipe

If you like to use the plugins for fullscreen mode and automatic hiding of the UI in desktop mode, you find these also on Github:

[Auto hide UI for PhotoSwipe](https://github.com/arnowelzel/photoswipe-auto-hide-ui)

[Fullscreen for PhotoSwipe](https://github.com/arnowelzel/photoswipe-fullscreen)

In addition a slightly modified version of the "dynamic caption" plugin by is also included:

[Dynamic caption plugin for PhotoSwipe v5](https://github.com/dimsemenov/photoswipe-dynamic-caption-plugin)

## Building minified versions of scripts and styles

If you want to change any of the scripts or styles you need to update the minified versions of them.

The plugin comes with a minifier script using PHP. To run that script use the following command:

```
php build.php
```

You should then see an output like this:

```
Building frontend script (PhotoSwipe 4)
Building frontend scripts (PhotoSwipe 5)
Building style for PhotoSwipe 4 skin classic
Building style for PhotoSwipe 4 skin classic-solid
Building style for PhotoSwipe 4 skin default
Building style for PhotoSwipe 4 skin default-solid
```
