<?php
require_once "libs/paloSantoGrid.class.php";
require_once "libs/paloSantoForm.class.php";
require_once "libs/paloSantoDB.class.php";
require_once "libs/paloSantoConfig.class.php";

function _moduleContent(&$smarty, $module_name) {
    $db = new paloDB("mysql://root:eLaStIx.2oo7@localhost/asterisk");
    
    $action = getParameter('action');
    $nav = getParameter('nav');
    
    switch($nav) {
        case 'config':
            return showConfig($smarty, $db);
        case 'extensions':
            return showExtensions($smarty, $db);
        case 'logs':
        default:
            return showLogs($smarty, $db, $action);
    }
}

function showLogs($smarty, $db, $action) {
    $content = '';
    
    // Exportar CSV
    if ($action == 'export') {
        exportCSV($db);
        return;
    }
    
    // Filtros
    $date_from = getParameter('date_from');
    $date_to = getParameter('date_to');
    $extension = getParameter('extension_filter');
    
    $where = array();
    $params = array();
    
    if ($date_from) {
        $where[] = "fecha_hora >= ?";
        $params[] = $date_from . ' 00:00:00';
    }
    if ($date_to) {
        $where[] = "fecha_hora <= ?";
        $params[] = $date_to . ' 23:59:59';
    }
    if ($extension) {
        $where[] = "extension_llamante = ?";
        $params[] = $extension;
    }
    
    $whereClause = empty($where) ? '' : 'WHERE ' . implode(' AND ', $where);
    
    $query = "SELECT * FROM esp32_access_log $whereClause ORDER BY fecha_hora DESC LIMIT 100";
    $logs = $db->fetchTable($query, true, $params);
    
    $content .= '
    <div class="card">
        <div class="card-header">
            <h3>Control de Relé ESP32 - Log de Auditoría</h3>
        </div>
        <div class="card-body">
            <form method="GET" class="mb-3">
                <input type="hidden" name="menu" value="' . getParameter('menu') . '">
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
                        <a href="?menu=' . getParameter('menu') . '&action=export" class="btn btn-success">Exportar CSV</a>
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
        $status_class = $log[5] == 'OK' ? 'success' : ($log[5] == 'SIMULATED_OK' ? 'info' : 'danger');
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

function showConfig($smarty, $db) {
    if ($_POST) {
        foreach ($_POST as $key => $value) {
            if (strpos($key, 'config_') === 0) {
                $config_key = substr($key, 7);
                $db->genQuery("UPDATE esp32_config SET config_value = ? WHERE config_key = ?", array($value, $config_key));
            }
        }
        header("Location: ?menu=" . getParameter('menu') . "&nav=config&msg=saved");
        exit;
    }
    
    $config = array();
    $result = $db->fetchTable("SELECT config_key, config_value FROM esp32_config");
    foreach ($result as $row) {
        $config[$row[0]] = $row[1];
    }
    
    $content = '
    <div class="card">
        <div class="card-header">
            <h3>Configuración del Módulo</h3>
        </div>
        <div class="card-body">
            <form method="POST">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>IP del ESP32:</label>
                            <input type="text" name="config_esp32_ip" class="form-control" value="' . htmlspecialchars($config['esp32_ip']) . '" required>
                        </div>
                        <div class="form-group">
                            <label>Puerto:</label>
                            <input type="number" name="config_esp32_port" class="form-control" value="' . htmlspecialchars($config['esp32_port']) . '" required>
                        </div>
                        <div class="form-group">
                            <label>Extensión objetivo:</label>
                            <input type="text" name="config_target_extension" class="form-control" value="' . htmlspecialchars($config['target_extension']) . '" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Token (opcional):</label>
                            <input type="text" name="config_token" class="form-control" value="' . htmlspecialchars($config['token']) . '">
                        </div>
                        <div class="form-group">
                            <label>Timeout (segundos):</label>
                            <input type="number" name="config_timeout" class="form-control" value="' . htmlspecialchars($config['timeout']) . '" required>
                        </div>
                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="config_simulation_mode" value="1" ' . ($config['simulation_mode'] ? 'checked' : '') . '>
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

function showExtensions($smarty, $db) {
    $action = getParameter('action');
    
    if ($action == 'add' && $_POST) {
        $ext = getParameter('extension');
        $desc = getParameter('descripcion');
        $pstn = getParameter('allow_pstn') ? 1 : 0;
        $hora_inicio = getParameter('hora_inicio') ?: '00:00:00';
        $hora_fin = getParameter('hora_fin') ?: '23:59:59';
        $dias = '';
        for($i=0; $i<7; $i++) {
            $dias .= getParameter('dia_'.$i) ? '1' : '0';
        }
        $db->genQuery("INSERT INTO esp32_authorized_extensions (extension, descripcion, allow_pstn, hora_inicio, hora_fin, dias_semana) VALUES (?, ?, ?, ?, ?, ?)", array($ext, $desc, $pstn, $hora_inicio, $hora_fin, $dias));
        header("Location: ?menu=" . getParameter('menu') . "&nav=extensions");
        exit;
    }
    
    if ($action == 'edit' && $_POST) {
        $id = getParameter('id');
        $ext = getParameter('extension');
        $desc = getParameter('descripcion');
        $pstn = getParameter('allow_pstn') ? 1 : 0;
        $activo = getParameter('activo') ? 1 : 0;
        $hora_inicio = getParameter('hora_inicio') ?: '00:00:00';
        $hora_fin = getParameter('hora_fin') ?: '23:59:59';
        $dias = '';
        for($i=0; $i<7; $i++) {
            $dias .= getParameter('dia_'.$i) ? '1' : '0';
        }
        $db->genQuery("UPDATE esp32_authorized_extensions SET extension=?, descripcion=?, allow_pstn=?, activo=?, hora_inicio=?, hora_fin=?, dias_semana=? WHERE id=?", array($ext, $desc, $pstn, $activo, $hora_inicio, $hora_fin, $dias, $id));
        header("Location: ?menu=" . getParameter('menu') . "&nav=extensions");
        exit;
    }
    
    if ($action == 'delete') {
        $id = getParameter('id');
        $db->genQuery("DELETE FROM esp32_authorized_extensions WHERE id = ?", array($id));
        header("Location: ?menu=" . getParameter('menu') . "&nav=extensions");
        exit;
    }
    
    $extensions = $db->fetchTable("SELECT * FROM esp32_authorized_extensions ORDER BY extension");
    
    $edit_id = getParameter('edit');
    $edit_data = null;
    if ($edit_id) {
        $edit_result = $db->fetchTable("SELECT * FROM esp32_authorized_extensions WHERE id = ?", true, array($edit_id));
        $edit_data = $edit_result ? $edit_result[0] : null;
    }
    
    $content = '
    <div class="card">
        <div class="card-header">
            <h3>Extensiones Autorizadas</h3>
        </div>
        <div class="card-body">
            <form method="POST" class="mb-3">
                <input type="hidden" name="action" value="' . ($edit_data ? 'edit' : 'add') . '">
                ' . ($edit_data ? '<input type="hidden" name="id" value="' . $edit_data[0] . '">' : '') . '
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label>Extensión:</label>
                        <input type="text" name="extension" class="form-control" value="' . ($edit_data ? htmlspecialchars($edit_data[1]) : '') . '" required>
                    </div>
                    <div class="col-md-4">
                        <label>Descripción:</label>
                        <input type="text" name="descripcion" class="form-control" value="' . ($edit_data ? htmlspecialchars($edit_data[2]) : '') . '">
                    </div>
                    <div class="col-md-2">
                        <label>Hora Inicio:</label>
                        <input type="time" name="hora_inicio" class="form-control" value="' . ($edit_data && isset($edit_data[5]) ? $edit_data[5] : '00:00') . '">
                    </div>
                    <div class="col-md-2">
                        <label>Hora Fin:</label>
                        <input type="time" name="hora_fin" class="form-control" value="' . ($edit_data && isset($edit_data[6]) ? $edit_data[6] : '23:59') . '">
                    </div>
                    <div class="col-md-1">
                        <label>
                            <input type="checkbox" name="allow_pstn" value="1" ' . ($edit_data && isset($edit_data[4]) && $edit_data[4] ? 'checked' : '') . '> PSTN
                        </label>
                        <label>
                            <input type="checkbox" name="activo" value="1" ' . ($edit_data ? ($edit_data[3] ? 'checked' : '') : 'checked') . '> Activo
                        </label>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-10">
                        <label>Días de la semana:</label><br>
                        ' . generateDaysCheckboxes($edit_data) . '
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-' . ($edit_data ? 'primary' : 'success') . '">' . ($edit_data ? 'Actualizar' : 'Agregar') . '</button>
                        ' . ($edit_data ? '<a href="?menu=' . getParameter('menu') . '&nav=extensions" class="btn btn-secondary btn-sm">Cancelar</a>' : '') . '
                    </div>
                </div>
            </form>
            
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Extensión</th>
                        <th>Descripción</th>
                        <th>Horario</th>
                        <th>Días</th>
                        <th>Estado</th>
                        <th>PSTN</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>';
    
    foreach ($extensions as $ext) {
        $status = $ext[3] ? '<span class="badge badge-success">Activo</span>' : '<span class="badge badge-secondary">Inactivo</span>';
        $pstn_status = isset($ext[4]) && $ext[4] ? '<span class="badge badge-info">Sí</span>' : '<span class="badge badge-secondary">No</span>';
        $horario = (isset($ext[5]) ? substr($ext[5], 0, 5) : '00:00') . ' - ' . (isset($ext[6]) ? substr($ext[6], 0, 5) : '23:59');
        $dias_array = isset($ext[7]) ? str_split($ext[7]) : array('1','1','1','1','1','1','1');
        $dias_nombres = array('D','L','M','X','J','V','S');
        $dias_activos = '';
        for($i=0; $i<7; $i++) {
            $dias_activos .= $dias_array[$i] == '1' ? $dias_nombres[$i] : '-';
        }
        $content .= "
                    <tr>
                        <td>{$ext[1]}</td>
                        <td>{$ext[2]}</td>
                        <td><small>$horario</small></td>
                        <td><small>$dias_activos</small></td>
                        <td>$status</td>
                        <td>$pstn_status</td>
                        <td>
                            <a href=\"?menu=" . getParameter('menu') . "&nav=extensions&edit={$ext[0]}\" 
                               class=\"btn btn-sm btn-primary me-1\">Editar</a>
                            <a href=\"?menu=" . getParameter('menu') . "&nav=extensions&action=delete&id={$ext[0]}\" 
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

function generateDaysCheckboxes($edit_data) {
    $days = array('Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb');
    $dias_semana = $edit_data && isset($edit_data[7]) ? str_split($edit_data[7]) : array('1','1','1','1','1','1','1');
    $html = '';
    for($i=0; $i<7; $i++) {
        $checked = $dias_semana[$i] == '1' ? 'checked' : '';
        $html .= '<label class="me-2"><input type="checkbox" name="dia_'.$i.'" value="1" '.$checked.'> '.$days[$i].'</label>';
    }
    return $html;
}

function exportCSV($db) {
    $logs = $db->fetchTable("SELECT * FROM esp32_access_log ORDER BY fecha_hora DESC");
    
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="esp32_access_log.csv"');
    
    $output = fopen('php://output', 'w');
    fputcsv($output, array('ID', 'Fecha/Hora', 'Extensión', 'IP ESP32', 'Acción', 'Resultado'));
    
    foreach ($logs as $log) {
        fputcsv($output, $log);
    }
    
    fclose($output);
    exit;
}
?>