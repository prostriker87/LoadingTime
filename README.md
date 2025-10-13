# LoadingTime
<p>Just a simple PHP  timer markup</p>
<img width="309" height="779" alt="image" src="https://github.com/user-attachments/assets/583f3127-da30-4123-b98f-4a7e1632472c" />


    <?php 
        $time = microtime(TRUE);
        $memory = memory_get_usage();
        include '_scripts/LoadingTime.php';
        LoadingTime::start($time, $memory);
        LoadingTime::mark('Modules Load','start',[$time, $memory]);
        LoadingTime::mark('LoadingTime.php');
        /*
        (...)
        */
        usleep(50000); //Last module load
        LoadingTime::mark('Last module');
        LoadingTime::mark('Modules Load',stop);
    ?><!DOCTYPE html>
    <html lang="es">
        <head>
        </head>
        <?php
            usleep(5000); //Head content
            LoadingTime::mark('head'); //You need to mark to renew last_timestamp, if you only start and stop, your next mark will not have renewed your timestamp
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
