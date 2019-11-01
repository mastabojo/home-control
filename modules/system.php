<?php
$theme = 'dark';
$pathToSystemIcons = "/img/system-icons/{$theme}/";
?>

<div class="system-tab">

<div class="row  align-items-center justify-content-center" style="height: 200px;">

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

<div class="row" style="height: 160px;">

<div class="col">
<table class="table table-sm">
<thead>
<tr><th colspan="2" class="text-left">Status</th></tr>
</thead>
<tbody>
<tr class="text-left"><td width="15%"><?php echo $l->Get("system_ipaddr_label");?></td><td><span id="ip-address"></span></td></tr>
<tr class="text-left"><td><?php echo $l->Get("system_connection_status_label");?></td><td><span id="connection-status"></span><?php echo " (ping to $CONNECTION_CHECK_IP_ADDRESS)";?></td></tr>
<tr class="text-left"><td><?php echo $l->Get("system_uptime_label");?></td><td>
<span id="uptime-years">0</span><?php echo $l->Get("time_unit_short_abbr", 0);?>&nbsp;
<span id="uptime-months">0</span><?php echo $l->Get("time_unit_short_abbr", 1);?>&nbsp;
<span id="uptime-days">0</span><?php echo $l->Get("time_unit_short_abbr", 2);?>&nbsp;
<span id="uptime-hours">0</span><?php echo $l->Get("time_unit_short_abbr", 3);?>&nbsp;
<span id="uptime-minutes">0</span><?php echo $l->Get("time_unit_short_abbr", 4);?>
</td></tr>
<tr><td></td><td></td></tr>
</tbody>
</table>
</div><!-- .col -->

</div><!-- .row -->

</div><!-- .system-tab -->