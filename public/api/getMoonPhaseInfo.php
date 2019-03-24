<?php
include(dirname(__DIR__) . '/api/class.MoonPhase.php');
$secondsInDay = 60 * 60 * 24;
$mp = new MoonPhase('');
echo json_encode([
    'phaseID' => $mp->getPhaseID(),
    'daysUntilNextFullMoon' => round($mp->getDaysUntilNextFullMoon()),
    'daysUntilNextNewMoon'  => round($mp->getDaysUntilNextNewMoon()),
    'nextFullMoonDateTS'    => round(time() + ($mp->getDaysUntilNextFullMoon() * $secondsInDay)),
    'nextNewMoonDateTS'     => round(time() + ($mp->getDaysUntilNextNewMoon() * $secondsInDay))
], JSON_FORCE_OBJECT);
