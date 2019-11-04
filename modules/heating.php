<?php

?>
<!-- Row 1 - consumption table -->
<div class="row no-gutters align-items-end" style="height: 180px;">

<!-- col 1 -->
<div class="col-9">

<table class="table table-sm">
<thead>
<tr class="text-center">
<th class="text-left"><?php echo $l->Get("heating-consumption");?> (KWh)</th>
<th><?php echo $l->Get("heating-low-tariff-abbr");?></th>
<th><?php echo $l->Get("heating-high-tariff-abbr");?></th>
<th><?php echo $l->Get("common-total");?></th><th><?php echo $l->Get("common-price");?> (Eur)</th>
</tr>
</thead>

<tbody>
<tr id="heating-current-daily-consumption" class="text-center">
<td class="text-left"><?php echo $l->Get("heating-daily-consumption");?></td><td></td><td></td><td></td><td></td>
</tr>
<tr id="heating-current-monthly-consumption" class="text-center">
<td class="text-left"><?php echo $l->Get("heating-monthly-consumption");?></td><td></td><td></td><td></td><td></td>
</tr>
<!-- Empty row -->
<tr>
<td></td><td></td><td></td><td></td><td></td>
</tr>
</tbody>

</table>

</div><!-- .col -->

<div class="col text-center">
<?php echo $l->Get("heating-daily-consumption");?><br>
<span id="heating-total-daily-consumption-value-big"></span><span id="heating-total-daily-consumption-unit">KWh</span>
</div><!-- .col -->

</div><!-- .row -->

<!-- Row 2 - consumption chart -->
<div class="row no-gutters align-items-end">

<!-- col 1 -->
<div class="col">

<?php
/*
// Use for Chart.js
<canvas id="hpchart" width="766" height="180"></canvas>
*/
?>
<div id="hpchart" style="width: 766px; heigth: 180px;"></div>

</div><!-- .col -->

</div><!-- .row -->
