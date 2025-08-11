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
                
            case 'edit_extension':
                $id = $_POST['id'];
                $ext = $_POST['extension'];
                $desc = $_POST['descripcion'];
                $pstn = isset($_POST['allow_pstn']) ? 1 : 0;
                $activo = isset($_POST['activo']) ? 1 : 0;
                $hora_inicio = $_POST['hora_inicio'] ?: '00:00:00';
                $hora_fin = $_POST['hora_fin'] ?: '23:59:59';
                $dias = '';
                for($i = 0; $i < 7; $i++) {
                    $dias .= isset($_POST['dia'.$i]) ? '1' : '0';
                }
                
                $stmt = $mysqli->prepare("UPDATE esp32_authorized_extensions SET extension=?, descripcion=?, allow_pstn=?, activo=?, hora_inicio=?, hora_fin=?, dias_semana=? WHERE id=?");
                $stmt->bind_param("ssiisssi", $ext, $desc, $pstn, $activo, $hora_inicio, $hora_fin, $dias, $id);
                
                if ($stmt->execute()) {
                    $message = "Extensión actualizada correctamente";
                } else {
                    $message = "Error: " . $stmt->error;
                }
                $stmt->close();
                break;
                
            case 'delete_extension':
                $id = $_POST['id'];
                $stmt = $mysqli->prepare("DELETE FROM esp32_authorized_extensions WHERE id = ?");
                $stmt->bind_param("i", $id);
                
                if ($stmt->execute()) {
                    $message = "Extensión eliminada correctamente";
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
                
            case 'block_extension':
                $ext = $_POST['extension'];
                $stmt = $mysqli->prepare("UPDATE esp32_authorized_extensions SET activo = 0 WHERE extension = ?");
                $stmt->bind_param("s", $ext);
                if ($stmt->execute()) {
                    $message = "Extensión $ext bloqueada correctamente";
                } else {
                    $message = "Error al bloquear extensión";
                }
                $stmt->close();
                break;
                
            case 'unblock_extension':
                $ext = $_POST['extension'];
                $stmt = $mysqli->prepare("UPDATE esp32_authorized_extensions SET activo = 1 WHERE extension = ?");
                $stmt->bind_param("s", $ext);
                if ($stmt->execute()) {
                    $message = "Extensión $ext desbloqueada correctamente";
                } else {
                    $message = "Error al desbloquear extensión";
                }
                $stmt->close();
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
    
    // Obtener extensión para editar
    $edit_ext = null;
    if (isset($_GET['edit'])) {
        $edit_id = $_GET['edit'];
        $stmt = $mysqli->prepare("SELECT * FROM esp32_authorized_extensions WHERE id = ?");
        $stmt->bind_param("i", $edit_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $edit_ext = $result->fetch_array();
        $stmt->close();
    }
    
    // Filtros para logs
    $date_from = $_GET['date_from'] ?? '';
    $date_to = $_GET['date_to'] ?? '';
    $extension_filter = $_GET['extension_filter'] ?? '';
    $resultado_filter = $_GET['resultado_filter'] ?? '';
    
    $where_conditions = array();
    $params = array();
    
    if ($date_from) {
        $where_conditions[] = "fecha_hora >= ?";
        $params[] = $date_from . ' 00:00:00';
    }
    if ($date_to) {
        $where_conditions[] = "fecha_hora <= ?";
        $params[] = $date_to . ' 23:59:59';
    }
    if ($extension_filter) {
        $where_conditions[] = "extension_llamante = ?";
        $params[] = $extension_filter;
    }
    if ($resultado_filter) {
        $where_conditions[] = "resultado = ?";
        $params[] = $resultado_filter;
    }
    
    $where_clause = empty($where_conditions) ? '' : 'WHERE ' . implode(' AND ', $where_conditions);
    $query = "SELECT l.*, e.activo FROM esp32_access_log l LEFT JOIN esp32_authorized_extensions e ON l.extension_llamante = e.extension $where_clause ORDER BY l.fecha_hora DESC LIMIT 50";
    
    $logs = array();
    if (empty($params)) {
        $result = $mysqli->query($query);
    } else {
        $stmt = $mysqli->prepare($query);
        $types = str_repeat('s', count($params));
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
    }
    
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
                    <h5 class="mb-0"><i class="fas fa-' . ($edit_ext ? 'edit' : 'plus') . ' me-2"></i>' . ($edit_ext ? 'Editar' : 'Agregar Nueva') . ' Extensión</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="action" value="' . ($edit_ext ? 'edit_extension' : 'add_extension') . '">
                        ' . ($edit_ext ? '<input type="hidden" name="id" value="' . $edit_ext[0] . '">' : '') . '
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Extensión:</label>
                                <input type="text" name="extension" class="form-control" value="' . ($edit_ext ? htmlspecialchars($edit_ext[1]) : '') . '" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Descripción:</label>
                                <input type="text" name="descripcion" class="form-control" value="' . ($edit_ext ? htmlspecialchars($edit_ext[2]) : '') . '">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Hora Inicio:</label>
                                <input type="time" name="hora_inicio" class="form-control" value="' . ($edit_ext ? substr($edit_ext[5], 0, 5) : '00:00') . '">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Hora Fin:</label>
                                <input type="time" name="hora_fin" class="form-control" value="' . ($edit_ext ? substr($edit_ext[6], 0, 5) : '23:59') . '">
                            </div>
                        </div>
                        <div class="row g-3 mt-2">
                            <div class="col-md-12">
                                <label class="form-label">Días de la semana:</label>
                                <div class="d-flex gap-3">';
                                
    $dias_edit = $edit_ext ? str_split($edit_ext[7] ?: '1111100') : array('0','1','1','1','1','1','0');
    $dias_nombres = array('Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb');
    
    for($i = 0; $i < 7; $i++) {
        $checked = $dias_edit[$i] == '1' ? 'checked' : '';
        $content .= '<div class="form-check">
                        <input class="form-check-input" type="checkbox" name="dia'.$i.'" id="dia'.$i.'" '.$checked.'>
                        <label class="form-check-label" for="dia'.$i.'">'.$dias_nombres[$i].'</label>
                    </div>';
    }
    
    $content .= '</div>
                            </div>
                        </div>
                        <div class="row g-3 mt-2">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="allow_pstn" id="pstn" ' . ($edit_ext && $edit_ext[4] ? 'checked' : '') . '>
                                    <label class="form-check-label" for="pstn">Permitir PSTN</label>
                                </div>
                                ' . ($edit_ext ? '<div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" name="activo" id="activo" ' . ($edit_ext[3] ? 'checked' : '') . '>
                                    <label class="form-check-label" for="activo">Activo</label>
                                </div>' : '') . '
                            </div>
                            <div class="col-md-6">
                                <button type="submit" class="btn btn-' . ($edit_ext ? 'primary' : 'success') . '">
                                    <i class="fas fa-' . ($edit_ext ? 'save' : 'plus') . ' me-2"></i>' . ($edit_ext ? 'Actualizar' : 'Agregar') . ' Extensión
                                </button>
                                ' . ($edit_ext ? '<a href="?" class="btn btn-secondary ms-2">Cancelar</a>' : '') . '
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
                                <th>Acciones</th>
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
                <td>
                    <a href="?edit=' . $ext[0] . '" class="btn btn-sm btn-outline-primary me-1">
                        <i class="fas fa-edit"></i>
                    </a>
                    <button onclick="deleteExt(' . $ext[0] . ', \'' . htmlspecialchars($ext[1]) . '\')" class="btn btn-sm btn-outline-danger">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>';
        }
    } else {
        $content .= '<tr><td colspan="7" class="text-center">No hay extensiones configuradas</td></tr>';
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
            <div class="card mb-3">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filtros de Búsqueda</h5>
                </div>
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-2">
                            <label class="form-label">Desde:</label>
                            <input type="date" name="date_from" class="form-control" value="' . htmlspecialchars($date_from) . '">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Hasta:</label>
                            <input type="date" name="date_to" class="form-control" value="' . htmlspecialchars($date_to) . '">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Extensión:</label>
                            <input type="text" name="extension_filter" class="form-control" value="' . htmlspecialchars($extension_filter) . '">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Resultado:</label>
                            <select name="resultado_filter" class="form-control">
                                <option value="">Todos los resultados</option>
                                <option value="APROBADO"' . ($resultado_filter == 'APROBADO' ? ' selected' : '') . '>✅ APROBADO</option>
                                <option value="DENEGADO"' . ($resultado_filter == 'DENEGADO' ? ' selected' : '') . '>❌ DENEGADO</option>
                                <option value="NO AUTORIZADO"' . ($resultado_filter == 'NO AUTORIZADO' ? ' selected' : '') . '>🚫 NO AUTORIZADO</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label><br>
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fas fa-search me-1"></i>Filtrar
                            </button>
                            <a href="?" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i>Limpiar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
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
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>';
                        
    if ($logs) {
        foreach($logs as $log) {
            $badge_class = 'badge-secondary';
            $icon = '';
            switch($log[5]) {
                case 'APROBADO':
                    $badge_class = 'badge-success';
                    $icon = '✅ ';
                    break;
                case 'DENEGADO':
                    $badge_class = 'badge-danger';
                    $icon = '❌ ';
                    break;
                case 'NO AUTORIZADO':
                    $badge_class = 'badge-warning';
                    $icon = '🚫 ';
                    break;
                // Mantener compatibilidad con etiquetas antiguas
                case 'OK': 
                    $badge_class = 'badge-success';
                    $icon = '✅ ';
                    break;
                case 'UNAUTHORIZED': 
                    $badge_class = 'badge-warning';
                    $icon = '🚫 ';
                    break;
                case 'FAILED_ATTEMPTS': 
                    $badge_class = 'badge-danger';
                    $icon = '❌ ';
                    break;
            }
            
            $extension = $log[2];
            $is_active = isset($log[6]) ? $log[6] : null;
            $action_buttons = '';
            
            if ($is_active === '1') {
                $action_buttons = '<button onclick="blockExtension(\'' . $extension . '\')" class="btn btn-sm btn-outline-danger" title="Bloquear extensión">
                    <i class="fas fa-ban"></i> Bloquear
                </button>';
            } elseif ($is_active === '0') {
                $action_buttons = '<button onclick="unblockExtension(\'' . $extension . '\')" class="btn btn-sm btn-outline-success" title="Desbloquear extensión">
                    <i class="fas fa-check"></i> Desbloquear
                </button>';
            } else {
                $action_buttons = '<button onclick="addExtension(\'' . $extension . '\')" class="btn btn-sm btn-outline-primary" title="Agregar extensión">
                    <i class="fas fa-plus"></i> Agregar
                </button>';
            }
            
            $content .= '<tr>
                <td>' . $log[1] . '</td>
                <td>' . $log[2] . '</td>
                <td>' . $log[3] . '</td>
                <td>' . $log[4] . '</td>
                <td><span class="badge ' . $badge_class . '">' . $icon . $log[5] . '</span></td>
                <td>' . $action_buttons . '</td>
            </tr>';
        }
    } else {
        $content .= '<tr><td colspan="6" class="text-center">No hay registros de actividad</td></tr>';
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
        
        function deleteExt(id, extension) {
            if (confirm(\'¿Está seguro de eliminar la extensión \' + extension + \'?\')) {
                const form = document.createElement(\'form\');
                form.method = \'POST\';
                form.innerHTML = \'<input type="hidden" name="action" value="delete_extension"><input type="hidden" name="id" value="\' + id + \'">\';
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        function blockExtension(extension) {
            if (confirm(\'¿Está seguro de bloquear la extensión \' + extension + \'?\')) {
                const form = document.createElement(\'form\');
                form.method = \'POST\';
                form.innerHTML = \'<input type="hidden" name="action" value="block_extension"><input type="hidden" name="extension" value="\' + extension + \'">\';
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        function unblockExtension(extension) {
            if (confirm(\'¿Está seguro de desbloquear la extensión \' + extension + \'?\')) {
                const form = document.createElement(\'form\');
                form.method = \'POST\';
                form.innerHTML = \'<input type="hidden" name="action" value="unblock_extension"><input type="hidden" name="extension" value="\' + extension + \'">\';
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        function addExtension(extension) {
            if (confirm(\'¿Desea agregar la extensión \' + extension + \' a las extensiones autorizadas?\')) {
                window.location.href = \'?extension_to_add=\' + extension;
            }
        }
    </script>';
    
    return $content;
}
?>
