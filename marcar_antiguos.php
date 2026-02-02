<?php
/**
 * Marcar Clientes Antiguos como Procesados
 * Este script marca todos los clientes existentes como "ya procesados"
 * para que NO reciban email de bienvenida
 * 
 * ⚠️ EJECUTAR ESTE SCRIPT SOLO UNA VEZ ANTES DE ACTIVAR EL SISTEMA AUTOMÁTICO
 */

// Configuración
$CRM_URL = 'https://gestion-tictac-comunicacion.es/index.php/api';
$API_TOKEN = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJ1c2VyIjoicHJvZHVjY2lvbkB0aWN0YWMtY29tdW5pY2FjaW9uLmVzIiwibmFtZSI6IlByb2R1Y2Npb24iLCJBUElfVElNRSI6MTc2OTUwMjU3MX0.4RoKiYv6z8sBE5MdchSE8iQ7wJnXGOIAlW52Mjn5oZvdRJsAWG3l-VmVIMlj3DawwtDl21e26_twU77usBjuGw';
$PROCESSED_FILE = __DIR__ . '/clientes_procesados.json';

// Token de seguridad (cámbialo por algo secreto)
$TOKEN_SEGURIDAD = 'marcar_antiguos_tictac_2025';

// Verificar token
$token = isset($_GET['token']) ? $_GET['token'] : '';

if ($token !== $TOKEN_SEGURIDAD) {
    die('
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Acceso Denegado</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                background: #f5f5f5;
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
                margin: 0;
            }
            .card {
                background: white;
                padding: 40px;
                border-radius: 10px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                max-width: 500px;
                text-align: center;
            }
            h1 { color: #e74c3c; }
            p { color: #666; line-height: 1.6; }
            code {
                background: #f4f4f4;
                padding: 2px 8px;
                border-radius: 3px;
                font-family: monospace;
            }
        </style>
    </head>
    <body>
        <div class="card">
            <h1>🔒 Acceso Denegado</h1>
            <p>Necesitas el token de seguridad para ejecutar este script.</p>
            <p>URL correcta: <br><code>marcar_antiguos.php?token=TOKEN</code></p>
        </div>
    </body>
    </html>
    ');
}

// Función para obtener clientes
function obtenerTodosLosClientes($crmUrl, $apiToken) {
    $clientes = array();
    
    echo "<p>Buscando clientes en el CRM...</p>";
    flush();
    
    for ($id = 1; $id <= 300; $id++) {
        $url = $crmUrl . "/clients/" . $id;
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'authtoken: ' . $apiToken,
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
        
        // Mostrar progreso cada 50 clientes
        if ($id % 50 === 0) {
            echo "<p>Verificados: $id clientes...</p>";
            flush();
        }
    }
    
    return $clientes;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marcar Clientes Antiguos - Tictac</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
        }
        .card {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        h1 {
            color: #E91E8C;
            margin-bottom: 10px;
        }
        .warning {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 20px;
            margin: 20px 0;
        }
        .success {
            background: #d4edda;
            border-left: 4px solid #28a745;
            padding: 20px;
            margin: 20px 0;
        }
        .info {
            background: #d1ecf1;
            border-left: 4px solid #0c5460;
            padding: 20px;
            margin: 20px 0;
        }
        .button {
            display: inline-block;
            background: #E91E8C;
            color: white;
            padding: 15px 30px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: bold;
            margin: 10px 5px;
        }
        .button:hover {
            background: #C91E82;
        }
        .button-secondary {
            background: #C6D617;
            color: #333;
        }
        pre {
            background: #2c3e50;
            color: #ecf0f1;
            padding: 20px;
            border-radius: 5px;
            overflow-x: auto;
            max-height: 400px;
            overflow-y: auto;
        }
        .progress {
            background: #f0f0f0;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <h1>🔧 Marcar Clientes Antiguos como Procesados</h1>
            <p style="color: #666;">
                Este script marca todos los clientes existentes en tu CRM como "ya procesados" 
                para que NO reciban el email de bienvenida automático.
            </p>
            
            <div class="warning">
                <strong>⚠️ Importante:</strong> Este script debe ejecutarse SOLO UNA VEZ antes de activar 
                el sistema automático de bienvenida. Una vez ejecutado, solo los nuevos clientes 
                que se creen a partir de ahora recibirán el email de bienvenida.
            </div>
            
            <?php if (isset($_GET['ejecutar']) && $_GET['ejecutar'] === '1'): ?>
                <div class="progress">
                    <h3 style="margin-top: 0;">Procesando...</h3>
                    <?php
                    $clientes = obtenerTodosLosClientes($CRM_URL, $API_TOKEN);
                    $totalClientes = count($clientes);
                    
                    echo "<p><strong>✓ Clientes encontrados: $totalClientes</strong></p>";
                    
                    // Obtener IDs
                    $clienteIds = array();
                    foreach ($clientes as $cliente) {
                        $clienteIds[] = $cliente['id'];
                    }
                    
                    // Guardar en archivo
                    file_put_contents($PROCESSED_FILE, json_encode($clienteIds));
                    
                    echo "<p><strong>✓ Archivo de procesados creado/actualizado</strong></p>";
                    ?>
                </div>
                
                <div class="success">
                    <h3 style="margin-top: 0;">✅ ¡Completado!</h3>
                    <p><strong>Se han marcado <?php echo $totalClientes; ?> clientes como procesados.</strong></p>
                    <p>Estos clientes NO recibirán el email de bienvenida automático.</p>
                    <p>A partir de ahora, solo los clientes NUEVOS recibirán el email.</p>
                </div>
                
                <div class="info">
                    <h4>Próximos pasos:</h4>
                    <ol style="line-height: 2;">
                        <li>Configura el Cron Job para que el sistema se ejecute automáticamente</li>
                        <li>Crea un cliente de prueba para verificar que funciona</li>
                        <li>Revisa la auditoría para ver los registros</li>
                    </ol>
                </div>
                
                <h3>Clientes marcados como procesados:</h3>
                <pre><?php
                foreach ($clientes as $cliente) {
                    echo "ID " . $cliente['id'] . " - " . htmlspecialchars($cliente['company_name']) . "\n";
                }
                ?></pre>
                
                <a href="index.php" class="button">Volver al Dashboard</a>
                <a href="auditoria.php" class="button button-secondary">Ver Auditoría</a>
                
            <?php else: ?>
                <div class="info">
                    <h3 style="margin-top: 0;">¿Qué hace este script?</h3>
                    <ol style="line-height: 2;">
                        <li>Busca TODOS los clientes actuales en tu CRM (hasta 300 clientes)</li>
                        <li>Obtiene sus IDs</li>
                        <li>Los marca como "ya procesados" en el archivo <code>clientes_procesados.json</code></li>
                        <li>A partir de ese momento, solo los clientes NUEVOS recibirán email</li>
                    </ol>
                </div>
                
                <h3>Configuración actual:</h3>
                <ul style="line-height: 2; color: #666;">
                    <li><strong>CRM:</strong> gestion-tictac-comunicacion.es</li>
                    <li><strong>Buscar hasta:</strong> 300 clientes</li>
                    <li><strong>Archivo:</strong> clientes_procesados.json</li>
                </ul>
                
                <div class="warning">
                    <strong>⚠️ Una vez ejecutado, este proceso NO se puede deshacer fácilmente.</strong><br>
                    Solo ejecútalo si estás seguro de que quieres que los clientes actuales NO reciban 
                    el email de bienvenida.
                </div>
                
                <div style="margin-top: 40px;">
                    <a href="?token=<?php echo $TOKEN_SEGURIDAD; ?>&ejecutar=1" class="button">
                        ▶️ Ejecutar Ahora
                    </a>
                    <a href="index.php" class="button button-secondary">Cancelar</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>