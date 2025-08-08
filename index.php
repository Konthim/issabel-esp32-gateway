<?php
function _moduleContent(&$smarty, $module_name)
{
    $message = '';
    $mysqli = new mysqli('localhost', 'root', 'BlackBopys', 'asterisk');
    
    if ($mysqli->connect_error) {
        return '<h1>ESP32 Relay Control</h1><p>Error conexión: ' . $mysqli->connect_error . '</p>';
    }
    
    // Procesar formularios
    if (isset($_POST['action'])) {
        switch($_POST['action']) {
            case 'add_extension':
                $ext = $_POST['extension'];
                $desc = $_POST['descripcion'];
                $pstn = isset($_POST['allow_pstn']) ? 1 : 0;
                $hora_inicio = $_POST['hora_inicio'] ?: '00:00:00';
                $hora_fin = $_POST['hora_fin'] ?: '23:59:59';
                $dias = '';
                for($i = 0; $i < 7; $i++) {
                    $dias .= isset($_POST['dia'.$i]) ? '1' : '0';
                }
                
                $stmt = $mysqli->prepare("INSERT INTO esp32_authorized_extensions (extension, descripcion, allow_pstn, hora_inicio, hora_fin, dias_semana) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssisss", $ext, $desc, $pstn, $hora_inicio, $hora_fin, $dias);
                
                if ($stmt->execute()) {
                    $message = "Extensión agregada correctamente";
                } else {
                    $message = "Error: " . $stmt->error;
                }
                $stmt->close();
                break;
                
            case 'save_config':
                $configs = array(
                    array('esp32_ip', $_POST['esp32_ip']),
                    array('esp32_port', $_POST['esp32_port']),
                    array('target_extension', $_POST['target_extension']),
                    array('token', $_POST['token']),
                    array('timeout', $_POST['timeout']),
                    array('simulation_mode', isset($_POST['simulation_mode']) ? '1' : '0')
                );
                
                $success = true;
                foreach($configs as $config) {
                    $stmt = $mysqli->prepare("INSERT INTO esp32_config (config_key, config_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE config_value = ?");
                    $stmt->bind_param("sss", $config[0], $config[1], $config[1]);
                    if (!$stmt->execute()) {
                        $success = false;
                        break;
                    }
                    $stmt->close();
                }
                
                $message = $success ? "Configuración guardada correctamente" : "Error al guardar configuración";
                break;
        }
    }
    
    // Obtener datos
    $extensions = array();
    $result = $mysqli->query("SELECT * FROM esp32_authorized_extensions ORDER BY extension");
    if ($result) {
        while ($row = $result->fetch_array()) {
            $extensions[] = $row;
        }
    }
    
    $logs = array();
    $result = $mysqli->query("SELECT * FROM esp32_access_log ORDER BY fecha_hora DESC LIMIT 20");
    if ($result) {
        while ($row = $result->fetch_array()) {
            $logs[] = $row;
        }
    }
    
    // Obtener configuración
    $config = array();
    $result = $mysqli->query("SELECT config_key, config_value FROM esp32_config");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $config[$row['config_key']] = $row['config_value'];
        }
    }
    
    $mysqli->close();
    
    $content = '
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .nav-tabs .nav-link { border-radius: 8px 8px 0 0; margin-right: 5px; }
        .nav-tabs .nav-link.active { background: #0d6efd; color: white; border-color: #0d6efd; }
        .card { border: none; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .table th { background: #f8f9fa; }
        .badge-success { background: #198754; }
        .badge-danger { background: #dc3545; }
        .badge-warning { background: #fd7e14; }
        .badge-info { background: #0dcaf0; }
    </style>
    
    <div class="container-fluid">
        <h1><i class="fas fa-microchip me-2"></i>Control Relé ESP32</h1>';
        
    if ($message) {
        $content .= '<div class="alert alert-success alert-dismissible fade show">
            ' . $message . '
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>';
    }
    
    $content .= '
        <ul class="nav nav-tabs mb-4">
            <li class="nav-item">
                <a class="nav-link active" href="#extensions" onclick="showTab(\'extensions\')">
                    <i class="fas fa-users me-2"></i>Extensiones Autorizadas
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#config" onclick="showTab(\'config\')">
                    <i class="fas fa-cog me-2"></i>Configuración
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#logs" onclick="showTab(\'logs\')">
                    <i class="fas fa-history me-2"></i>Log de Auditoría
                </a>
            </li>
        </ul>
        
        <!-- Tab Extensiones -->
        <div id="extensions" class="tab-content">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-plus me-2"></i>Agregar Nueva Extensión</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="action" value="add_extension">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Extensión:</label>
                                <input type="text" name="extension" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Descripción:</label>
                                <input type="text" name="descripcion" class="form-control">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Hora Inicio:</label>
                                <input type="time" name="hora_inicio" class="form-control" value="00:00">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Hora Fin:</label>
                                <input type="time" name="hora_fin" class="form-control" value="23:59">
                            </div>
                        </div>
                        <div class="row g-3 mt-2">
                            <div class="col-md-12">
                                <label class="form-label">Días de la semana:</label>
                                <div class="d-flex gap-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="dia0" id="dia0">
                                        <label class="form-check-label" for="dia0">Dom</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="dia1" id="dia1" checked>
                                        <label class="form-check-label" for="dia1">Lun</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="dia2" id="dia2" checked>
                                        <label class="form-check-label" for="dia2">Mar</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="dia3" id="dia3" checked>
                                        <label class="form-check-label" for="dia3">Mié</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="dia4" id="dia4" checked>
                                        <label class="form-check-label" for="dia4">Jue</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="dia5" id="dia5" checked>
                                        <label class="form-check-label" for="dia5">Vie</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="dia6" id="dia6">
                                        <label class="form-check-label" for="dia6">Sáb</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row g-3 mt-2">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="allow_pstn" id="pstn">
                                    <label class="form-check-label" for="pstn">Permitir PSTN</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-plus me-2"></i>Agregar Extensión
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Extensiones Configuradas</h5>
                </div>
                <div class="card-body">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Extensión</th>
                                <th>Descripción</th>
                                <th>Horario</th>
                                <th>Días</th>
                                <th>PSTN</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>';
                        
    if ($extensions) {
        foreach($extensions as $ext) {
            $dias_str = '';
            $dias_array = str_split($ext[7] ?: '1111100');
            $dias_names = array('D', 'L', 'M', 'X', 'J', 'V', 'S');
            for($i = 0; $i < 7; $i++) {
                $dias_str .= ($dias_array[$i] == '1') ? $dias_names[$i] : '-';
            }
            
            $content .= '<tr>
                <td>' . htmlspecialchars($ext[1]) . '</td>
                <td>' . htmlspecialchars($ext[2]) . '</td>
                <td>' . substr($ext[5], 0, 5) . ' - ' . substr($ext[6], 0, 5) . '</td>
                <td><small>' . $dias_str . '</small></td>
                <td>' . ($ext[4] ? '<span class="badge badge-info">Sí</span>' : '<span class="badge badge-secondary">No</span>') . '</td>
                <td>' . ($ext[3] ? '<span class="badge badge-success">Activo</span>' : '<span class="badge badge-secondary">Inactivo</span>') . '</td>
            </tr>';
        }
    } else {
        $content .= '<tr><td colspan="6" class="text-center">No hay extensiones configuradas</td></tr>';
    }
    
    $content .= '
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Tab Configuración -->
        <div id="config" class="tab-content" style="display:none;">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-cog me-2"></i>Configuración del Sistema</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="action" value="save_config">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">IP del ESP32:</label>
                                <input type="text" name="esp32_ip" class="form-control" value="' . ($config['esp32_ip'] ?? '192.168.1.100') . '" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Puerto:</label>
                                <input type="number" name="esp32_port" class="form-control" value="' . ($config['esp32_port'] ?? '80') . '" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Extensión objetivo:</label>
                                <input type="text" name="target_extension" class="form-control" value="' . ($config['target_extension'] ?? '8000') . '" required>
                            </div>
                        </div>
                        <div class="row g-3 mt-3">
                            <div class="col-md-6">
                                <label class="form-label">Token (opcional):</label>
                                <input type="password" name="token" class="form-control" value="' . ($config['token'] ?? '') . '">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Timeout (segundos):</label>
                                <input type="number" name="timeout" class="form-control" value="' . ($config['timeout'] ?? '5') . '" required>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check mt-4">
                                    <input class="form-check-input" type="checkbox" name="simulation_mode" id="simMode" ' . (($config['simulation_mode'] ?? '1') == '1' ? 'checked' : '') . '>
                                    <label class="form-check-label" for="simMode">Modo simulación</label>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Guardar Configuración
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Tab Logs -->
        <div id="logs" class="tab-content" style="display:none;">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-history me-2"></i>Log de Auditoría</h5>
                </div>
                <div class="card-body">
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
                        
    if ($logs) {
        foreach($logs as $log) {
            $badge_class = '';
            switch($log[5]) {
                case 'OK': $badge_class = 'badge-success'; break;
                case 'ERROR': $badge_class = 'badge-danger'; break;
                case 'UNAUTHORIZED': $badge_class = 'badge-warning'; break;
                case 'SIMULATED_OK': $badge_class = 'badge-info'; break;
                default: $badge_class = 'badge-secondary';
            }
            
            $content .= '<tr>
                <td>' . $log[1] . '</td>
                <td>' . $log[2] . '</td>
                <td>' . $log[3] . '</td>
                <td>' . $log[4] . '</td>
                <td><span class="badge ' . $badge_class . '">' . $log[5] . '</span></td>
            </tr>';
        }
    } else {
        $content .= '<tr><td colspan="5" class="text-center">No hay registros de actividad</td></tr>';
    }
    
    $content .= '
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function showTab(tabName) {
            document.querySelectorAll(".tab-content").forEach(tab => {
                tab.style.display = "none";
            });
            
            document.querySelectorAll(".nav-link").forEach(link => {
                link.classList.remove("active");
            });
            
            document.getElementById(tabName).style.display = "block";
            event.target.classList.add("active");
        }
    </script>';
    
    return $content;
}
?>
