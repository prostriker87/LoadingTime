# LoadingTime
Just a simple PHP  timer markup

    <?php 
        $time = microtime(TRUE);
        $memory = memory_get_usage();
        include '_scripts/LoadingTime.php';
        LoadingTime::start($time, $memory);
        LoadingTime::mark('LoadingTime.php');
        echo LoadingTime::report();
    ?>
