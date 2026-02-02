<?php
/**
 * DEBUG - Ver qué devuelve la API de items
 * Elimina este archivo después de usarlo
 */

require_once '../config.php';

echo "<h1>Debug API Items</h1>";

// Probar diferentes endpoints
$endpoints = [
    'items',
    'invoice_items',
    'estimate_items',
    'items/list',
];

foreach ($endpoints as $endpoint) {
    echo "<h2>Probando: {$endpoint}</h2>";
    $response = callCrmApi($endpoint);
    echo "<pre>";
    print_r($response);
    echo "</pre>";
    echo "<hr>";
}

// Probar items individuales
echo "<h2>Probando items individuales (1-10)</h2>";
for ($id = 1; $id <= 10; $id++) {
    $response = callCrmApi('items/' . $id);
    if ($response && !isset($response['error'])) {
        echo "<h3>Item {$id}:</h3>";
        echo "<pre>";
        print_r($response);
        echo "</pre>";
    }
}