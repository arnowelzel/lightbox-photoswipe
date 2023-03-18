<style>
.lbwps_admin p {
    max-width:50em;
}
.lbwps_text {
    font-size:14px;
}
.lbwps_text:first-child {
    padding-top:15px;
}
</style>
<div class="wrap lbwps_admin"><h1><?php echo __('Lightbox with PhotoSwipe', 'lightbox-photoswipe'); ?></h1>
<?php
$this->uiFormStart();
include('options-navigation.inc.php');
include('options-tab1.inc.php');
include('options-tab2.inc.php');
include('options-tab3.inc.php');
include('options-tab4.inc.php');
include('options-tab5.inc.php');
include('options-tab6.inc.php');
include('options-tab7.inc.php');
$this->uiFormEnd();
?>
</div>
<?php
include('options-script.inc.php');
?>
