<?php
// Servidor local para probar la interfaz del módulo ESP32
$port = 5000;

// Datos simulados
$logs = [
    ['1', '2024-01-15 10:30:15', '1001', '192.168.1.123', 'ON', 'OK'],
    ['2', '2024-01-15 11:45:22', '1002', '192.168.1.123', 'ON', 'ERROR'],
    ['3', '2024-01-15 14:20:10', '1001', '192.168.1.123', 'ON', 'SIMULATED_OK'],
    ['4', '2024-01-15 16:15:33', '1003', '192.168.1.123', 'ON', 'UNAUTHORIZED']
];

$extensions = [
    ['1', '1001', 'Extensión Admin', '1'],
    ['2', '1002', 'Extensión Usuario 1', '1'],
    ['3', '1003', 'Extensión Usuario 2', '0']
];

$config = [
    'esp32_ip' => '192.168.1.123',
    'esp32_port' => '80',
    'target_extension' => '8000',
    'token' => 'mi_token_secreto',
    'simulation_mode' => '1',
    'timeout' => '5'
];

function getParameter($name) {
    return $_GET[$name] ?? $_POST[$name] ?? '';
}

function showLogs($logs) {
    $date_from = getParameter('date_from');
    $date_to = getParameter('date_to');
    $extension = getParameter('extension_filter');
    
    $content = '
    <div class="card">
        <div class="card-header">
            <h3>Control de Relé ESP32 - Log de Auditoría</h3>
        </div>
        <div class="card-body">
            <form method="GET" class="mb-3">
                <input type="hidden" name="nav" value="logs">
                <div class="row">
                    <div class="col-md-3">
                        <label>Desde:</label>
                        <input type="date" name="date_from" class="form-control" value="' . htmlspecialchars($date_from) . '">
                    </div>
                    <div class="col-md-3">
                        <label>Hasta:</label>
                        <input type="date" name="date_to" class="form-control" value="' . htmlspecialchars($date_to) . '">
                    </div>
                    <div class="col-md-3">
                        <label>Extensión:</label>
                        <input type="text" name="extension_filter" class="form-control" value="' . htmlspecialchars($extension) . '">
                    </div>
                    <div class="col-md-3">
                        <label>&nbsp;</label><br>
                        <button type="submit" class="btn btn-primary">Filtrar</button>
                        <a href="?nav=logs&action=export" class="btn btn-success">Exportar CSV</a>
                    </div>
                </div>
            </form>
            
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Fecha/Hora</th>
                        <th>Extensión</th>
                        <th>IP ESP32</th>
                        <th>Acción</th>
                        <th>Resultado</th>
                    </tr>
                </thead>
                <tbody>';
    
    foreach ($logs as $log) {
        $status_class = $log[5] == 'OK' ? 'success' : ($log[5] == 'SIMULATED_OK' ? 'info' : ($log[5] == 'UNAUTHORIZED' ? 'warning' : 'danger'));
        $content .= "
                    <tr>
                        <td>{$log[1]}</td>
                        <td>{$log[2]}</td>
                        <td>{$log[3]}</td>
                        <td>{$log[4]}</td>
                        <td><span class=\"badge badge-{$status_class}\">{$log[5]}</span></td>
                    </tr>";
    }
    
    $content .= '
                </tbody>
            </table>
        </div>
    </div>';
    
    return $content;
}

function showConfig($config) {
    $content = '
    <div class="card">
        <div class="card-header">
            <h3>Configuración del Módulo</h3>
        </div>
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="nav" value="config">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>IP del ESP32:</label>
                            <input type="text" name="esp32_ip" class="form-control" value="' . htmlspecialchars($config['esp32_ip']) . '" required>
                        </div>
                        <div class="form-group">
                            <label>Puerto:</label>
                            <input type="number" name="esp32_port" class="form-control" value="' . htmlspecialchars($config['esp32_port']) . '" required>
                        </div>
                        <div class="form-group">
                            <label>Extensión objetivo:</label>
                            <input type="text" name="target_extension" class="form-control" value="' . htmlspecialchars($config['target_extension']) . '" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Token (opcional):</label>
                            <input type="text" name="token" class="form-control" value="' . htmlspecialchars($config['token']) . '">
                        </div>
                        <div class="form-group">
                            <label>Timeout (segundos):</label>
                            <input type="number" name="timeout" class="form-control" value="' . htmlspecialchars($config['timeout']) . '" required>
                        </div>
                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="simulation_mode" value="1" ' . ($config['simulation_mode'] ? 'checked' : '') . '>
                                Modo simulación
                            </label>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Guardar Configuración</button>
            </form>
        </div>
    </div>';
    
    return $content;
}

function showExtensions($extensions) {
    $content = '
    <div class="card">
        <div class="card-header">
            <h3>Extensiones Autorizadas</h3>
        </div>
        <div class="card-body">
            <form method="POST" class="mb-3">
                <input type="hidden" name="nav" value="extensions">
                <input type="hidden" name="action" value="add">
                <div class="row">
                    <div class="col-md-4">
                        <input type="text" name="extension" class="form-control" placeholder="Extensión" required>
                    </div>
                    <div class="col-md-6">
                        <input type="text" name="descripcion" class="form-control" placeholder="Descripción">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-success">Agregar</button>
                    </div>
                </div>
            </form>
            
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Extensión</th>
                        <th>Descripción</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>';
    
    foreach ($extensions as $ext) {
        $status = $ext[3] ? '<span class="badge badge-success">Activo</span>' : '<span class="badge badge-secondary">Inactivo</span>';
        $content .= "
                    <tr>
                        <td>{$ext[1]}</td>
                        <td>{$ext[2]}</td>
                        <td>$status</td>
                        <td>
                            <a href=\"?nav=extensions&action=delete&id={$ext[0]}\" 
                               class=\"btn btn-sm btn-danger\" onclick=\"return confirm('¿Eliminar esta extensión?')\">Eliminar</a>
                        </td>
                    </tr>";
    }
    
    $content .= '
                </tbody>
            </table>
        </div>
    </div>';
    
    return $content;
}

// Procesar solicitud
$nav = getParameter('nav') ?: 'logs';

switch($nav) {
    case 'config':
        $content = showConfig($config);
        break;
    case 'extensions':
        $content = showExtensions($extensions);
        break;
    case 'logs':
    default:
        $content = showLogs($logs);
        break;
}

// HTML completo
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Control de Relé ESP32 - Demo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .badge-success { background-color: #28a745; }
        .badge-danger { background-color: #dc3545; }
        .badge-warning { background-color: #ffc107; color: #212529; }
        .badge-info { background-color: #17a2b8; }
        .badge-secondary { background-color: #6c757d; }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark bg-dark">
        <div class="container-fluid">
            <span class="navbar-brand">Issabel - Control de Relé ESP32</span>
        </div>
    </nav>
    
    <div class="container-fluid mt-3">
        <div class="row">
            <div class="col-md-12">
                <ul class="nav nav-tabs mb-3">
                    <li class="nav-item">
                        <a class="nav-link <?= $nav == 'logs' ? 'active' : '' ?>" href="?nav=logs">Log de Auditoría</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $nav == 'extensions' ? 'active' : '' ?>" href="?nav=extensions">Extensiones Autorizadas</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $nav == 'config' ? 'active' : '' ?>" href="?nav=config">Configuración</a>
                    </li>
                </ul>
                
                <?= $content ?>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>