<table id="lbwps-tab-2" class="form-table" style="display:none;">
    <tr class="lbwps-ver4">
        <th scope="row">
            <?php echo __('Skin', self::SLUG); ?>
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
                    __('Original', self::SLUG),
                    __('Original with solid background', self::SLUG),
                    __('New share symbol', self::SLUG),
                    __('New share symbol with solid background', self::SLUG),
                ],
                '<br>'
            );
            ?>
        </td>
    </tr>
    <tr>
        <th scope="row">
            <?php echo __('Spacing between pictures', self::SLUG); ?></th>
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
            <p class="description"><?php echo __('Space between pictures relative to screenwidth.', self::SLUG); ?></p>
        </td>
    </tr>
</table>
