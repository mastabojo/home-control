<?php
session_start();

// check if valid user
if(!isset($_SESSION['user']['userlevel']) || $_SESSION['user']['userlevel'] < 1 || !isset($_SESSION['user']['loggedin'])) {
    session_destroy();
    header('location: /index.php');
    exit();
}

define('NL', "\n");

$BASEPATH = dirname(__DIR__) . DIRECTORY_SEPARATOR;

include $BASEPATH . 'env.php';
include $BASEPATH . "public/api/class.Lang.php";

$l = new Lang($LANGUAGE);

$theme = 'dark';
// Icons or text for tab titles
$tabTitleIcons = false;

$tabs = [
    'home',
    'shutters',
    'lights',
    'calendar',
    'weather',
    'heating',
    // 'cameras',
    'system'
];

$tabTitles = [
    'home' => strtolower($l->Get("tab_names", "home")),
    'shutters' => strtolower($l->Get("tab_names", "shutters")),
    'lights' => strtolower($l->Get("tab_names", "lights")),
    'calendar' => strtolower($l->Get("tab_names", "calendar")),
    'weather' => strtolower($l->Get("tab_names", "weather")),
    'heating' => strtolower($l->Get("tab_names", "heating")),
    // 'cameras' => strtolower($l->Get("tab_names", "cameras")),
    'system' => strtolower($l->Get("tab_names", "system"))
];

 ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Home control</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" media="screen" href="css/bootstrap.min.css">
    <!-- link href="//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css" type="text/css" rel="stylesheet" -->
    <link rel="stylesheet" type="text/css" media="screen" href="css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" media="screen" href="css/custom-<?php echo $theme;?>.css">
    <link rel="stylesheet" type="text/css" media="screen" href="css/hccalendar.css">
</head>
<body>

<div class="container-fluid">

<!-- Navigation (Tabs) -->
<ul class="nav nav-tabs justify-content-center border-0 mt-2" id="hcTabs" role="tablist">
<?php
foreach($tabs as $key => $tab) {
    $active = $key == 0 ? ' active show' : '';
    echo '<li class="nav-item ">';
    echo "<a class=\"nav-link{$active} border-0\" id=\"{$tab}-tab\" data-toggle=\"tab\" ";
    echo "href=\"#{$tab}\" role=\"tab\" aria-controls=\"{$tab}\" aria-selected=\"true\">";

    if($tabTitleIcons) {
        echo "<img src=\"/img/tab-icons/{$theme}/{$tab}-off.svg\" style=\"width: 16px;\">";
    } else {
        echo isset($tabTitles[$tab]) ? $tabTitles[$tab] : $tab;
    }
    echo "</a></li>" . NL;
}
?>
</ul>

<div class="row" style="height: 380x;">
  <div id="main-pane" class="col-sm">
      
<!-- Tab content -->
<div class="tab-content" id="hcTabContent">
<?php
foreach($tabs as $key => $tab) {
    $active = $key == 0 ? ' active show' : '';
    echo "<div class=\"tab-pane fade{$active}\" id=\"{$tab}\" role=\"tabpanel\" aria-labelledby=\"{$tab}-tab\">";
    include "../modules/{$tab}.php";
    echo "</div>" . NL;
}
?>
</div><!-- div#hcTabContent.tab-content -->
</div><!--div#main-pane -->
</div><!--div.row -->

<div class="row" style="height: 24px;">
<div id="status-pane" class="col-sm mt-1">

<div class="row no-gutters align-items-center">

<div class="col">
<span id="span-time" class="time-display"></span> <span id="span-date" class="date-display"></span>
</div><!-- .col -->


<div class="col text-right">
<img id="moon-phase-icon" src="/img/lunar-phase-icons/dark/moon-none.svg">&nbsp;&nbsp;&nbsp;
<img src="/img/lunar-phase-icons/dark/moon-4.svg" id="img-moon-phase-icon-1">&nbsp;<span id="span-moon-phase-info-1"></span>&nbsp;
<img src="/img/lunar-phase-icons/dark/moon-0.svg" id="img-moon-phase-icon-2">&nbsp;<span id="span-moon-phase-info-2"></span>
</div><!-- .col -->




<div class="col text-right">
<span class="cpu-data-display">CPU</span>: <span id="span-cpu-data" class="cpu-data-display">0&deg;</span>
</div><!-- .col -->

</div><!-- .row -->



  </div><!--div#status-pane -->
</div><!--div.row -->

</div><!-- div.container -->

<script src="js/jquery.min.js"></script>
<script src="js/popper.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="js/moment.min.js"></script>
<!-- script src="js/chart.js/Chart.min.js"></script -->
<script src="js/apexcharts/apexchart.min.js"></script>
<script src="js/main.js"></script>
<script>mainLoop();</script>
<!-- script>getHeatPumpchart();</script -->
</body>
</html>