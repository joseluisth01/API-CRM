<?php
/**
 * Ejecutor Manual del Sistema de Bienvenida
 * Para probar desde el navegador
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ejecutar Sistema - Tictac</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
        }
        .card {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #E91E8C;
            margin-bottom: 10px;
        }
        .button {
            display: inline-block;
            background: #E91E8C;
            color: white;
            padding: 15px 40px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: bold;
            margin: 10px 5px;
            transition: all 0.3s;
        }
        .button:hover {
            background: #C91E82;
            transform: translateY(-2px);
        }
        .button-secondary {
            background: #C6D617;
            color: #333;
        }
        .button-secondary:hover {
            background: #b5c015;
        }
        .result {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
            border-left: 4px solid #E91E8C;
        }
        pre {
            background: #2c3e50;
            color: #ecf0f1;
            padding: 20px;
            border-radius: 5px;
            overflow-x: auto;
            max-height: 500px;
            overflow-y: auto;
        }
        .warning {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
        }
        .success {
            background: #d4edda;
            border-left: 4px solid #28a745;
            padding: 15px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <h1>🚀 Sistema de Bienvenida Automática</h1>
            <p style="color: #666; margin-bottom: 30px;">
                Ejecuta manualmente el sistema para probar o enviar emails a nuevos clientes
            </p>
            
            <?php if (isset($_GET['ejecutar'])): ?>
                <div class="success">
                    <strong>✅ Sistema ejecutado correctamente</strong>
                </div>
                
                <div class="result">
                    <h3 style="margin-top: 0;">Resultado de la ejecución:</h3>
                    <pre><?php
                    ob_start();
                    include 'bienvenida_auto.php';
                    $output = ob_get_clean();
                    echo htmlspecialchars($output);
                    ?></pre>
                </div>
                
                <a href="auditoria.php" class="button">Ver Auditoría Completa</a>
                <a href="ejecutar_manual.php" class="button button-secondary">Ejecutar de Nuevo</a>
                <a href="index.php" class="button button-secondary">Volver al Dashboard</a>
                
            <?php else: ?>
                <div class="warning">
                    <h3 style="margin-top: 0;">⚠️ IMPORTANTE: Clientes Antiguos</h3>
                    <p><strong>Si es la PRIMERA VEZ que ejecutas este sistema:</strong></p>
                    <ol style="line-height: 2;">
                        <li>El sistema enviará emails a TODOS los clientes que no hayan sido procesados antes</li>
                        <li>Si tienes clientes antiguos que NO quieres que reciban el email, primero debes marcarlos como procesados</li>
                        <li>Para eso, usa el script: <code>marcar_antiguos.php</code></li>
                    </ol>
                    <p style="margin-top: 15px;">
                        <a href="marcar_antiguos.php?token=marcar_antiguos_tictac_2025" class="button" style="background: #ffc107; color: #333;">
                            🔧 Marcar Clientes Antiguos Primero
                        </a>
                    </p>
                </div>
                
                <div class="warning">
                    <strong>⚠️ Importante:</strong> Este sistema detecta clientes nuevos (que no hayan recibido email antes) 
                    y les envía automáticamente un email de bienvenida.
                </div>
                
                <h3>¿Qué hace el sistema?</h3>
                <ul style="line-height: 2;">
                    <li>✓ Busca clientes en el CRM</li>
                    <li>✓ Identifica cuáles no han recibido email de bienvenida</li>
                    <li>✓ Obtiene información del contacto principal</li>
                    <li>✓ Envía email personalizado de bienvenida</li>
                    <li>✓ Registra todo en auditoría</li>
                    <li>✓ Marca clientes como procesados (no envía duplicados)</li>
                </ul>
                
                <h3 style="margin-top: 30px;">Configuración Actual:</h3>
                <ul style="line-height: 2; color: #666;">
                    <li><strong>CRM:</strong> gestion-tictac-comunicacion.es</li>
                    <li><strong>Email desde:</strong> hola@tictac-comunicacion.es</li>
                    <li><strong>Busca hasta:</strong> 200 clientes</li>
                </ul>
                
                <div style="margin-top: 40px;">
                    <a href="?ejecutar=1" class="button">▶️ Ejecutar Sistema Ahora</a>
                    <a href="auditoria.php" class="button button-secondary">Ver Auditoría</a>
                    <a href="index.php" class="button button-secondary">Volver al Dashboard</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>