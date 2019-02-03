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

$theme = 'dark';

$tabs = [
    'home',
     'blinds',
    'lights',
    'weather',
    'heatpump',
    'cameras',
    'settings'
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
    <link rel="stylesheet" type="text/css" media="screen" href="css/custom-<?php echo $theme;?>.css">
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
    echo "href=\"#{$tab}\" role=\"tab\" aria-controls=\"{$tab}\" aria-selected=\"true\">{$tab}</a></li>" . NL;
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

<div class="row" style="height: 30px;">
  <div id="status-pane" class="col-sm mt-1">
    <span id="span-time" class="time-display"></span> <span id="span-date" class="date-display"></span>
    
  </div><!--div#status-pane -->
</div><!--div.row -->

</div><!-- div.container -->

<script src="js/jquery.min.js"></script>
<script src="js/popper.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="js/moment.min.js"></script>
<script src="js/Chart.min.js"></script>
<script src="js/main.js"></script>
<script>mainLoop();</script>
<script>getHeatPumpchart();</script>
</body>
</html>