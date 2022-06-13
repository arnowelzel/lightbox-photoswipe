<table id="lbwps-tab-6" class="form-table" style="display:none;">
    <tr>
        <th scope="row">
            <?php echo __('General', self::SLUG); ?>
        </th>
        <td>
            <label><?php $this->uiControlCheckbox('close_on_drag'); ?> <?php echo __('Close with vertical drag in mobile view', self::SLUG); ?></label><br>
            <label><?php $this->uiControlCheckbox('pinchtoclose'); ?> <?php echo __('Enable pinch to close gesture on mobile devices', self::SLUG); ?></label><br>
            <label><?php $this->uiControlCheckbox('taptotoggle'); ?> <?php echo __('Enable tap to toggle controls on mobile devices', self::SLUG); ?></label><br>
        </td>
    </tr>
</table>
