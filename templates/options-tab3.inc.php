<table id="lbwps-tab-3" class="form-table" style="display:none;">
    <tr>
        <th scope="row">
            <?php echo __('General', self::SLUG); ?>
        </th>
        <td>
            <label><?php $this->uiControlCheckbox('show_caption'); ?> <?php echo __('Show caption if available', self::SLUG); ?></label><br>
            <label><?php $this->uiControlCheckbox('usepostdata'); ?> <?php echo __('Get the image captions from the database (this may cause delays on slower servers)', self::SLUG); ?></label>
        </td>
    </tr>
    <tr>
        <th scope="row">
            <?php echo __('Used elements', self::SLUG); ?>
        </th>
        <td>
            <label><?php $this->uiControlCheckbox('usetitle'); ?> <?php echo __('Title', self::SLUG); ?></label><br>
            <label><?php $this->uiControlCheckbox('usecaption'); ?> <?php echo __('Caption', self::SLUG); ?></label><br>
            <label><?php $this->uiControlCheckbox('usedescription'); ?> <?php echo __('Description', self::SLUG); ?></label><br>
            <label><?php $this->uiControlCheckbox('use_alt'); ?> <?php echo __('Alternative text', self::SLUG); ?></label><br>
            <label><?php $this->uiControlCheckbox('showexif'); ?> <?php echo __('EXIF data if available', self::SLUG); ?><?php if (!function_exists('exif_read_data')) { ?>(<?php echo __('<a href="https://www.php.net/manual/en/book.exif.php" target="_blank">the PHP EXIF extension</a> is missing on this server!', self::SLUG); ?>)<?php } ?></label><br>
            <label><?php $this->uiControlCheckbox('showexif_date'); ?> <?php echo __('Show date in EXIF data if available', self::SLUG); ?></label>
        </td>
    </tr>
    <tr class="lbwps-ver5">
        <th scope="row">
            <?php echo __('Type of caption', self::SLUG); ?>
        </th>
        <td>
            <label><?php $this->uiControlRadio('caption_type', ['auto', 'aside', 'below', 'overlay'], [__('dynamic, automatic', self::SLUG), __('dynamic, aside', self::SLUG), __('dynamic, below', self::SLUG), __('overlay', self::SLUG)], '<br>'); ?></label>
        </td>
    </tr>
</table>
