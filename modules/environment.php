<?php
$valueCSS = 'font-size: 80px; background-color: rgba(240, 240, 240, 0.1); padding: 5px;';
?>
<div class="environment-tab">


<div class="row  align-items-center justify-content-center" style="height: 340px;">

<div class="col text-center">
<span id="environment-temeprature-01" style="<?php echo $valueCSS;?>;"></span><br>
<?php echo $l->Get("environment-temperature-label");?>
</div><!-- .col -->

<div class="col text-center">
<span id="environment-humidity-01" style="<?php echo $valueCSS;?>;"></span><br>
<?php echo $l->Get("environment-humidity-label");?>
</div><!-- .col -->

<div class="col text-center">


</div><!-- .col -->

<div class="col text-center">


</div><!-- .col -->

</div><!-- .row -->


<div class="row  align-items-center justify-content-right" style="height: 20px;">

<div class="col text-center">Prostor 1
</div><!-- .col -->

<div class="col text-center">Prostor 2
</div><!-- .col -->

</div><!-- .row -->


<div class="row  align-items-center justify-content-right" style="height: 20px;">

<div class="col text-center">
</div><!-- .col -->

<div class="col text-center">
</div><!-- .col -->

<div class="col text-center">
</div><!-- .col -->

<div class="col text-right">
<span class="updated-display"><i class="fa fa-refresh " ></i></span>&nbsp;<span id="environment-temp-and-humidity-last-updated"></span>
</div><!-- .col -->

</div><!-- .row -->

</div><!-- .environment-tab -->

