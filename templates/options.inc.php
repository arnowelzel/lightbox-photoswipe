<style>
.lbwps_text {
    font-size:14px;
}
.lbwps_text:first-child {
    padding-top:15px;
}
</style>
<div class="wrap"><h1><?php __('Lightbox with PhotoSwipe', self::SLUG); ?></h1>
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
