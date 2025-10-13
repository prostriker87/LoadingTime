<?php 
    $time = microtime(TRUE);
    $memory = memory_get_usage();
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
        public static function mark($name, $action = null, $data = null) {
            if (!self::$started) self::start();

            $nowTime = microtime(true);
            $nowMemory = memory_get_usage();

            // --- Contador independiente ---
            if ($action === 'start') {
                self::$counters[$name] = [
                    'startTime' => $data[0] ?? $nowTime,
                    'startMemory' => $data[1] ?? $nowMemory,
                    'endTime' => null,
                    'endMemory' => null
                ];
            } elseif ($action === 'stop' && isset(self::$counters[$name])) {
                self::$counters[$name]['endTime'] = $nowTime;
                self::$counters[$name]['endMemory'] = $nowMemory;
            } else {
                // --- Registro normal de módulo ---
                $elapsedTime = ($nowTime - self::$lastTime) * 1000;
                $elapsedMemory = ($nowMemory - self::$lastMemory) / 1024 / 1024;

                // Guardamos además el "start" relativo para orden correcto
                $startOffset = $nowTime - self::$startTime;

                self::$modules[$name] = [
                    'time' => $elapsedTime,
                    'memory' => $elapsedMemory,
                    'start' => $startOffset
                ];

                self::$lastTime = $nowTime;
                self::$lastMemory = $nowMemory;
            }
        }

        // Genera el reporte visual
        public static function report() {
                self::$lastTime = microtime(true);
                self::$lastMemory = memory_get_usage();;
            // 🔹 Fusionar módulos y contadores con su tiempo de inicio relativo
            $merged = [];

            foreach (self::$modules as $name  => $m) {
                $merged[] = [
                    'type' => 'module',
                    'name' => $name,
                    'time' => $m['time'],
                    'memory' => $m['memory'],
                    'start' => $m['start'] ?? 0
                ];
            }

            foreach (self::$counters as $name => $c) {
                if ($c['endTime'] === null) continue;
                $merged[] = [
                    'type' => 'counter',
                    'name' => $name,
                    'time' => ($c['endTime'] - $c['startTime']) * 1000,
                    'memory' => ($c['endMemory'] - $c['startMemory']) / 1024 / 1024,
                    'start' => $c['startTime'] - self::$startTime
                ];
            }

            // 🔹 Ordenar por inicio ascendente, empate → categoría primero
            usort($merged, function($a, $b) {
                $diff = $a['start'] <=> $b['start'];
                if ($diff !== 0) return $diff;
                return ($a['type'] === 'module' && $b['type'] === 'counter') ? -1 :
                    (($a['type'] === 'counter' && $b['type'] === 'module') ? 1 : 0);
            });

            $endTime = microtime(true);
            $endMemory = memory_get_usage();

            $totalTime = ($endTime - self::$startTime) * 1000;
            $totalMemory = ($endMemory - self::$startMemory) / 1024 / 1024;
            $unitTotal = $totalTime >= 1000 ? 'secs' : 'ms';
            if ($totalTime >= 1000) $totalTime /= 1000;

            $elapsedTime = ($endTime - self::$lastTime) * 1000;
            $elapsedMemory = ($endMemory - self::$lastMemory) / 1024 / 1024;
            $merged[] = [
                'type' => 'counter',
                'name' => 'LoadingTime::report()',
                'time' => $elapsedTime,
                'memory' => $elapsedMemory,
                'start' => $endTime - self::$startTime
            ];

            // 🔹 Render del HTML
            $html = '<div style="font-family:monospace; font-size:13px; background:#111; color:#eee; padding:15px; border-radius:12px; width:fit-content; margin:auto;">';
            $html .= '<h3 style="margin:0 0 10px 0; color:#0ff;">⏱️ Reporte de Carga</h3>';
            $html .= '<div style="margin-bottom:10px;">
                        <strong style="color:#0f0;">Carga total:</strong> ' . round($totalTime, 2) . ' ' . $unitTotal . 
                        ' &nbsp;|&nbsp; <strong>Memoria:</strong> ' . round($totalMemory, 2) . ' MB
                    </div>';
            $html .= '<hr style="border:1px solid #333;">';
            $html .= '<div><strong style="color:#0ff;">Timeline (ordenado):</strong><br>';

            if (!empty($merged)) {
                $maxTime = max(array_column($merged, 'time'));

                foreach ($merged as $item) {
                    $color = $item['type'] === 'counter' ? '#ff0' : '#0f0';
                    $icon  = $item['type'] === 'counter' ? '⚡' : '⚙️';
                    $unit  = $item['time'] >= 1000 ? 'secs' : 'ms';
                    $time  = $item['time'] >= 1000 ? $item['time']/1000 : $item['time'];

                    $percent = $maxTime > 0 ? ($item['time'] / $maxTime) * 100 : 0;

                    $html .= sprintf(
                        '<div style="margin-left:15px; margin-bottom:5px;">
                            <span style="color:%s;">%s %s</span>
                            <div style="background:#222; border-radius:4px; overflow:hidden; width:250px; height:8px; margin:3px 0;">
                                <div style="background:%s; width:%.1f%%; height:100%%;"></div>
                            </div>
                            <span style="color:%s;">%.2f %s</span> | <span style="color:#0ff;">%.2f MB</span>
                        </div>',
                        $color, $icon, htmlspecialchars($item['name']),
                        $color, $percent,
                        $color, $time, $unit, $item['memory']
                    );
                }
            } else {
                $html .= '<div style="margin-left:15px; color:#777;">(Sin marcas registradas)</div>';
            }

            $html .= '</div>';
            $html .= '<hr style="border:1px solid #333;">';
            $html .= '<div style="font-size:11px; color:#888;">Generated by LoadingTime v4.7 (optimized)</div>';
            $html .= '</div>';

            return $html;
        }

    }
    LoadingTime::start($time, $memory);
?>
