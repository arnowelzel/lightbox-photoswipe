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
            <label><?php $this->uiControlCheckbox('showexif'); ?> <?php echo __('EXIF data if available', self::SLUG); ?>{% if not hasExif %} (<?php echo __('<a href="https://www.php.net/manual/en/book.exif.php" target="_blank">the PHP EXIF extension</a> is missing on this server!', self::SLUG); ?>){% endif %}</label><br>
            <label><?php $this->uiControlCheckbox('showexif_date'); ?> <?php echo __('Show date in EXIF data if available', self::SLUG); ?></label>
        </td>
    </tr>
</table>
