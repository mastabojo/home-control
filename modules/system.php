<?php
$theme = 'dark';
$pathToSystemIcons = "/img/system-icons/{$theme}/";
?>

<div class="system-tab">

<div class="row  align-items-center justify-content-center" style="height: 320px;">

<div class="col text-center">
<img id="refresh-browser" src="<?php echo $pathToSystemIcons;?>btn-refresh-browser.svg"><br>
<?php echo $l->Get("system_refresh");?>
</div><!-- .col -->

<div class="col text-center">
<img id="exit-browser" src="<?php echo $pathToSystemIcons;?>btn-exit-browser.svg"><br>
<?php echo $l->Get("system_exit_browser");?>
</div><!-- .col -->

<div class="col text-center">
<img id="reboot" src="<?php echo $pathToSystemIcons;?>btn-reboot.svg"><br>
<?php echo $l->Get("system_reboot");?>
</div><!-- .col -->

<div class="col text-center">
<img id="shutdown" src="<?php echo $pathToSystemIcons;?>btn-shutdown.svg"><br>
<?php echo $l->Get("system_shutdown");?>
</div><!-- .col -->

</div><!-- .row -->

<div class="row" style="height: 40px;">
<div class="col text-center">
<?php echo $l->Get("system_ipaddr");?>: <span id="ip-address"></span>
</div><!-- .col -->
</div><!-- .row -->

</div><!-- .system-tab -->