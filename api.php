<?php
header('Content-Type: application/json');
$archivo = 'db_pnl.txt'; // Aquí se guardan los estados

// Crear el archivo si no existe y darle permisos
if (!file_exists($archivo)) {
    file_put_contents($archivo, json_encode([]));
    chmod($archivo, 0777);
}

$metodo = $_SERVER['REQUEST_METHOD'];

// CONSULTAR ESTADO (GET)
if ($metodo === 'GET') {
    $data = json_decode(file_get_contents($archivo), true);
    if (isset($_GET['accion']) && $_GET['accion'] === 'listar') {
        echo json_encode($data);
    } elseif (isset($_GET['id'])) {
        $id = $_GET['id'];
        echo json_encode(['estado' => $data[$id] ?? 'visitando']);
    }
    exit;
}

// ACTUALIZAR ESTADO (POST)
if ($metodo === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (isset($input['id_usuario'])) {
        $id = $input['id_usuario'];
        $estado = $input['estado'] ?? 'visitando';
        $data = json_decode(file_get_contents($archivo), true);
        $data[$id] = $estado;
        file_put_contents($archivo, json_encode($data));
        echo json_encode(['res' => 'ok']);
    }
    exit;
}
?>