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

<!-- Buttons for chart display period -->
<div id="hpchart-period-select" style="position: absolute; left: 0px; top:0px; z-index:100;">
<div class="btn-group" role="group" aria-label="Period buttons">
  <button type="button" class="btn btn-outline-secondary btn-sm btn-period-fixed active" name="daily">Dnevna poraba</button>
  <button type="button" class="btn btn-outline-secondary btn-sm btn-period-fixed" name="monthly">Mesečna poraba</button>
  <button type="button" class="btn btn-outline-secondary btn-sm btn-period-fixed" name="yearly">Letna poraba</button>
  <button type="button" class="btn btn-outline-secondary btn-sm btn-period-shift shift-back">&nbsp;&lt;&nbsp;</button>
  <button type="button" class="btn btn-outline-secondary btn-sm btn-period-shift shift-fwd">&nbsp;&gt;&nbsp;</button>
  <button type="button" class="btn btn-outline-secondary btn-sm btn-period-select" data-toggle="modal" data-target="#modalHpChartPeriod">Izberi obdobje</button>
</div>
</div><!-- #hpchart-period-select -->

<?php
/*
// Use for Chart.js
<canvas id="hpchart" width="766" height="180"></canvas>
*/
?>
<div id="hpchart" style="width: 766px; heigth: 180px;"></div>

</div><!-- .col -->

</div><!-- .row -->



<!-- Modal box for date select-->
<div class="modal fade" id="modalHpChartPeriod" tabindex="-1" role="dialog" aria-labelledby="modalHpChartPeriodLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content" style="background-color: rgba(14, 68, 90, 0.9);">

<?php /*
      <div class="modal-header">
        <h5 class="modal-title" id="modalHpChartPeriodLabel">Izberi obdobje</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
*/?>
      <div class="modal-body">
    
        <div id="day-selector" class="text-center visible">
        <p>Izberi dan</p>
        <?php
        $daysInMonth = date("t");
        // $daysInMonth = 31;
        for($i = 1; $i <= $daysInMonth; $i++) {
            if($i == 1 || $i == 11 || $i == 21) {
                echo '<div class="btn-group btn-group-toggle text-center" data-toggle="buttons">';
            }
            $active = $i == date("j") ? ' active' : '';
            $checked = $i == date("j") ? ' checked' : '';
            $disabled = $i > date("j") ? ' disabled' : '';
            echo '<label class="btn btn-secondary' . $active . $disabled . '">
            <input type="radio" class="hpchart-selector-days"
            name="hpchart-selected-day-' . str_pad($i, 2, '0', STR_PAD_LEFT) . '" 
            id="day-' . str_pad($i, 2, '0', STR_PAD_LEFT) . '" 
            value="' . str_pad($i, 2, '0', STR_PAD_LEFT) . '" autocomplete="off"' . $checked .'> ';
            echo str_pad($i, 2, '0', STR_PAD_LEFT) .' </label>';
            if($i == 10 || $i == 20 || $i == $daysInMonth) {
                echo '</div><!-- .btn-group -->';
            }
        }
        ?>
        
        </div><!-- #day-selector -->


        <div class="empty-dumy-div">&nbsp;</div>


        <div id="month-selector" class="text-center visible">
        <p>Izberi mesec</p>
        <div class="btn-group btn-group-toggle text-center" data-toggle="buttons">
        <?php
        for($i = 1; $i <= 12; $i++) {
            $active = $i == date("n") ? ' active' : '';
            $checked = $i == date("n") ? ' checked' : '';
            $disabled = $i < 6 || $i > date("n") ? ' disabled' : '';
            echo '<label class="btn btn-secondary ' . $active . $disabled . '">
            <input type="radio" name="hpchart-selected-month' . str_pad($i, 2, '0', STR_PAD_LEFT) . '" 
            id="month-' . str_pad($i, 2, '0', STR_PAD_LEFT) . '" 
            value="' . str_pad($i, 2, '0', STR_PAD_LEFT) . '" autocomplete="off"' . $checked .'> ' . $i .' </label>';
        }
        ?>
        </div><!-- .btn-group -->
        </div><!-- #month-selector -->


        <div class="empty-dumy-div">&nbsp;</div>


        <div id="year-selector" class="text-center visible">
        <p>Izberi leto</p>
        <div class="btn-group btn-group-toggle" data-toggle="buttons">
        <?php 
        // Show years from year when data collection began to current year
        $startValue = 2019;
        $endValue = date("Y");
        for($i = $startValue; $i <= $endValue; $i++) {
            $active = $i == date("Y") ? ' active' : '';
            $checked = $i == date("Y") ? ' checked' : '';
            echo '<label class="btn btn-secondary btn-sm' . $active . '">
            <input type="radio" name="hpchart-selected-year-' . $i . '" id="year-' . $i . '" 
            value="' . $i . '" autocomplete="off"' . $checked . '> ' . $i .' </label>';
        }
        ?>
        </div><!-- .btn-group -->
        </div><!-- #year-selector -->

  
      </div><!-- .modal-body -->

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary btn-sm" id="hpchart-period-ok">Ok</button>
      </div>
    </div>
  </div>
</div>