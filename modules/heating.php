<?php
?>
<!-- Row 1 - consumption table -->
<div class="row no-gutters align-items-end" style="height: 180px; background-color: green;">

<!-- col 1 -->
<div class="col">

<table>
<thead>
<tr>
</tr>
</thead>

<tbody>
<tr>
<td>Trenutna dnevna poraba</td><td id="heating-current-daily-consumption">0</td>
</tr>
<tr>
<td>Min dnevna poraba v mesecu</td><td>0</td>
</tr>
<tr>
<td>Max dnevna poraba v mesecu</td><td>0</td>
</tr>
<tr>
<td>Mesečna poraba</td><td>0</td>
</tr>
</tbody>

</table>

</div><!-- .col -->

</div><!-- .row -->

<!-- Row 2 - consumption chart -->
<div class="row no-gutters align-items-end">

<!-- col 1 -->
<div class="col">





</div><!-- .col -->

</div><!-- .row -->

<script>
setInterval(function() {
    $("#heating-current-daily-consumption").text(localStorage.getItem('heating-totalKwh-daily'));
}, 933);

</script>