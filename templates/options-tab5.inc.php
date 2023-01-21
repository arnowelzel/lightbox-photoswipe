<div id="lbwps-tab-5" style="display:none;">
    <div>
        <table class="form-table">
            <tr class="lbwps-ver4">
                <th scope="row">
                    <?php echo __('General', 'lightbox-photoswipe'); ?>
                </th>
                <td>
                    <label class="lbwps-ver4"><?php $this->uiControlCheckbox('fulldesktop'); ?> <?php echo __('Full picture size in desktop view', 'lightbox-photoswipe'); ?></label><br>
                    <label><?php $this->uiControlCheckbox('desktop_slider'); ?> <?php echo __('Use slide animation when switching images in desktop view', 'lightbox-photoswipe'); ?></label><br>
                    <label><?php $this->uiControlCheckbox('close_on_click'); ?> <?php echo __('Close the lightbox by clicking outside the image', 'lightbox-photoswipe'); ?></label>
                </td>
            </tr>
            <tr class="lbwps-ver4">
                <th scope="row">
                    <?php echo __('Mouse wheel function', 'lightbox-photoswipe'); ?>
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
                            __('Scroll zoomed image otherwise do nothing', 'lightbox-photoswipe'),
                            __('Scroll zoomed image or close lightbox if not zoomed', 'lightbox-photoswipe'),
                            __('Zoom in/out', 'lightbox-photoswipe'),
                            __('Switch to next/previous picture', 'lightbox-photoswipe'),
                        ],
                        '<br>'); ?>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <?php echo __('Idle time for controls', 'lightbox-photoswipe'); ?>
                </th>
                <td>
                    <select id="lightbox_photoswipe_idletime" name="lightbox_photoswipe_idletime">
                        <?php
                        echo '<option value="0"';
                        if ((int)$this->optionsManager->getOption('idletime') === 0) {
                            echo ' selected="selected"';
                        }
                        echo '>'.__('never hide automatically', 'lightbox-photoswipe').'</option>';
                        for ($idletime = 1000; $idletime <= 10000; $idletime+=1000) {
                            echo '<option value="'.$idletime.'"';
                            if ((int)$this->optionsManager->getOption('idletime') === $idletime) {
                                echo ' selected="selected"';
                            }
                            echo '>'.($idletime/1000).' '._n('second','seconds', $idletime/1000, 'lightbox-photoswipe');
                            if ($idletime == 4000) {
                                echo ' ('.__('Default', 'lightbox-photoswipe').')';
                            }
                            echo '</option>';
                        }
                        ?>
                    </select>
                    <p class="description"><?php echo __('Time until the on screen controls will disappear automatically in desktop view. Note: Keeping controls visible is only supported with PhotoSwipe 5.', 'lightbox-photoswipe'); ?></p>
                </td>
            </tr>
        </table>
    </div>
</div>