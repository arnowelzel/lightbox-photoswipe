<table id="lbwps-tab-1" class="form-table">
    <tr>
        <th scope="row">
            <?php echo __('PhotoSwipe version to use', self::SLUG); ?>
        </th>
        <td>
            <?php $this->uiControlRadio('version', ['4', '5'], [__('version 4', self::SLUG), __('version 5', self::SLUG)], ' '); ?>
            <p class="description"><?php echo __('The available options depend on the PhotoSwipe version. UI customizations done for version 4 may not work for version 5.', self::SLUG); ?></p>
        </td>
    </tr>
    <tr>
        <th scope="row">
            <?php echo __('Excluded pages/posts', self::SLUG); ?>
        </th>
        <td>
            <?php $this->uiControlText('disabled_post_ids') ?>
            <p class="description"><?php echo __('Enter a comma separated list with the numerical IDs of the pages/posts where the lightbox should not be used. This can also be changed in the page/post itself.', self::SLUG); ?></p>
            <p><label for="lightbox_photoswipe_metabox"><?php $this->uiControlCheckbox('metabox') ?>&nbsp;<?php echo __('Show this setting as checkbox in page/post editor', self::SLUG); ?></label></p>
        </td>
    </tr>
    <tr>
        <th scope="row">
            <?php echo __('Excluded post types', self::SLUG); ?>
        </th>
        <td>
            <?php $this->uiControlText('disabled_post_types') ?>
            <p class="description"><?php echo __('Enter a comma separated list of post types where the lightbox should not be used.', self::SLUG); ?><br>
            <?php echo __('Available post types on this site', self::SLUG); ?>: <?php $this->uiGetposttypes(); ?></p>
        </td>
    </tr>
    <tr>
        <th scope="row">
            <?php echo __('Visible elements', self::SLUG); ?>
        </th>
        <td>
            <label class="lbwps-ver4"><?php $this->uiControlCheckbox('show_counter') ?>&nbsp;<?php echo __('Show picture counter', self::SLUG); ?></label><br class="lbwps-ver4">
            <label><?php $this->uiControlCheckbox('show_fullscreen') ?>&nbsp;<?php echo __('Show fullscreen button', self::SLUG); ?></label><br>
            <label class="lbwps-ver4"><?php $this->uiControlCheckbox('show_zoom') ?>&nbsp;<?php echo __('Show zoom button if available', self::SLUG); ?></label>
        </td>
    </tr>
    <tr>
        <th scope="row">
            <?php echo __('Other options', self::SLUG); ?>
        </th>
        <td>
            <label class="lbwps-ver4"><?php $this->uiControlCheckbox('history') ?>&nbsp;<?php echo __('Update browser history (going back in the browser will first close the lightbox)', self::SLUG); ?></label><br class="lbwps-ver4">
            <label><?php $this->uiControlCheckbox('loop') ?>&nbsp;<?php echo __('Allow infinite loop', self::SLUG); ?></label><br>
            <label><?php $this->uiControlCheckbox('separate_galleries') ?>&nbsp;<?php echo __('Show WordPress galleries and Gutenberg gallery blocks in separate lightboxes', self::SLUG); ?></label><br>
            <label><?php $this->uiControlCheckbox('ignore_external') ?>&nbsp;<?php echo __('Ignore links to images on other sites', self::SLUG); ?></label><br>
            <label><?php $this->uiControlCheckbox('ignore_hash') ?>&nbsp;<?php echo __('Ignore links to images which contain a hash (#)', self::SLUG); ?></label><br>
            <label><?php $this->uiControlCheckbox('hide_scrollbars') ?>&nbsp;<?php echo __('Hide scrollbars when opening the lightbox (this may not work with your theme)', self::SLUG); ?></label><br>
            <label><?php $this->uiControlCheckbox('fix_links') ?>&nbsp;<?php echo __('Fix links to images which include image sizes (e.g. "image-1024x768.jpg" instead of "image.jpg")', self::SLUG); ?></label><br>
            <label><?php $this->uiControlCheckbox('fix_scaled') ?>&nbsp;<?php echo __('Fix links to scaled images (e.g."image.jpg" instead of "image-scaled.jpg")', self::SLUG); ?></label><br>
        </td>
    </tr>
    <tr>
        <th scope="row">
            <?php echo __('SVG scaling factor', self::SLUG); ?>
        </th>
        <td>
            <select id="lightbox_photoswipe_svg_scaling" name="lightbox_photoswipe_svg_scaling">
                <?php
                for ($scaling = 100; $scaling < 550; $scaling += 50) {
                    echo '<option value="'.$scaling.'"';
                    if ((int)$this->optionsManager->getOption('svg_scaling') === $scaling) echo ' selected="selected"';
                    echo '>'.($scaling).'%';
                    if ($scaling === 200) echo ' ('.__('Default', self::SLUG).')';
                    echo '</option>';
                }
                ?>
            </select>
            <p class="description"><?php echo __('Factor by which SVG images get scaled when displayed in the lightbox.', self::SLUG); ?>
            <?php if (!function_exists('simplexml_load_file')) { ?><br><?php echo __('<a href="https://www.php.net/manual/en/ref.simplexml.php" target="_blank">The PHP SimpleXML extension</a> is missing on this server! SVG images can not be displayed!', self::SLUG); ?><?php } ?></p>
        </td>
    </tr>
    <tr>
        <th scope="row">
            <?php echo __('CDN URL prefix', self::SLUG); ?>
        </th>
        <td>
            <?php $this->uiControlText('cdn_url') ?>
            <p class="description"><?php echo __('If you use the JetPack CDN you can leave this setting empty – JetPack is already supported!', self::SLUG); ?><br>
            <?php echo __('If you use a CDN plugin which adds an URL prefix in front of the image link, you can add this prefix (including "http://" or "https://") here. You can enter multiple prefixes separated by comma. The image meta data can then be retrieved from the local file and without loading the image from the CDN. You also need this if you want to use image captions from the WordPress database but serve images using a CDN.', self::SLUG); ?></p>
        </td>
    </tr>
    <tr>
        <th scope="row">
            <?php echo __('CDN mode', self::SLUG); ?>
        </th>
        <td>
            <?php $this->uiControlRadio('cdn_mode', ['prefix', 'pull'], [__('Prefix', self::SLUG), __('Pull', self::SLUG)], ' ') ?>
            <p class="description"><?php echo __('CDNs usually use "prefix mode" which adds the CDN domain in front of the whole URL. Some CDNs like ExactDN use "pull mode" which means only the domain of the website is replaced by the CDN domain. If images don\'t show up with the CDN active try another mode.', self::SLUG); ?></p>
        </td>
    </tr>
</table>
