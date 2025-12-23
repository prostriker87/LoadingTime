<?php 
    define('LT_ENABLED', true); //false in prod
    require 'LoadingTime.php'; //must be keeped in prod or every call removed
    LoadingTime::mark('pre-html Load','start',[$time, $memory]); // $time & $memory inherited from LoadingTime.php
    LoadingTime::mark('LoadingTime.php');
    /*
    (...)
    */
    usleep(50000); //Last pre-html module load
    LoadingTime::mark('Last module');
    LoadingTime::mark('pre-html Load',stop);
?><!DOCTYPE html>
<html lang="es">
    <head>
    </head>
    <?php
        usleep(5000); //Head content A
        LoadingTime::mark('head A'); //You need to mark to renew last_timestamp, if you only start and stop, your next mark will not have renewed your timestamp
        usleep(7500); //Head content B
        LoadingTime::skip(); // You can also skip content time from stats
        LoadingTime::mark('body'),'start';
    ?>
    <body>
        <?php 
            usleep(150000); //Body content A
            LoadingTime::mark('Body content A');
            usleep(100000); //Body content B
            LoadingTime::mark('Body content B');
            usleep(50000); //Body content C
            LoadingTime::mark('Body content C');
        ?>
    </body>
    <?php
        LoadingTime::mark('body'),'stop';
    ?>
</html>
<?php
    echo LoadingTime::report();
?>
