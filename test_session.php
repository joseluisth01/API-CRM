<?php
/**
 * TEST SESSION - Ver variables de sesión
 * ELIMINAR DESPUÉS DE USAR
 */

session_start();

?>
<!DOCTYPE html>
<html>
<head>
    <title>Test Session</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 40px; background: #f5f5f5; }
        .container { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #E91E8C; }
        pre { background: #f9f9f9; padding: 20px; border-radius: 5px; overflow-x: auto; }
        .info { background: #d4edda; padding: 15px; border-radius: 5px; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Variables de Sesión del CRM</h1>
        
        <div class="info">
            <strong>Instrucciones:</strong><br>
            1. Asegúrate de estar logueado en el CRM<br>
            2. Mira las variables abajo<br>
            3. Busca variables como: user_id, logged_in, is_admin, user_type, etc.<br>
            4. Dime qué variables ves y te actualizo el auth.php
        </div>
        
        <h2>$_SESSION:</h2>
        <pre><?php print_r($_SESSION); ?></pre>
        
        <h2>Variables Importantes:</h2>
        <?php
        $important_vars = [
            'user_id',
            'login_user_id', 
            'user_logged_in',
            'logged_in',
            'is_admin',
            'user_type',
            'login_user',
            'user_name',
            'user_email',
            'is_staff'
        ];
        
        echo "<ul>";
        foreach ($important_vars as $var) {
            $exists = isset($_SESSION[$var]);
            $value = $exists ? $_SESSION[$var] : 'NO EXISTE';
            $color = $exists ? '#28a745' : '#dc3545';
            echo "<li style='color: {$color};'><strong>\$_SESSION['{$var}']</strong> = " . 
                 htmlspecialchars(print_r($value, true)) . "</li>";
        }
        echo "</ul>";
        ?>
        
        <h2>Cookies:</h2>
        <pre><?php print_r($_COOKIE); ?></pre>
        
        <h2>Info del Request:</h2>
        <pre>
IP: <?php echo $_SERVER['REMOTE_ADDR']; ?>

User Agent: <?php echo $_SERVER['HTTP_USER_AGENT']; ?>

Referer: <?php echo $_SERVER['HTTP_REFERER'] ?? 'N/A'; ?>
        </pre>
    </div>
</body>
</html>