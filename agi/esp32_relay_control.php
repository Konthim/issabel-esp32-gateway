#!/usr/bin/php -q
<?php
require_once('/var/www/html/libs/paloSantoConfig.class.php');
require_once('/var/www/html/libs/paloSantoDB.class.php');

// Configuración de base de datos
$arrConfig = array(
    'host' => 'localhost',
    'user' => 'root',
    'password' => 'BlackBopys',
    'database' => 'asterisk'
);

$db = new paloDB($arrConfig);

// Leer variables de Asterisk
$stdin = fopen('php://stdin', 'r');
$stdout = fopen('php://stdout', 'w');

$agi = array();
while (!feof($stdin)) {
    $line = trim(fgets($stdin));
    if (empty($line)) break;
    
    $parts = explode(':', $line, 2);
    if (count($parts) == 2) {
        $agi[trim($parts[0])] = trim($parts[1]);
    }
}

$caller_id = $agi['agi_callerid'] ?? '';
$extension = preg_replace('/[^0-9]/', '', $caller_id);

// Obtener configuración
$config = array();
$query = "SELECT config_key, config_value FROM esp32_config";
$result = $db->fetchTable($query);
foreach ($result as $row) {
    $config[$row[0]] = $row[1];
}

// Verificar si la extensión está autorizada y en horario permitido
$context = $agi['agi_context'] ?? '';
$is_pstn = (strpos($context, 'from-pstn') !== false || strpos($context, 'from-trunk') !== false);

if ($is_pstn) {
    $query = "SELECT hora_inicio, hora_fin, dias_semana FROM esp32_authorized_extensions WHERE extension = ? AND activo = 1 AND allow_pstn = 1";
} else {
    $query = "SELECT hora_inicio, hora_fin, dias_semana FROM esp32_authorized_extensions WHERE extension = ? AND activo = 1";
}
$ext_data = $db->getFirstRowQuery($query, true, array($extension));

if (!$ext_data) {
    logAccess($db, $extension, $config['esp32_ip'], 'ON', 'UNAUTHORIZED');
    fputs($stdout, "VERBOSE \"Extension $extension not authorized\" 1\n");
    exit(1);
}

// Verificar horario
$current_time = date('H:i:s');
$current_day = date('w'); // 0=domingo, 1=lunes, etc.
$dias_permitidos = str_split($ext_data[2]);

if (!$dias_permitidos[$current_day] || $current_time < $ext_data[0] || $current_time > $ext_data[1]) {
    logAccess($db, $extension, $config['esp32_ip'], 'ON', 'OUT_OF_SCHEDULE');
    fputs($stdout, "VERBOSE \"Extension $extension outside allowed schedule\" 1\n");
    exit(1);
}

$authorized = true;



// Construir URL
$url = "http://{$config['esp32_ip']}:{$config['esp32_port']}/on";
if (!empty($config['token'])) {
    $url .= "?token=" . urlencode($config['token']);
}

$resultado = 'ERROR';
if ($config['simulation_mode'] == '1') {
    $resultado = 'SIMULATED_OK';
    fputs($stdout, "VERBOSE \"Simulation mode: relay activation simulated\" 1\n");
} else {
    // Ejecutar comando HTTP
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, intval($config['timeout']));
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($response !== false && $http_code == 200) {
        $resultado = 'OK';
        fputs($stdout, "VERBOSE \"Relay activated successfully\" 1\n");
    } else {
        fputs($stdout, "VERBOSE \"Failed to activate relay: HTTP $http_code\" 1\n");
    }
}

// Registrar en log
logAccess($db, $extension, $config['esp32_ip'], 'ON', $resultado);

function logAccess($db, $extension, $ip, $action, $result) {
    $query = "INSERT INTO esp32_access_log (fecha_hora, extension_llamante, ip_esp32, accion, resultado) VALUES (NOW(), ?, ?, ?, ?)";
    $db->genQuery($query, array($extension, $ip, $action, $result));
}
?>