<?php 
class LoadingTime {
    private static $startTime = 0;
    private static $startMemory = 0;
    private static $lastTime = 0;
    private static $lastMemory = 0;
    private static $modules = [];
    private static $counters = [];
    private static $started = false;

    // Inicia la medición global
    public static function start($time = null, $memory = null) {
        self::$startTime = $time ?? microtime(true);
        self::$startMemory = $memory ?? memory_get_usage();
        self::$lastTime = self::$startTime;
        self::$lastMemory = self::$startMemory;
        self::$modules = [];
        self::$counters = [];
        self::$started = true;
    }

    // Marca un punto en la ejecución
    public static function mark($name, $action = null) {
        if (!self::$started) self::start();

        $nowTime = microtime(true);
        $nowMemory = memory_get_usage();


        

        // --- Contador independiente ---
        if ($action === 'start') {
            self::$counters[$name] = [
                'startTime' => $nowTime,
                'startMemory' => $nowMemory,
                'endTime' => null,
                'endMemory' => null
            ];
        } elseif ($action === 'stop' && isset(self::$counters[$name])) {
            self::$counters[$name]['endTime'] = $nowTime;
            self::$counters[$name]['endMemory'] = $nowMemory;
        }else{
        // Registro normal de marca
            $elapsedTime = ($nowTime - self::$lastTime) * 1000;
            $elapsedMemory = ($nowMemory - self::$lastMemory) / 1024 / 1024;

            self::$modules[] = [
            'name' => $name,
            'time' => $elapsedTime,
            'memory' => $elapsedMemory
            ];
            self::$lastTime = $nowTime;
            self::$lastMemory = $nowMemory;
        }
    }

    // Genera el reporte visual
    public static function report() {
        $endTime = microtime(true);
        $endMemory = memory_get_usage();

        $totalTime = ($endTime - self::$startTime) * 1000;
        $totalMemory = ($endMemory - self::$startMemory) / 1024 / 1024;
        $unitTotal = $totalTime >= 1000 ? 'secs' : 'ms';
        if ($totalTime >= 1000) $totalTime /= 1000;

        $html = '<div style="font-family:monospace; font-size:13px; background:#111; color:#eee; padding:15px; border-radius:12px; width:fit-content; margin:auto;">';
        $html .= '<h3 style="margin:0 0 10px 0; color:#0ff;">⏱️ Reporte de Carga</h3>';
        $html .= '<div style="margin-bottom:10px;">
                    <strong style="color:#0f0;">Carga total:</strong> ' . round($totalTime, 2) . ' ' . $unitTotal . 
                    ' &nbsp;|&nbsp; <strong>Memoria:</strong> ' . round($totalMemory, 2) . ' MB
                  </div>';
        $html .= '<hr style="border:1px solid #333;">';

        // Timeline normal
        $html .= '<div><strong style="color:#0ff;">Timeline:</strong><br>';
        if (!empty(self::$modules)) {
            $maxTime = max(array_column(self::$modules, 'time'));
            foreach (self::$modules as $mod) {
                $time = $mod['time'];
                $mem = $mod['memory'];
                $unit = $time >= 1000 ? 'secs' : 'ms';
                if ($time >= 1000) $time /= 1000;
                $percent = $maxTime > 0 ? ($mod['time'] / $maxTime) * 100 : 0;

                $html .= sprintf(
                    '<div style="margin-left:15px; margin-bottom:5px;">
                        <span style="color:#0ff;">⚙️ %s</span>
                        <div style="background:#222; border-radius:4px; overflow:hidden; width:250px; height:8px; margin:3px 0;">
                            <div style="background:#0f0; width:%.1f%%; height:100%%;"></div>
                        </div>
                        <span style="color:#0f0;">%.2f %s</span> | <span style="color:#0ff;">%.2f MB</span>
                    </div>',
                    htmlspecialchars($mod['name']),
                    $percent,
                    $time,
                    $unit,
                    $mem
                );
            }
        } else {
            $html .= '<div style="margin-left:15px; color:#777;">(Sin marcas registradas)</div>';
        }
        $html .= '</div>';

        // Contadores independientes (una sola línea cada uno)
        if (!empty(self::$counters)) {
            $html .= '<hr style="border:1px solid #333;">';
            $html .= '<div><strong style="color:#ff0;">Contadores Independientes:</strong><br>';
            foreach (self::$counters as $name => $counter) {
                if ($counter['endTime'] === null) continue; // ignorar si no se cerró con stop

                $deltaTime = ($counter['endTime'] - $counter['startTime']) * 1000;
                $deltaMemory = ($counter['endMemory'] - $counter['startMemory']) / 1024 / 1024;
                $unit = $deltaTime >= 1000 ? 'secs' : 'ms';
                if ($deltaTime >= 1000) $deltaTime /= 1000;

                $html .= sprintf(
                    '<div style="margin-left:15px; margin-bottom:5px;">
                        <span style="color:#ff0;">⚡ %s</span>
                        <div style="background:#222; border-radius:4px; overflow:hidden; width:200px; height:6px; margin:3px 0;">
                            <div style="background:#ff0; width:100%%; height:100%%;"></div>
                        </div>
                        <span style="color:#0f0;">%.2f %s</span> | <span style="color:#0ff;">%.2f MB</span>
                    </div>',
                    htmlspecialchars($name),
                    $deltaTime,
                    $unit,
                    $deltaMemory
                );
            }
            $html .= '</div>';
        }

        $html .= '<hr style="border:1px solid #333;">';
        $html .= '<div style="font-size:11px; color:#888;">Generated by LoadingTime v4.6</div>';
        $html .= '</div>';

        return $html;
    }
}
?>
