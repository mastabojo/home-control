<?php

?>
<!-- Row 1 - consumption table -->
<div class="row no-gutters align-items-end" style="height: 180px;">

<!-- col 1 -->
<div class="col-9">

<table class="table table-sm">
<thead>
<tr class="text-center">
<th class="text-left">Poraba (KWh)</th><th>MT</th><th>VT</th><th>Skupaj</th><th>Cena (Eur)</th>
</tr>
</thead>

<tbody>
<tr id="heating-current-daily-consumption" class="text-center">
<td class="text-left">Dnevna poraba</td><td></td><td></td><td></td><td></td>
</tr>
<tr id="heating-current-monthly-consumption" class="text-center">
<td class="text-left">Mesečna poraba</td><td></td><td></td><td></td><td></td>
</tr>
<!-- Empty row -->
<tr>
<td></td><td></td><td></td><td></td><td></td>
</tr>
</tbody>

</table>

</div><!-- .col -->

<div class="col text-center">
Dnevna poraba<br>
<span id="heating-total-daily-consumption-value-big"></span><span id="heating-total-daily-consumption-unit">KWh</span>
</div><!-- .col -->

</div><!-- .row -->

<!-- Row 2 - consumption chart -->
<div class="row no-gutters align-items-end">

<!-- col 1 -->
<div class="col">

<canvas id="hpchart" width="766" height="180"></canvas>

</div><!-- .col -->

</div><!-- .row -->
