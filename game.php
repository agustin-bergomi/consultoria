<?php
$archivo = "estado.txt";
$ip = $_SERVER['REMOTE_ADDR'];
if (!file_exists($archivo)) file_put_contents($archivo, '{}');
$estado = json_decode(file_get_contents($archivo), true);

// Inicializar jugador si no existe
if (!isset($estado[$ip])) {
    $color_usados = array_column($estado, 'color');
    $color = in_array('blue', $color_usados) ? 'red' : 'blue';
    $vida_inicial = rand(50, 100); // HP entre 50 y 100 para que no mueran de un golpe
    $estado[$ip] = ['color' => $color, 'health' => $vida_inicial];
    file_put_contents($archivo, json_encode($estado));
}

// Atacar (POST)
$mensaje = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $daño = rand(5, 25); // Daño aleatorio
    foreach ($estado as $ip_jugador => &$jugador) {
        if ($ip_jugador !== $ip) {
            $jugador['health'] -= $daño;
            if ($jugador['health'] < 0) $jugador['health'] = 0;
            $atacante = $estado[$ip]['color'];
            $defensor = $jugador['color'];
            $mensaje = "$atacante atacó a $defensor por $daño puntos de daño.";
        }
    }
    file_put_contents($archivo, json_encode($estado));
}

$yo = $estado[$ip];
$enemigo = array_values(array_filter($estado, fn($v) => $v !== $yo))[0] ?? null;

echo json_encode([
    'yo' => $yo,
    'enemigo' => $enemigo,
    'mensaje' => $mensaje
]);
