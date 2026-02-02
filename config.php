<?php
/**
 * Configuración Global - API Tictac Comunicación
 * Archivo central con todas las configuraciones del sistema
 */

// ============================================
// AUTENTICACIÓN
// ============================================
require_once __DIR__ . '/auth.php';
verificarAutenticacion();

// ============================================
// CONFIGURACIÓN DEL CRM
// ============================================
define('CRM_URL', 'https://gestion-tictac-comunicacion.es/index.php/api');
define('API_TOKEN', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJ1c2VyIjoicHJvZHVjY2lvbkB0aWN0YWMtY29tdW5pY2FjaW9uLmVzIiwibmFtZSI6IlByb2R1Y2Npb24iLCJBUElfVElNRSI6MTc2OTUwMjU3MX0.4RoKiYv6z8sBE5MdchSE8iQ7wJnXGOIAlW52Mjn5oZvdRJsAWG3l-VmVIMlj3DawwtDl21e26_twU77usBjuGw');

// ============================================
// RUTAS DEL SISTEMA
// ============================================
define('BASE_PATH', dirname(__FILE__));
define('DATA_PATH', BASE_PATH . '/data');
define('INCLUDES_PATH', BASE_PATH . '/includes');
define('ASSETS_PATH', BASE_PATH . '/assets');

// URLs para navegación
define('BASE_URL', 'https://gestion-tictac-comunicacion.es/api');
define('CLIENTES_URL', BASE_URL . '/clientes');
define('AUDITORIA_URL', BASE_URL . '/auditoria');
define('PRESUPUESTOS_URL', BASE_URL . '/presupuestos');
define('FACTURAS_URL', BASE_URL . '/facturas');

// ============================================
// CONFIGURACIÓN DE MARCA
// ============================================
define('COMPANY_NAME', 'Tictac Comunicación');

// Logos (coloca logocolor.png y logoblanco.png en /assets/img/)
define('LOGO_COLOR', BASE_URL . '/assets/img/logocolor.png');
define('LOGO_BLANCO', BASE_URL . '/assets/img/logoblanco.png');

// Colores corporativos
define('BRAND_COLOR', '#E91E8C');
define('BRAND_COLOR_DARK', '#C91E82');
define('ACCENT_COLOR', '#C6D617');

// ============================================
// CONFIGURACIÓN DEL SISTEMA
// ============================================
define('SYSTEM_VERSION', '1.0');
define('SYSTEM_TIMEZONE', 'Europe/Madrid');

// Establecer zona horaria
date_default_timezone_set(SYSTEM_TIMEZONE);

// ============================================
// FUNCIONES GLOBALES
// ============================================

/**
 * Realizar llamada a la API del CRM
 * @param string $endpoint Endpoint de la API (ej: 'clients', 'clients/1')
 * @param string $method Método HTTP (GET, POST, PUT, DELETE)
 * @param array $data Datos a enviar (opcional)
 * @return array|false Respuesta decodificada o false en caso de error
 */
function callCrmApi($endpoint, $method = 'GET', $data = null) {
    $url = CRM_URL . '/' . ltrim($endpoint, '/');
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'authtoken: ' . API_TOKEN,
        'Content-Type: application/json'
    ));
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    
    // Configurar método
    if ($method !== 'GET') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        if ($data !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200 && $response) {
        return json_decode($response, true);
    }
    
    return false;
}

/**
 * Obtener artículos/items del CRM
 * @return array Lista de artículos
 */
function getArticulosCRM() {
    $articulos = array();
    
    // Intentar con diferentes endpoints según el CRM
    $endpoints = ['items', 'invoice_items', 'estimate_items'];
    
    foreach ($endpoints as $endpoint) {
        $response = callCrmApi($endpoint);
        if ($response && is_array($response)) {
            // Si es array directo
            if (isset($response[0])) {
                $articulos = $response;
                break;
            }
            // Si está en data
            if (isset($response['data']) && is_array($response['data'])) {
                $articulos = $response['data'];
                break;
            }
        }
    }
    
    // Si no funciona, intentar cargar uno por uno
    if (empty($articulos)) {
        for ($id = 1; $id <= 100; $id++) {
            $item = callCrmApi('items/' . $id);
            if ($item && is_array($item) && !isset($item['error'])) {
                // Verificar que no esté eliminado
                if (!isset($item['deleted']) || $item['deleted'] == 0) {
                    $articulos[] = $item;
                }
            }
        }
    }
    
    return $articulos;
}

/**
 * Guardar registro en archivo JSON de auditoría
 * @param string $tipo Tipo de acción (email_bienvenida, sistema, etc.)
 * @param string $estado Estado (enviado, error, ejecutado)
 * @param string $mensaje Mensaje descriptivo
 * @param array $datos Datos adicionales
 */
function guardarAuditoria($tipo, $estado, $mensaje, $datos = array()) {
    $logFile = DATA_PATH . '/auditoria.json';
    
    $logs = array();
    if (file_exists($logFile)) {
        $logs = json_decode(file_get_contents($logFile), true);
        if (!is_array($logs)) $logs = array();
    }
    
    $registro = array(
        'fecha' => date('Y-m-d H:i:s'),
        'tipo' => $tipo,
        'estado' => $estado,
        'mensaje' => $mensaje,
        'cliente_id' => isset($datos['cliente_id']) ? $datos['cliente_id'] : 0,
        'cliente_nombre' => isset($datos['cliente_nombre']) ? $datos['cliente_nombre'] : '',
        'email' => isset($datos['email']) ? $datos['email'] : ''
    );
    
    $logs[] = $registro;
    file_put_contents($logFile, json_encode($logs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

/**
 * Formatear fecha en español
 * @param string $fecha Fecha en formato SQL
 * @return string Fecha formateada
 */
function formatearFecha($fecha) {
    if (empty($fecha)) return 'N/A';
    
    $timestamp = strtotime($fecha);
    return date('d/m/Y H:i', $timestamp);
}

/**
 * Obtener tipo de cliente según sus grupos
 * @param array $cliente Datos del cliente
 * @return string Tipo: 'con_mantenimiento', 'sin_mantenimiento', 'inactivo'
 */
function getTipoCliente($cliente) {
    $clientGroups = isset($cliente['client_groups']) ? strtolower($cliente['client_groups']) : '';
    
    // Verificar si es inactivo
    if (strpos($clientGroups, 'inactivo') !== false) {
        return 'inactivo';
    }
    
    // Verificar si tiene mantenimiento
    if (!empty($clientGroups)) {
        if (strpos($clientGroups, 'sin') !== false && strpos($clientGroups, 'mantenimiento') !== false) {
            return 'sin_mantenimiento';
        }
        if (strpos($clientGroups, 'mantenimiento') !== false) {
            return 'con_mantenimiento';
        }
    }
    
    return 'sin_mantenimiento';
}

/**
 * Redireccionar a una URL
 * @param string $url URL destino
 */
function redirect($url) {
    header("Location: " . $url);
    exit;
}

/**
 * Mostrar mensaje de error y terminar
 * @param string $mensaje Mensaje de error
 */
function mostrarError($mensaje) {
    http_response_code(500);
    echo json_encode(array('error' => true, 'mensaje' => $mensaje));
    exit;
}

/**
 * Mostrar mensaje de éxito
 * @param string $mensaje Mensaje
 * @param array $datos Datos adicionales
 */
function mostrarExito($mensaje, $datos = array()) {
    http_response_code(200);
    $response = array('error' => false, 'mensaje' => $mensaje);
    if (!empty($datos)) {
        $response = array_merge($response, $datos);
    }
    echo json_encode($response);
    exit;
}