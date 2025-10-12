# LoadingTime
Just a simple PHP  timer markup
<?php
require_once 'LoadingTime.php';

// El framework ya tenía un inicio
$initTime = $_SERVER['REQUEST_TIME_FLOAT'];
$initMemory = memory_get_usage();

LoadingTime::start($initTime, $initMemory);

// El primer mark es justo después de cargar este script
LoadingTime::mark('loadingtime.php');

usleep(300000);
LoadingTime::mark('modules.php');

usleep(500000);
LoadingTime::mark('router.php');

usleep(200000);
LoadingTime::mark('controller.php');

echo LoadingTime::report();
?>
