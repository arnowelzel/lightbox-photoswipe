<div id="lbwps-tab-4" style="display:none;">
    <div class="lbwps-ver4">
        <table class="form-table">
            <tr>
                <th scope="row">
                    <?php echo __('Visible sharing options', 'lightbox-photoswipe'); ?>
                </th>
                <td>
                    <label><?php $this->uiControlCheckbox('share_facebook'); ?> <?php echo __('Share on Facebook', 'lightbox-photoswipe'); ?></label><br>
                    <label><?php $this->uiControlCheckbox('share_twitter'); ?> <?php echo __('Tweet', 'lightbox-photoswipe'); ?></label><br>
                    <label><?php $this->uiControlCheckbox('share_direct'); ?> <?php echo __('Use URL of images instead of lightbox on Facebook and Twitter', 'lightbox-photoswipe'); ?></label><br>
                    <label><?php $this->uiControlCheckbox('share_pinterest'); ?> <?php echo __('Pin it', 'lightbox-photoswipe'); ?></label><br>
                    <label><?php $this->uiControlCheckbox('share_download'); ?> <?php echo __('Download image', 'lightbox-photoswipe'); ?></label><br>
                    <label><?php $this->uiControlCheckbox('share_copyurl'); ?> <?php echo __('Copy image URL', 'lightbox-photoswipe'); ?></label><br>
                    <label><?php $this->uiControlCheckbox('share_custom'); ?> <?php echo __('Custom link', 'lightbox-photoswipe'); ?></label>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <?php echo __('Custom link, label', 'lightbox-photoswipe'); ?>
                </th>
                <td>
                    <?php $this->uiControltext('share_custom_label', __('Your label here', 'lightbox-photoswipe')); ?>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <?php echo __('Custom link, URL', 'lightbox-photoswipe'); ?>
                </th>
                <td>
                    <?php $this->uiControltext('share_custom_link', '{{raw_image_url}}'); ?>
                    <p class="description"><?php echo __('Placeholders for the link:<br />{{raw_url}}&nbsp;&ndash;&nbsp;URL of the lightbox<br />{{url}}&nbsp;&ndash;&nbsp;encoded URL of the lightbox<br />{{raw_image_url}}&nbsp;&ndash;&nbsp;URL of the image<br />{{image_url}}&nbsp;&ndash;&nbsp;encoded URL of the image<br />{{text}}&nbsp;&ndash;&nbsp;image caption.', 'lightbox-photoswipe'); ?></p>
                </td>
            </tr>
        </table>
    </div>
    <div class="lbwps-ver5">
        <p class="lbwps_text"><?php echo __('Sharing options are not yet supported for PhotoSwipe 5.', 'lightbox-photoswipe') ?></p>
    </div>
</div>