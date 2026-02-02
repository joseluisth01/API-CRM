<?php
/**
 * Gestión de Clientes - Sistema Tictac
 * Lista completa de clientes con filtros por grupo
 */

// Mostrar errores para debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configuración
$CRM_URL = 'https://gestion-tictac-comunicacion.es/index.php/api';
$API_TOKEN = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJ1c2VyIjoicHJvZHVjY2lvbkB0aWN0YWMtY29tdW5pY2FjaW9uLmVzIiwibmFtZSI6IlByb2R1Y2Npb24iLCJBUElfVElNRSI6MTc2OTUwMjU3MX0.4RoKiYv6z8sBE5MdchSE8iQ7wJnXGOIAlW52Mjn5oZvdRJsAWG3l-VmVIMlj3DawwtDl21e26_twU77usBjuGw';

// Obtener filtro (por defecto: con mantenimiento)
$filtro = isset($_GET['filtro']) ? $_GET['filtro'] : 'con_mantenimiento';

// Función para obtener clientes
function obtenerClientes($crmUrl, $apiToken, $limite = 200) {
    $clientes = array();
    
    for ($id = 1; $id <= $limite; $id++) {
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
            if ($cliente) {
                $clientes[] = $cliente;
            }
        }
    }
    
    return $clientes;
}

// Función para obtener información del contacto
function obtenerContacto($crmUrl, $apiToken, $clientId) {
    $url = $crmUrl . "/contact_by_clientid/" . $clientId;
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'authtoken: ' . $apiToken,
        'Content-Type: application/json'
    ));
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200 && $response) {
        $contactos = json_decode($response, true);
        // Devolver array de contactos
        return is_array($contactos) ? $contactos : array();
    }
    
    return array();
}

// Obtener todos los clientes
$todosLosClientes = obtenerClientes($CRM_URL, $API_TOKEN, 200);

// Función para determinar el tipo de cliente
function getTipoCliente($cliente) {
    $clientGroups = isset($cliente['client_groups']) ? strtolower($cliente['client_groups']) : '';
    
    // Verificar si es inactivo (por grupo, no por deleted)
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
    
    // Por defecto, sin mantenimiento
    return 'sin_mantenimiento';
}

// Filtrar clientes
$clientesFiltrados = array();
$totalConMantenimiento = 0;
$totalSinMantenimiento = 0;
$totalInactivos = 0;

foreach ($todosLosClientes as $cliente) {
    $tipo = getTipoCliente($cliente);
    
    if ($tipo === 'con_mantenimiento') {
        $totalConMantenimiento++;
    } elseif ($tipo === 'sin_mantenimiento') {
        $totalSinMantenimiento++;
    } elseif ($tipo === 'inactivo') {
        $totalInactivos++;
    }
    
    // Aplicar filtro
    if ($filtro === 'todos' || $filtro === $tipo) {
        $clientesFiltrados[] = $cliente;
    }
}

$totalGeneral = count($todosLosClientes);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clientes - Tictac Comunicación</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            background: #f5f5f5;
            color: #333;
        }
        
        .header {
            background: linear-gradient(135deg, #E91E8C 0%, #C91E82 100%);
            color: white;
            padding: 30px;
            box-shadow: 0 2px 20px rgba(0,0,0,0.1);
        }
        
        .header-content {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            font-size: 36px;
            font-weight: 300;
            letter-spacing: 8px;
        }
        
        .back-button {
            background: rgba(255,255,255,0.2);
            color: white;
            padding: 10px 25px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .back-button:hover {
            background: rgba(255,255,255,0.3);
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 30px 20px;
        }
        
        .page-header h1 {
            color: #E91E8C;
            font-weight: 300;
            font-size: 32px;
            margin-bottom: 10px;
        }
        
        .stats-bar {
            background: white;
            padding: 20px 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-around;
            margin: 30px 0;
            flex-wrap: wrap;
            gap: 20px;
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-number {
            font-size: 36px;
            font-weight: bold;
            color: #E91E8C;
        }
        
        .stat-label {
            color: #666;
            font-size: 14px;
        }
        
        .filters {
            background: white;
            padding: 20px 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 30px;
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .filter-button {
            padding: 10px 25px;
            border: 2px solid #ddd;
            background: white;
            border-radius: 50px;
            cursor: pointer;
            font-weight: 600;
            color: #666;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .filter-button:hover {
            border-color: #E91E8C;
            color: #E91E8C;
        }
        
        .filter-button.active {
            background: #E91E8C;
            color: white;
            border-color: #E91E8C;
        }
        
        .table-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        thead {
            background: #E91E8C;
            color: white;
        }
        
        th {
            padding: 15px;
            text-align: left;
            font-weight: 600;
        }
        
        td {
            padding: 15px;
            border-bottom: 1px solid #f0f0f0;
        }
        
        tbody tr:hover {
            background: #f9f9f9;
        }
        
        .client-name {
            color: #E91E8C;
            cursor: pointer;
            font-weight: 600;
        }
        
        .client-name:hover {
            color: #C91E82;
            text-decoration: underline;
        }
        
        .badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .badge-con-mant {
            background: #d4edda;
            color: #155724;
        }
        
        .badge-sin-mant {
            background: #fff3cd;
            color: #856404;
        }
        
        .badge-inactive {
            background: #f8d7da;
            color: #721c24;
        }
        
        /* Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
        }
        
        .modal-content {
            background: white;
            margin: 10px auto;
            padding: 0;
            border-radius: 15px;
            width: 90%;
            max-width: 600px;
            box-shadow: 0 10px 50px rgba(0,0,0,0.3);
        }
        
        .modal-header {
            background: linear-gradient(135deg, #E91E8C 0%, #C91E82 100%);
            color: white;
            padding: 15px;
            border-radius: 15px 15px 0 0;
            display: flex;
            justify-content: space-between;
            align-items:center;
        }
        
        .modal-header h2 {
            font-weight: 300;
            font-size: 24px;
        }
        
        .close {
            color: white;
            font-size: 32px;
            cursor: pointer;
        }
        
        .modal-body {
            padding: 30px;
        }
        
        .info-row {
            display: flex;
            padding: 15px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .info-label {
            flex: 0 0 150px;
            font-weight: 600;
            color: #666;
        }
        
        .info-value {
            flex: 1;
            color: #333;
        }
        
        .info-value a {
            color: #E91E8C;
            text-decoration: none;
        }
        
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 15px;
            }
            
            table {
                min-width: 700px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <div class="logo">t/ctac</div>
            <a href="index.php" class="back-button">← Volver al Dashboard</a>
        </div>
    </div>
    
    <div class="container">
        <div class="page-header">
            <h1>👥 Gestión de Clientes</h1>
            <p style="color: #666;">Visualiza y gestiona todos los clientes del CRM</p>
        </div>
        
        <!-- Estadísticas -->
        <div class="stats-bar">
            <div class="stat-item">
                <div class="stat-number"><?php echo $totalGeneral; ?></div>
                <div class="stat-label">Total Clientes</div>
            </div>
            <div class="stat-item">
                <div class="stat-number"><?php echo $totalConMantenimiento; ?></div>
                <div class="stat-label">Con Mantenimiento</div>
            </div>
            <div class="stat-item">
                <div class="stat-number"><?php echo $totalSinMantenimiento; ?></div>
                <div class="stat-label">Sin Mantenimiento</div>
            </div>
            <div class="stat-item">
                <div class="stat-number"><?php echo $totalInactivos; ?></div>
                <div class="stat-label">Inactivos</div>
            </div>
        </div>
        
        <!-- Filtros -->
        <div class="filters">
            <span style="font-weight: 600; color: #666;">Filtrar por:</span>
            <a href="?filtro=con_mantenimiento" class="filter-button <?php echo $filtro === 'con_mantenimiento' ? 'active' : ''; ?>">
                ✓ Con Mantenimiento (<?php echo $totalConMantenimiento; ?>)
            </a>
            <a href="?filtro=sin_mantenimiento" class="filter-button <?php echo $filtro === 'sin_mantenimiento' ? 'active' : ''; ?>">
                ⚠ Sin Mantenimiento (<?php echo $totalSinMantenimiento; ?>)
            </a>
            <a href="?filtro=inactivo" class="filter-button <?php echo $filtro === 'inactivo' ? 'active' : ''; ?>">
                ✗ Inactivos (<?php echo $totalInactivos; ?>)
            </a>
            <a href="?filtro=todos" class="filter-button <?php echo $filtro === 'todos' ? 'active' : ''; ?>">
                ⊛ Todos (<?php echo $totalGeneral; ?>)
            </a>
        </div>
        
        <!-- Tabla -->
        <div class="table-container">
            <?php if (count($clientesFiltrados) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Empresa</th>
                            <th>Contacto</th>
                            <th>Teléfono</th>
                            <th>Fecha Creación</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($clientesFiltrados as $cliente): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($cliente['id']); ?></strong></td>
                                
                                <td>
                                    <span class="client-name" onclick='showClientModal(<?php echo json_encode($cliente); ?>)'>
                                        <?php echo htmlspecialchars($cliente['company_name']); ?>
                                    </span>
                                </td>
                                
                                <td><?php echo htmlspecialchars(isset($cliente['primary_contact']) ? $cliente['primary_contact'] : 'Sin contacto'); ?></td>
                                
                                <td><?php echo !empty($cliente['phone']) ? htmlspecialchars($cliente['phone']) : '-'; ?></td>
                                
                                <td><?php echo htmlspecialchars(isset($cliente['created_date']) ? $cliente['created_date'] : 'N/A'); ?></td>
                                
                                <td>
                                    <?php 
                                    $tipo = getTipoCliente($cliente);
                                    if ($tipo === 'inactivo'): 
                                    ?>
                                        <span class="badge badge-inactive">✗ Inactivo</span>
                                    <?php elseif ($tipo === 'con_mantenimiento'): ?>
                                        <span class="badge badge-con-mant">✓ Con Mantenimiento</span>
                                    <?php else: ?>
                                        <span class="badge badge-sin-mant">⚠ Sin Mantenimiento</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div style="text-align: center; padding: 60px 20px; color: #999;">
                    <div style="font-size: 64px; margin-bottom: 20px;">📭</div>
                    <h3>No hay clientes en esta categoría</h3>
                    <p>Prueba cambiando el filtro</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Modal -->
    <div id="clientModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Información del Cliente</h2>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            <div class="modal-body" id="modalBody"></div>
        </div>
    </div>
    
    <div style="text-align: center; padding: 40px 20px; color: #666;">
        <p><strong>Tictac Comunicación</strong> - Sistema de Gestión</p>
        <p style="font-size: 12px; margin-top: 10px;">
            Última actualización: <?php echo date('d/m/Y H:i:s'); ?>
        </p>
    </div>
    
    <script>
        function showClientModal(cliente) {
            const modal = document.getElementById('clientModal');
            const modalTitle = document.getElementById('modalTitle');
            const modalBody = document.getElementById('modalBody');
            
            modalTitle.textContent = cliente.company_name || 'Cliente';
            
            // Mostrar loading
            modalBody.innerHTML = '<div style="text-align: center; padding: 40px;"><div style="font-size: 48px;">⏳</div><p>Cargando información del contacto...</p></div>';
            modal.style.display = 'block';
            
            // Cargar información del contacto
            fetch('get_contacto.php?client_id=' + cliente.id)
                .then(response => response.json())
                .then(contactos => {
                    let html = '';
                    
                    // INFORMACIÓN DE LA EMPRESA
                    html += '<h3 style="color: #E91E8C; margin-bottom: 15px; font-size: 18px;">📊 Información de la Empresa</h3>';
                    
                    html += '<div class="info-row"><div class="info-label">ID:</div><div class="info-value"><strong>' + cliente.id + '</strong></div></div>';
                    html += '<div class="info-row"><div class="info-label">Empresa:</div><div class="info-value"><strong>' + cliente.company_name + '</strong></div></div>';
                    
                    if (cliente.vat_number) {
                        html += '<div class="info-row"><div class="info-label">NIF/CIF:</div><div class="info-value">' + cliente.vat_number + '</div></div>';
                    }
                    
                    if (cliente.phone) {
                        html += '<div class="info-row"><div class="info-label">Teléfono:</div><div class="info-value"><a href="tel:' + cliente.phone + '">' + cliente.phone + '</a></div></div>';
                    }
                    
                    if (cliente.address) {
                        let address = cliente.address;
                        if (cliente.city) address += '<br>' + cliente.city;
                        if (cliente.state) address += ', ' + cliente.state;
                        if (cliente.zip) address += ' - ' + cliente.zip;
                        if (cliente.country) address += '<br>' + cliente.country;
                        html += '<div class="info-row"><div class="info-label">Dirección:</div><div class="info-value">' + address + '</div></div>';
                    }
                    
                    if (cliente.website) {
                        html += '<div class="info-row"><div class="info-label">Website:</div><div class="info-value"><a href="' + cliente.website + '" target="_blank">' + cliente.website + '</a></div></div>';
                    }
                    
                    if (cliente.created_date) {
                        html += '<div class="info-row"><div class="info-label">Fecha Creación:</div><div class="info-value">' + cliente.created_date + '</div></div>';
                    }
                    
                    if (cliente.client_groups) {
                        html += '<div class="info-row"><div class="info-label">Grupos:</div><div class="info-value">' + cliente.client_groups + '</div></div>';
                    }
                    
                    if (cliente.owner_name) {
                        html += '<div class="info-row"><div class="info-label">Propietario CRM:</div><div class="info-value">' + cliente.owner_name + '</div></div>';
                    }
                    
                    // INFORMACIÓN DE CONTACTOS
                    if (contactos && contactos.length > 0) {
                        html += '<h3 style="color: #E91E8C; margin: 30px 0 15px 0; font-size: 18px;">👤 Contactos</h3>';
                        
                        contactos.forEach(function(contacto, index) {
                            if (index > 0) {
                                html += '<div style="border-top: 2px solid #E91E8C; margin: 20px 0; padding-top: 20px;"></div>';
                            }
                            
                            const nombreCompleto = (contacto.first_name || '') + ' ' + (contacto.last_name || '');
                            if (nombreCompleto.trim()) {
                                html += '<div class="info-row"><div class="info-label">Nombre:</div><div class="info-value"><strong>' + nombreCompleto + '</strong></div></div>';
                            }
                            
                            if (contacto.job_title) {
                                html += '<div class="info-row"><div class="info-label">Cargo:</div><div class="info-value">' + contacto.job_title + '</div></div>';
                            }
                            
                            if (contacto.email) {
                                html += '<div class="info-row"><div class="info-label">Email:</div><div class="info-value"><a href="mailto:' + contacto.email + '">' + contacto.email + '</a></div></div>';
                            }
                            
                            if (contacto.phone) {
                                html += '<div class="info-row"><div class="info-label">Teléfono:</div><div class="info-value"><a href="tel:' + contacto.phone + '">' + contacto.phone + '</a></div></div>';
                            }
                            
                            if (contacto.alternative_phone) {
                                html += '<div class="info-row"><div class="info-label">Teléfono Alt.:</div><div class="info-value"><a href="tel:' + contacto.alternative_phone + '">' + contacto.alternative_phone + '</a></div></div>';
                            }
                            
                            if (contacto.address) {
                                html += '<div class="info-row"><div class="info-label">Dirección:</div><div class="info-value">' + contacto.address + '</div></div>';
                            }
                            
                            if (contacto.gender) {
                                const genero = contacto.gender === 'male' ? 'Hombre' : contacto.gender === 'female' ? 'Mujer' : 'Otro';
                                html += '<div class="info-row"><div class="info-label">Género:</div><div class="info-value">' + genero + '</div></div>';
                            }
                            
                            if (contacto.skype) {
                                html += '<div class="info-row"><div class="info-label">Skype:</div><div class="info-value">' + contacto.skype + '</div></div>';
                            }
                            
                            if (contacto.is_primary_contact === '1') {
                                html += '<div class="info-row"><div class="info-label">Tipo:</div><div class="info-value"><span style="background: #C6D617; padding: 3px 10px; border-radius: 10px; font-size: 11px; font-weight: bold;">★ CONTACTO PRINCIPAL</span></div></div>';
                            }
                        });
                    } else {
                        html += '<div style="text-align: center; padding: 20px; color: #999; margin-top: 20px;"><p>No hay contactos registrados para este cliente</p></div>';
                    }
                    
                    modalBody.innerHTML = html;
                })
                .catch(error => {
                    console.error('Error:', error);
                    // Mostrar info básica si falla la carga del contacto
                    let html = '<h3 style="color: #E91E8C; margin-bottom: 15px;">📊 Información de la Empresa</h3>';
                    html += '<div class="info-row"><div class="info-label">ID:</div><div class="info-value"><strong>' + cliente.id + '</strong></div></div>';
                    html += '<div class="info-row"><div class="info-label">Empresa:</div><div class="info-value"><strong>' + cliente.company_name + '</strong></div></div>';
                    
                    if (cliente.primary_contact) {
                        html += '<div class="info-row"><div class="info-label">Contacto:</div><div class="info-value">' + cliente.primary_contact + '</div></div>';
                    }
                    
                    const email = cliente.email || '';
                    if (email) {
                        html += '<div class="info-row"><div class="info-label">Email:</div><div class="info-value"><a href="mailto:' + email + '">' + email + '</a></div></div>';
                    }
                    
                    html += '<div style="text-align: center; padding: 20px; color: #e74c3c; margin-top: 20px;"><p>⚠️ No se pudo cargar la información del contacto</p></div>';
                    
                    modalBody.innerHTML = html;
                });
        }
        
        function closeModal() {
            document.getElementById('clientModal').style.display = 'none';
        }
        
        window.onclick = function(event) {
            if (event.target == document.getElementById('clientModal')) {
                closeModal();
            }
        }
        
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeModal();
            }
        });
    </script>
</body>
</html>