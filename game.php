<?php
$archivo = "estado.txt";
$ip = $_SERVER['REMOTE_ADDR'];

if (!file_exists($archivo)) file_put_contents($archivo, '{}');

$estado = json_decode(file_get_contents($archivo), true);

// Asignar color si no está
if (!isset($estado[$ip])) {
    $color_usados = array_column($estado, 'color');
    $color = in_array('blue', $color_usados) ? 'red' : 'blue';
    $estado[$ip] = ['color' => $color, 'health' => 3];
    file_put_contents($archivo, json_encode($estado));
}

// Atacar (si viene por POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($estado as $ip_jugador => &$jugador) {
        if ($ip_jugador !== $ip) {
            $jugador['health']--;
            if ($jugador['health'] <= 0) {
                $jugador['health'] = 0;
            }
        }
    }
    file_put_contents($archivo, json_encode($estado));
}

// Devolver estado actualizado
echo json_encode([
    'yo' => $estado[$ip],
    'enemigo' => array_values(array_filter($estado, fn($v) => $v !== $estado[$ip]))[0] ?? null
]);
