<div id="lbwps-tab-5" style="display:none;">
    <div>
        <table class="form-table">
            <tr class="lbwps-ver4">
                <th scope="row">
                    <?php echo __('General', self::SLUG); ?>
                </th>
                <td>
                    <label class="lbwps-ver4"><?php $this->uiControlCheckbox('fulldesktop'); ?> <?php echo __('Full picture size in desktop view', self::SLUG); ?></label><br>
                    <label><?php $this->uiControlCheckbox('desktop_slider'); ?> <?php echo __('Use slide animation when switching images in desktop view', self::SLUG); ?></label><br>
                    <label><?php $this->uiControlCheckbox('close_on_click'); ?> <?php echo __('Close the lightbox by clicking outside the image', self::SLUG); ?></label>
                </td>
            </tr>
            <tr class="lbwps-ver4">
                <th scope="row">
                    <?php echo __('Mouse wheel function', self::SLUG); ?>
                </th>
                <td>
                    <?php $this->uiControlRadio(
                        'wheelmode',
                        [
                            'scroll',
                            'close',
                            'zoom',
                            'switch'
                        ],
                        [
                            __('Scroll zoomed image otherwise do nothing', self::SLUG),
                            __('Scroll zoomed image or close lightbox if not zoomed', self::SLUG),
                            __('Zoom in/out', self::SLUG),
                            __('Switch to next/previous picture', self::SLUG),
                        ],
                        '<br>'); ?>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <?php echo __('Idle time for controls', self::SLUG); ?>
                </th>
                <td>
                    <select id="lightbox_photoswipe_idletime" name="lightbox_photoswipe_idletime">
                        <?php
                        echo '<option value="0"';
                        if ((int)$this->optionsManager->getOption('idletime') === 0) {
                            echo ' selected="selected"';
                        }
                        echo '>'.__('never hide automatically', self::SLUG).'</option>';
                        for ($idletime = 1000; $idletime <= 10000; $idletime+=1000) {
                            echo '<option value="'.$idletime.'"';
                            if ((int)$this->optionsManager->getOption('idletime') === $idletime) {
                                echo ' selected="selected"';
                            }
                            echo '>'.($idletime/1000).' '._n('second','seconds', $idletime/1000, self::SLUG);
                            if ($idletime == 4000) {
                                echo ' ('.__('Default', self::SLUG).')';
                            }
                            echo '</option>';
                        }
                        ?>
                    </select>
                    <p class="description"><?php echo __('Time until the on screen controls will disappear automatically in desktop view. Note: Keeping controls visible is only supported with PhotoSwipe 5.', self::SLUG); ?></p>
                </td>
            </tr>
        </table>
    </div>
</div>