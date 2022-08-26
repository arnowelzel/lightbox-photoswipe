<div id="lbwps-tab-4" style="display:none;">
    <div class="lbwps-ver4">
        <table class="form-table">
            <tr>
                <th scope="row">
                    <?php echo __('Visible sharing options', self::SLUG); ?>
                </th>
                <td>
                    <label><?php $this->uiControlCheckbox('share_facebook'); ?> <?php echo __('Share on Facebook', self::SLUG); ?></label><br>
                    <label><?php $this->uiControlCheckbox('share_twitter'); ?> <?php echo __('Tweet', self::SLUG); ?></label><br>
                    <label><?php $this->uiControlCheckbox('share_direct'); ?> <?php echo __('Use URL of images instead of lightbox on Facebook and Twitter', self::SLUG); ?></label><br>
                    <label><?php $this->uiControlCheckbox('share_pinterest'); ?> <?php echo __('Pin it', self::SLUG); ?></label><br>
                    <label><?php $this->uiControlCheckbox('share_download'); ?> <?php echo __('Download image', self::SLUG); ?></label><br>
                    <label><?php $this->uiControlCheckbox('share_copyurl'); ?> <?php echo __('Copy image URL', self::SLUG); ?></label><br>
                    <label><?php $this->uiControlCheckbox('share_custom'); ?> <?php echo __('Custom link', self::SLUG); ?></label>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <?php echo __('Custom link, label', self::SLUG); ?>
                </th>
                <td>
                    <?php $this->uiControltext('share_custom_label', __('Your label here', self::SLUG)); ?>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <?php echo __('Custom link, URL', self::SLUG); ?>
                </th>
                <td>
                    <?php $this->uiControltext('share_custom_link', '{{raw_image_url}}'); ?>
                    <p class="description"><?php echo __('Placeholders for the link:<br />{{raw_url}}&nbsp;&ndash;&nbsp;URL of the lightbox<br />{{url}}&nbsp;&ndash;&nbsp;encoded URL of the lightbox<br />{{raw_image_url}}&nbsp;&ndash;&nbsp;URL of the image<br />{{image_url}}&nbsp;&ndash;&nbsp;encoded URL of the image<br />{{text}}&nbsp;&ndash;&nbsp;image caption.', self::SLUG); ?></p>
                </td>
            </tr>
        </table>
    </div>
    <div class="lbwps-ver5">
        <p class="lbwps_text"><?php echo __('Sharing options are not yet supported for PhotoSwipe 5.', self::SLUG) ?></p>
    </div>
</div>