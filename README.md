# LoadingTime
<p>Just a simple PHP  timer markup</p>
<img width="318" height="833" alt="image" src="https://github.com/user-attachments/assets/b90e9b3e-be25-4d0a-a9e0-bea00e60fe32" />

    <?php 
        include 'LoadingTime.php';
        LoadingTime::mark('Modules Load','start',[$time, $memory]); // $time & $memory inherited from LoadingTime.php
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
