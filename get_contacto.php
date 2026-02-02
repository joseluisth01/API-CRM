<?php
/**
 * Obtener información del contacto de un cliente
 * Archivo auxiliar para el modal
 */

header('Content-Type: application/json');

// Configuración
$CRM_URL = 'https://gestion-tictac-comunicacion.es/index.php/api';
$API_TOKEN = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJ1c2VyIjoicHJvZHVjY2lvbkB0aWN0YWMtY29tdW5pY2FjaW9uLmVzIiwibmFtZSI6IlByb2R1Y2Npb24iLCJBUElfVElNRSI6MTc2OTUwMjU3MX0.4RoKiYv6z8sBE5MdchSE8iQ7wJnXGOIAlW52Mjn5oZvdRJsAWG3l-VmVIMlj3DawwtDl21e26_twU77usBjuGw';

// Obtener ID del cliente
$clientId = isset($_GET['client_id']) ? intval($_GET['client_id']) : 0;

if ($clientId <= 0) {
    echo json_encode(array('error' => 'ID de cliente inválido'));
    exit;
}

// Llamar a la API
$url = $CRM_URL . '/contact_by_clientid/' . $clientId;

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'authtoken: ' . $API_TOKEN,
    'Content-Type: application/json'
));
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200 && $response) {
    // Devolver respuesta tal cual
    echo $response;
} else {
    // Sin contactos
    echo json_encode(array());
}
?>