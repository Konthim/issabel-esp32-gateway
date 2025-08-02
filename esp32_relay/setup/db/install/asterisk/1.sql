CREATE TABLE IF NOT EXISTS esp32_access_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fecha_hora DATETIME NOT NULL,
    extension_llamante VARCHAR(20) NOT NULL,
    ip_esp32 VARCHAR(45) NOT NULL,
    accion VARCHAR(10) NOT NULL,
    resultado VARCHAR(20) NOT NULL,
    INDEX idx_fecha (fecha_hora),
    INDEX idx_extension (extension_llamante)
);

CREATE TABLE IF NOT EXISTS esp32_config (
    id INT AUTO_INCREMENT PRIMARY KEY,
    config_key VARCHAR(50) UNIQUE NOT NULL,
    config_value TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS esp32_authorized_extensions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    extension VARCHAR(20) UNIQUE NOT NULL,
    descripcion VARCHAR(100),
    activo TINYINT(1) DEFAULT 1,
    allow_pstn TINYINT(1) DEFAULT 0,
    hora_inicio TIME DEFAULT '00:00:00',
    hora_fin TIME DEFAULT '23:59:59',
    dias_semana VARCHAR(7) DEFAULT '1111111'
);

INSERT IGNORE INTO esp32_config (config_key, config_value) VALUES
('esp32_ip', '192.168.1.123'),
('esp32_port', '80'),
('target_extension', '8000'),
('token', ''),
('simulation_mode', '0'),
('timeout', '5');

INSERT IGNORE INTO esp32_authorized_extensions (extension, descripcion, allow_pstn, hora_inicio, hora_fin, dias_semana) VALUES
('1001', 'Extensión Admin', 1, '00:00:00', '23:59:59', '1111111'),
('1002', 'Extensión Usuario 1', 0, '08:00:00', '18:00:00', '1111100'),
('1003', 'Extensión Usuario 2', 0, '09:00:00', '17:00:00', '1111100');