<?php
/**
 * Dashboard Principal - Sistema Tictac
 * PaneEEEEEl de control con acceso a todas las funcionalidades
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Tictac Comunicaci��n</title>
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
            padding: 40px 30px;
            text-align: center;
            box-shadow: 0 2px 20px rgba(0,0,0,0.1);
        }
        
        .logo {
            font-size: 48px;
            font-weight: 300;
            letter-spacing: 8px;
            margin-bottom: 10px;
        }
        
        .tagline {
            font-size: 14px;
            letter-spacing: 2px;
            opacity: 0.9;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 50px 20px;
        }
        
        .welcome {
            text-align: center;
            margin-bottom: 50px;
        }
        
        .welcome h1 {
            color: #E91E8C;
            font-weight: 300;
            font-size: 32px;
            margin-bottom: 10px;
        }
        
        .welcome p {
            color: #666;
            font-size: 16px;
        }
        
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-top: 40px;
        }
        
        .dashboard-card {
            background: white;
            border-radius: 15px;
            padding: 40px 30px;
            text-align: center;
            box-shadow: 0 5px 25px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            cursor: pointer;
            text-decoration: none;
            color: inherit;
            display: block;
        }
        
        .dashboard-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.15);
        }
        
        .dashboard-card.active {
            border: 3px solid #C6D617;
        }
        
        .dashboard-card.disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .dashboard-card.disabled:hover {
            transform: none;
        }
        
        .card-icon {
            font-size: 64px;
            margin-bottom: 20px;
        }
        
        .card-title {
            font-size: 24px;
            font-weight: 600;
            color: #E91E8C;
            margin-bottom: 15px;
        }
        
        .card-description {
            color: #666;
            font-size: 14px;
            line-height: 1.6;
        }
        
        .badge {
            display: inline-block;
            background: #C6D617;
            color: #333;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            margin-top: 15px;
        }
        
        .badge-disabled {
            background: #ccc;
        }
        
        .footer {
            text-align: center;
            padding: 40px 20px;
            color: #666;
            margin-top: 60px;
        }
        
        .footer-logo {
            font-size: 24px;
            font-weight: 300;
            letter-spacing: 4px;
            color: #E91E8C;
            margin-bottom: 10px;
        }
        
        @media (max-width: 768px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
            
            .logo {
                font-size: 36px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">t/ctac</div>
        <div class="tagline">COMUNICACI�0�7N - PANEL DE GESTI�0�7N</div>
    </div>
    
    <div class="container">
        <div class="welcome">
            <h1>Bienvenido al Dashboard</h1>
            <p>Gestiona clientes, presupuestos y facturas desde un solo lugar</p>
        </div>
        
        <div class="dashboard-grid">
            <!-- Clientes -->
            <a href="clientes.php" class="dashboard-card active">
                <div class="card-icon">�9�5</div>
                <div class="card-title">Clientes</div>
                <div class="card-description">
                    Visualiza, filtra y gestiona todos tus clientes del CRM. 
                    Distingue entre activos e inactivos.
                </div>
                <span class="badge">Disponible</span>
            </a>
            
            <!-- Auditor��a -->
            <a href="auditoria.php" class="dashboard-card active">
                <div class="card-icon">�9�6</div>
                <div class="card-title">Auditor��a</div>
                <div class="card-description">
                    Registro completo de emails autom��ticos enviados y 
                    todas las acciones del sistema.
                </div>
                <span class="badge">Disponible</span>
            </a>
            
            <!-- Preparar Presupuesto -->
            <a href="#" class="dashboard-card disabled" onclick="return false;">
                <div class="card-icon">�9�5</div>
                <div class="card-title">Preparar Presupuesto</div>
                <div class="card-description">
                    Crea y genera presupuestos profesionales para tus clientes 
                    de forma r��pida y sencilla.
                </div>
                <span class="badge badge-disabled">Pr��ximamente</span>
            </a>
            
            <!-- Preparar Factura -->
            <a href="#" class="dashboard-card disabled" onclick="return false;">
                <div class="card-icon">�0�8</div>
                <div class="card-title">Preparar Factura</div>
                <div class="card-description">
                    Genera facturas profesionales y env��alas directamente 
                    a tus clientes desde el sistema.
                </div>
                <span class="badge badge-disabled">Pr��ximamente</span>
            </a>
        </div>
    </div>
    
    <div class="footer">
        <div class="footer-logo">t/ctac</div>
        <p><strong>Tictac Comunicaci��n</strong> - Agencia de Marketing Digital</p>
        <p style="font-size: 12px; margin-top: 10px; color: #999;">
            Sistema de Gesti��n v1.0 - <?= date('Y') ?>
        </p>
    </div>
</body>
</html>