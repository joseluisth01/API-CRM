<?php
/**
 * Sistema de Bienvenida Automática
 * Detecta nuevos clientes y envía emails de bienvenida
 * 
 * Configurar en cron: */5 * * * * /usr/bin/php /ruta/a/bienvenida_auto.php
 */

// Configuración
$CRM_URL = 'https://gestion-tictac-comunicacion.es/index.php/api';
$API_TOKEN = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJ1c2VyIjoicHJvZHVjY2lvbkB0aWN0YWMtY29tdW5pY2FjaW9uLmVzIiwibmFtZSI6IlByb2R1Y2Npb24iLCJBUElfVElNRSI6MTc2OTUwMjU3MX0.4RoKiYv6z8sBE5MdchSE8iQ7wJnXGOIAlW52Mjn5oZvdRJsAWG3l-VmVIMlj3DawwtDl21e26_twU77usBjuGw';

// ⚠️ IMPORTANTE: FECHA DE CORTE
// Solo enviará emails a clientes creados DESPUÉS de esta fecha
// Formato: YYYY-MM-DD
// Cambia esta fecha a la fecha de hoy o cuando quieras que empiece
$FECHA_CORTE = '2025-01-28'; // ← CAMBIA ESTA FECHA

// Email settings
$SMTP_HOST = 'smtp.gmail.com';
$SMTP_PORT = 587;
$SMTP_USER = 'hola@tictac-comunicacion.es';
$SMTP_PASS = 'esfl ngja qmgc gobl';
$FROM_EMAIL = 'hola@tictac-comunicacion.es';
$FROM_NAME = 'Tictac Comunicación';

// Archivos
$PROCESSED_FILE = __DIR__ . '/clientes_procesados.json';
$AUDIT_FILE = __DIR__ . '/auditoria.json';

// Función para guardar en auditoría
function guardarAuditoria($tipo, $clienteId, $clienteNombre, $email, $estado, $mensaje = '') {
    global $AUDIT_FILE;
    
    $auditoria = array();
    if (file_exists($AUDIT_FILE)) {
        $contenido = file_get_contents($AUDIT_FILE);
        $auditoria = json_decode($contenido, true);
        if (!is_array($auditoria)) {
            $auditoria = array();
        }
    }
    
    $registro = array(
        'id' => uniqid(),
        'fecha' => date('Y-m-d H:i:s'),
        'tipo' => $tipo,
        'cliente_id' => $clienteId,
        'cliente_nombre' => $clienteNombre,
        'email' => $email,
        'estado' => $estado,
        'mensaje' => $mensaje
    );
    
    array_unshift($auditoria, $registro);
    
    // Mantener solo últimos 500 registros
    $auditoria = array_slice($auditoria, 0, 500);
    
    file_put_contents($AUDIT_FILE, json_encode($auditoria, JSON_PRETTY_PRINT));
}

// Función para obtener clientes procesados
function obtenerProcesados() {
    global $PROCESSED_FILE;
    
    if (file_exists($PROCESSED_FILE)) {
        $contenido = file_get_contents($PROCESSED_FILE);
        $data = json_decode($contenido, true);
        return is_array($data) ? $data : array();
    }
    
    return array();
}

// Función para marcar como procesado
function marcarProcesado($clienteId) {
    global $PROCESSED_FILE;
    
    $procesados = obtenerProcesados();
    
    if (!in_array($clienteId, $procesados)) {
        $procesados[] = $clienteId;
        file_put_contents($PROCESSED_FILE, json_encode($procesados));
    }
}

// Función para obtener clientes del CRM
function obtenerClientes() {
    global $CRM_URL, $API_TOKEN;
    
    $clientes = array();
    
    for ($id = 1; $id <= 200; $id++) {
        $url = $CRM_URL . "/clients/" . $id;
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'authtoken: ' . $API_TOKEN,
            'Content-Type: application/json'
        ));
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200 && $response) {
            $cliente = json_decode($response, true);
            if ($cliente && isset($cliente['deleted']) && $cliente['deleted'] === '0') {
                $clientes[] = $cliente;
            }
        }
    }
    
    return $clientes;
}

// Función para obtener contacto del cliente
function obtenerContacto($clienteId) {
    global $CRM_URL, $API_TOKEN;
    
    $url = $CRM_URL . '/contact_by_clientid/' . $clienteId;
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'authtoken: ' . $API_TOKEN,
        'Content-Type: application/json'
    ));
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200 && $response) {
        $contactos = json_decode($response, true);
        if (is_array($contactos) && count($contactos) > 0) {
            // Buscar contacto principal
            foreach ($contactos as $contacto) {
                if (isset($contacto['is_primary_contact']) && $contacto['is_primary_contact'] === '1') {
                    return $contacto;
                }
            }
            // Si no hay principal, devolver el primero
            return $contactos[0];
        }
    }
    
    return null;
}

// Función para enviar email
function enviarEmail($destinatario, $nombre, $empresa) {
    global $SMTP_HOST, $SMTP_PORT, $SMTP_USER, $SMTP_PASS, $FROM_EMAIL, $FROM_NAME;
    
    $asunto = '¡Bienvenido/a a Tictac Comunicación! 🎉';
    
    $mensaje = '
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <style>
            body { font-family: Arial, sans-serif; background: #f5f5f5; margin: 0; padding: 0; }
            .container { max-width: 600px; margin: 0 auto; background: white; }
            .header { background: linear-gradient(135deg, #E91E8C 0%, #C91E82 100%); color: white; padding: 40px 30px; text-align: center; }
            .logo { font-size: 48px; font-weight: 300; letter-spacing: 8px; }
            .tagline { font-size: 14px; letter-spacing: 2px; margin-top: 10px; opacity: 0.9; }
            .content { padding: 40px 30px; }
            .greeting { font-size: 24px; color: #333; margin-bottom: 20px; }
            .message { font-size: 16px; line-height: 1.6; color: #555; margin-bottom: 20px; }
            .highlight { color: #E91E8C; font-weight: bold; }
            .button { display: inline-block; background: #C6D617; color: #333; padding: 15px 40px; text-decoration: none; border-radius: 50px; font-weight: bold; margin: 20px 0; }
            .benefits { background: #f9f9f9; padding: 25px; border-left: 4px solid #E91E8C; margin: 25px 0; }
            .benefits ul { margin: 15px 0; padding-left: 20px; }
            .benefits li { margin: 10px 0; color: #555; }
            .footer { background: #333; color: white; padding: 30px; text-align: center; }
            .footer-logo { font-size: 24px; letter-spacing: 4px; margin-bottom: 15px; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <div class="logo">t/ctac</div>
                <div class="tagline">COMUNICACIÓN</div>
            </div>
            
            <div class="content">
                <div class="greeting">¡Hola ' . htmlspecialchars($nombre) . '! 👋</div>
                
                <p class="message">
                    Es un placer darte la bienvenida a <span class="highlight">' . htmlspecialchars($empresa) . '</span> 
                    como nuevo cliente de <strong>Tictac Comunicación</strong>.
                </p>
                
                <p class="message">
                    Somos tu agencia de marketing digital en Córdoba, especializada en impulsar negocios 
                    como el tuyo a través de estrategias personalizadas y resultados medibles.
                </p>
                
                <div class="benefits">
                    <strong style="color: #E91E8C; font-size: 18px;">¿Qué puedes esperar de nosotros?</strong>
                    <ul>
                        <li><strong>Estrategias personalizadas</strong> adaptadas a tus objetivos</li>
                        <li><strong>Comunicación directa</strong> con nuestro equipo</li>
                        <li><strong>Reportes detallados</strong> del progreso de tus campañas</li>
                        <li><strong>Acceso al portal de clientes</strong> 24/7</li>
                        <li><strong>Soporte prioritario</strong> cuando lo necesites</li>
                    </ul>
                </div>
                
                <center>
                    <a href="https://gestion-tictac-comunicacion.es" class="button">
                        Acceder al Portal de Clientes
                    </a>
                </center>
                
                <p class="message" style="margin-top: 30px;">
                    <strong>Próximos pasos:</strong><br>
                    Nuestro equipo se pondrá en contacto contigo en las próximas 24-48 horas 
                    para coordinar una reunión inicial y definir la mejor estrategia para tu negocio.
                </p>
                
                <p class="message">
                    Si tienes alguna pregunta, no dudes en contactarnos.
                </p>
                
                <p class="message" style="margin-top: 40px;">
                    <strong>¡Estamos emocionados de trabajar contigo!</strong><br>
                    <span style="color: #E91E8C;">El equipo de Tictac Comunicación</span>
                </p>
            </div>
            
            <div class="footer">
                <div class="footer-logo">t/ctac</div>
                <p><strong>Tictac Comunicación</strong></p>
                <p style="margin-top: 10px; font-size: 14px;">
                    📧 hola@tictac-comunicacion.es<br>
                    🌐 www.tictac-comunicacion.es
                </p>
                <p style="margin-top: 20px; font-size: 11px; color: #999;">
                    © ' . date('Y') . ' Tictac Comunicación. Todos los derechos reservados.
                </p>
            </div>
        </div>
    </body>
    </html>
    ';
    
    // Headers
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=utf-8\r\n";
    $headers .= "From: " . $FROM_NAME . " <" . $FROM_EMAIL . ">\r\n";
    $headers .= "Reply-To: " . $FROM_EMAIL . "\r\n";
    
    // Intentar enviar con mail() primero
    if (mail($destinatario, $asunto, $mensaje, $headers)) {
        return true;
    }
    
    // Si falla, intentar con SMTP manual
    return enviarEmailSMTP($destinatario, $asunto, $mensaje);
}

// Función de respaldo para enviar por SMTP
function enviarEmailSMTP($destinatario, $asunto, $mensaje) {
    global $SMTP_HOST, $SMTP_PORT, $SMTP_USER, $SMTP_PASS, $FROM_EMAIL, $FROM_NAME;
    
    $socket = fsockopen('tls://' . $SMTP_HOST, $SMTP_PORT, $errno, $errstr, 30);
    
    if (!$socket) {
        return false;
    }
    
    $response = fgets($socket, 515);
    
    fputs($socket, "EHLO " . $_SERVER['HTTP_HOST'] . "\r\n");
    $response = fgets($socket, 515);
    
    fputs($socket, "AUTH LOGIN\r\n");
    $response = fgets($socket, 515);
    
    fputs($socket, base64_encode($SMTP_USER) . "\r\n");
    $response = fgets($socket, 515);
    
    fputs($socket, base64_encode($SMTP_PASS) . "\r\n");
    $response = fgets($socket, 515);
    
    if (substr($response, 0, 3) !== '235') {
        fclose($socket);
        return false;
    }
    
    fputs($socket, "MAIL FROM: <" . $FROM_EMAIL . ">\r\n");
    $response = fgets($socket, 515);
    
    fputs($socket, "RCPT TO: <" . $destinatario . ">\r\n");
    $response = fgets($socket, 515);
    
    fputs($socket, "DATA\r\n");
    $response = fgets($socket, 515);
    
    $headers = "From: " . $FROM_NAME . " <" . $FROM_EMAIL . ">\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=utf-8\r\n";
    $headers .= "Subject: " . $asunto . "\r\n";
    
    fputs($socket, $headers . "\r\n" . $mensaje . "\r\n.\r\n");
    $response = fgets($socket, 515);
    
    fputs($socket, "QUIT\r\n");
    fclose($socket);
    
    return true;
}

// PROCESO PRINCIPAL
echo "[" . date('Y-m-d H:i:s') . "] Iniciando sistema de bienvenida automática...\n";

$procesados = obtenerProcesados();
echo "Clientes ya procesados: " . count($procesados) . "\n";

$clientes = obtenerClientes();
echo "Clientes encontrados en CRM: " . count($clientes) . "\n";

$nuevosClientes = 0;
$emailsEnviados = 0;

foreach ($clientes as $cliente) {
    $clienteId = $cliente['id'];
    $clienteNombre = $cliente['company_name'];
    $fechaCreacion = isset($cliente['created_date']) ? $cliente['created_date'] : '';
    
    // Si ya fue procesado, saltar
    if (in_array($clienteId, $procesados)) {
        continue;
    }
    
    // ⚠️ VALIDAR FECHA DE CORTE
    // Solo procesar clientes creados DESPUÉS de la fecha de corte
    if (!empty($fechaCreacion)) {
        $fechaCliente = date('Y-m-d', strtotime($fechaCreacion));
        $fechaCorte = date('Y-m-d', strtotime($FECHA_CORTE));
        
        if ($fechaCliente <= $fechaCorte) {
            echo "  ⏭️  Cliente antiguo (creado: $fechaCliente) - Omitiendo\n";
            // Marcar como procesado para no verificarlo de nuevo
            marcarProcesado($clienteId);
            continue;
        }
    }
    
    $nuevosClientes++;
    echo "\n[NUEVO CLIENTE] ID: $clienteId - $clienteNombre (Creado: $fechaCreacion)\n";
    
    // Obtener contacto
    $contacto = obtenerContacto($clienteId);
    
    if (!$contacto) {
        echo "  ⚠ No se encontró contacto para este cliente\n";
        guardarAuditoria('bienvenida', $clienteId, $clienteNombre, '', 'sin_contacto', 'No se encontró información de contacto');
        marcarProcesado($clienteId);
        continue;
    }
    
    $email = isset($contacto['email']) ? $contacto['email'] : '';
    $nombreContacto = (isset($contacto['first_name']) ? $contacto['first_name'] : '') . ' ' . (isset($contacto['last_name']) ? $contacto['last_name'] : '');
    $nombreContacto = trim($nombreContacto);
    
    if (empty($nombreContacto)) {
        $nombreContacto = 'Estimado/a';
    }
    
    if (empty($email)) {
        echo "  ⚠ El contacto no tiene email\n";
        guardarAuditoria('bienvenida', $clienteId, $clienteNombre, '', 'sin_email', 'El contacto no tiene email configurado');
        marcarProcesado($clienteId);
        continue;
    }
    
    echo "  📧 Enviando email a: $email\n";
    
    if (enviarEmail($email, $nombreContacto, $clienteNombre)) {
        echo "  ✅ Email enviado correctamente\n";
        guardarAuditoria('bienvenida', $clienteId, $clienteNombre, $email, 'enviado', 'Email de bienvenida enviado exitosamente');
        $emailsEnviados++;
    } else {
        echo "  ❌ Error al enviar email\n";
        guardarAuditoria('bienvenida', $clienteId, $clienteNombre, $email, 'error', 'Error al enviar el email');
    }
    
    marcarProcesado($clienteId);
}

echo "\n=== RESUMEN ===\n";
echo "Nuevos clientes detectados: $nuevosClientes\n";
echo "Emails enviados exitosamente: $emailsEnviados\n";
echo "Finalizado: " . date('Y-m-d H:i:s') . "\n";

// Guardar log de ejecución
guardarAuditoria('sistema', 0, 'Sistema', '', 'ejecutado', "Procesados: $nuevosClientes nuevos, $emailsEnviados emails enviados");
?>