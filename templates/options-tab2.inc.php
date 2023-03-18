<table id="lbwps-tab-2" class="form-table" style="display:none;">
    <tr class="lbwps-ver4">
        <th scope="row">
            <?php echo __('Skin', 'lightbox-photoswipe'); ?>
        </th>
        <td>
            <?php $this->uiControlRadio(
                'skin',
                [
                    '1',
                    '2',
                    '3',
                    '4'
                ],
                [
                    __('Original', 'lightbox-photoswipe'),
                    __('Original with solid background', 'lightbox-photoswipe'),
                    __('New share symbol', 'lightbox-photoswipe'),
                    __('New share symbol with solid background', 'lightbox-photoswipe'),
                ],
                '<br>'
            );
            ?>
        </td>
    </tr>
    <tr>
        <th scope="row">
            <?php echo __('Spacing between pictures', 'lightbox-photoswipe'); ?>
        </th>
        <td>
            <select id="lightbox_photoswipe_spacing" name="lightbox_photoswipe_spacing">
                <?php
                for ($spacing = 0; $spacing < 13; $spacing++) {
                    echo '<option value="'.$spacing.'"';
                    if ((int)$this->optionsManager->getOption('spacing') === $spacing) echo ' selected="selected"';
                    echo '>'.$spacing.'%';
                    if ($spacing === 12) echo ' ('.__('Default', 'lightbox-photoswipe').')';
                    echo '</option>';
                }
                ?>
            </select>
            <p class="description"><?php echo __('Space between pictures relative to screenwidth.', 'lightbox-photoswipe'); ?></p>
        </td>
    </tr>
    <tr class="lbwps-ver5">
        <th scope="row">
            <?php echo __('Background opacity', 'lightbox-photoswipe'); ?>
        </th>
        <td>
            <select id="lightbox_photoswipe_bg_opacity" name="lightbox_photoswipe_bg_opacity">
                <?php
                for ($bg_opacity = 0; $bg_opacity <= 100; $bg_opacity += 10) {
                    echo '<option value="'.$bg_opacity.'"';
                    if ((int)$this->optionsManager->getOption('bg_opacity') === $bg_opacity) echo ' selected="selected"';
                    echo '>'.$bg_opacity.'%';
                    if ($bg_opacity === 100) echo ' ('.__('Default', 'lightbox-photoswipe').')';
                    echo '</option>';
                }
                ?>
            </select>
            <p class="description"><?php echo __('Opacity of the background for the lightbox (values below 100% may not work well with some caption styles).', 'lightbox-photoswipe'); ?></p>
        </td>
    </tr>
    <tr class="lbwps-ver5">
        <th scope="row">
            <?php echo __('Image padding', 'lightbox-photoswipe'); ?>
        </th>
        <td>
            <label><?php echo __('left', 'lightbox-photoswipe'); ?>: <?php $this->uiControlText('padding_left', '', 'small-text') ?></label>
            <label><?php echo __('top', 'lightbox-photoswipe'); ?>: <?php $this->uiControlText('padding_top', '', 'small-text') ?></label>
            <label><?php echo __('right', 'lightbox-photoswipe'); ?>: <?php $this->uiControlText('padding_right', '', 'small-text') ?></label>
            <label><?php echo __('bottom', 'lightbox-photoswipe'); ?>: <?php $this->uiControlText('padding_bottom', '', 'small-text') ?></label>
            <p class="description"><?php echo __('Padding around the image in px', 'lightbox-photoswipe'); ?></p>
        </td>
    </tr>
</table>
