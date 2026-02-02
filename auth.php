<?php
/**
 * Sistema de Autenticación
 * Verifica que el usuario esté logueado en el CRM
 */

session_start();

/**
 * Verificar si el usuario está autenticado en el CRM
 */
function verificarAutenticacion() {
    // Verificar si existe sesión del CRM
    // El CRM usa sesiones PHP, así que verificamos si el usuario está logueado
    
    // Opción 1: Verificar variable de sesión del CRM
    if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
        // No está logueado, redirigir al login del CRM
        header('Location: https://gestion-tictac-comunicacion.es/index.php/signin');
        exit;
    }
    
    // Opción 2: Verificar que tenga permisos de staff/admin
    if (isset($_SESSION['user_type']) && $_SESSION['user_type'] !== 'staff') {
        // No es staff, denegar acceso
        header('Location: https://gestion-tictac-comunicacion.es/index.php/signin');
        exit;
    }
    
    return true;
}

/**
 * Obtener información del usuario actual
 */
function getUsuarioActual() {
    if (isset($_SESSION['user_id'])) {
        return [
            'id' => $_SESSION['user_id'],
            'nombre' => $_SESSION['user_name'] ?? 'Usuario',
            'email' => $_SESSION['user_email'] ?? '',
            'tipo' => $_SESSION['user_type'] ?? 'staff'
        ];
    }
    return null;
}

/**
 * Cerrar sesión
 */
function cerrarSesion() {
    session_destroy();
    header('Location: https://gestion-tictac-comunicacion.es/index.php/signin');
    exit;
}