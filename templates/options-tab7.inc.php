<div id="lbwps-tab-7" style="display:none">
    <p class="lbwps_text"><?php echo __('Plugin version', 'lightbox-photoswipe'); ?>: <?php echo self::VERSION ?></p>
    <p class="lbwps_text"><?php echo __('This plugin shows all linked pictures in a lightbox based on an extended version of PhotoSwipe. If the lightbox does not open, make sure that images are linked to the media and not to the attachment page. Also make sure that no other lightbox is in use (some themes or gallery plugins bring their own lightbox which needs to be disabled). ', 'lightbox-photoswipe'); ?></p>
    <p class="lbwps_text"><?php echo __('For the opening transition of PhotoSwipe the plugin will use a smaller version of the linked image if available. The registered image sizes on this website are as following', 'lightbox-photoswipe'); ?>:</p>
    <p class="lbwps_text"><?php
        foreach($this->imageSizes as $imageSize)
        {
            echo sprintf('%d×%d %s<br>', $imageSize['width'], $imageSize['height'], __('pixels', 'lightbox-photoswipe'));
        }
        ?></p>
    <p class="lbwps_text"><?php echo sprintf(
            /* translators: %d value for image size */
            __('The smallest size (%d×%d pixels) will be used to check if a smaller image is available for the transition.', 'lightbox-photoswipe'),
            $this->imageSizes[0]['width'],
            $this->imageSizes[0]['height']
        ); ?></p>
    <p class="lbwps_text"><?php echo sprintf(
            /* translators: %d value for image size */
            __('For example: if an image is 1000×400 pixels the small version of it should be %d×%d pixels since the smallest registered image size is %d×%d pixels. If a portrait image has 820×1400 pixels the small version should then be %d×%d pixels. The plugin will take rounding errors into account and will also check for images which are one pixel less or more wide or high.', 'lightbox-photoswipe'),
            $this->imageSizes[0]['width'],
            400/1000 * $this->imageSizes[0]['height'],
            $this->imageSizes[0]['width'],
            $this->imageSizes[0]['height'],
            820/1400 * $this->imageSizes[0]['width'],
            $this->imageSizes[0]['height']
        ); ?></p>
    <p class="lbwps_text"><?php echo __('Image information like size, EXIF data, name of the preview image is cached as WordPress transients. To improve performance with large image galleries you can use caching plugins like <a href="https://wordpress.org/plugins/redis-cache/" target="_blank">Redis Object Cache</a>.', 'lightbox-photoswipe'); ?></p>
    <p class="lbwps_text"><?php echo __('For documentation about hooks, styling etc. please see FAQ', 'lightbox-photoswipe'); ?>: <a href="https://wordpress.org/plugins/lightbox-photoswipe/#faq" target="_blank">https://wordpress.org/plugins/lightbox-photoswipe/#faq</a>.</p>
    <p class="lbwps_text"><b><?php echo __('If you like my WordPress plugins and want to support my work I would be very happy about a donation via PayPal.', 'lightbox-photoswipe'); ?></b></p>
    <p class="lbwps_text"><b><a href="https://paypal.me/ArnoWelzel">https://paypal.me/ArnoWelzel</a></b></p>
    <p class="lbwps_text"><b><?php echo __('Thank you :-)', 'lightbox-photoswipe'); ?></b></p>
</div>
