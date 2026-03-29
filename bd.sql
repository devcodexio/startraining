-- =========================================
-- DATABASE: startraining
-- =========================================

-- 1. TABLA CONFIGURACIÓN GLOBAL
CREATE TABLE IF NOT EXISTS configuracion (
    clave VARCHAR(50) PRIMARY KEY,
    valor TEXT
);

INSERT INTO configuracion (clave, valor) VALUES 
('nombre_sitio', 'StarTraining Pro'),
('modo_mantenimiento', 'off'),
('logo_sitio', '/assets/img/logo.png')
ON CONFLICT (clave) DO NOTHING;

-- 2. TABLA EMPRESAS
CREATE TABLE IF NOT EXISTS empresas (
    id SERIAL PRIMARY KEY,
    nombre_comercial VARCHAR(150) NOT NULL,
    ruc VARCHAR(11) UNIQUE NOT NULL,
    sector VARCHAR(100),
    correo_contacto VARCHAR(150) UNIQUE NOT NULL,
    telefono VARCHAR(20),
    direccion TEXT,

    -- LOGIN EMPRESA
    password_hash VARCHAR(255) NOT NULL,

    -- FOTO PERFIL
    foto_perfil TEXT, -- URL o ruta

    estado VARCHAR(20) DEFAULT 'pendiente', -- pendiente, activo, rechazado, bloqueado
    es_top BOOLEAN DEFAULT FALSE,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. TABLA ADMINISTRADORES
CREATE TABLE IF NOT EXISTS administradores (
    id SERIAL PRIMARY KEY,
    usuario VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    nombre VARCHAR(100),
    foto_perfil TEXT,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 4. TABLA CARRERAS (Para filtros)
CREATE TABLE IF NOT EXISTS carreras (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(100) UNIQUE NOT NULL
);

INSERT INTO carreras (nombre) VALUES 
('Ingeniería de Sistemas'), 
('Diseño Gráfico'), 
('Marketing Digital'), 
('Contabilidad')
ON CONFLICT (nombre) DO NOTHING;

-- 5. TABLA VACANTES
CREATE TABLE IF NOT EXISTS vacantes (
    id SERIAL PRIMARY KEY,
    empresa_id INTEGER NOT NULL,
    carrera_id INTEGER,
    
    titulo_puesto VARCHAR(150) NOT NULL,
    descripcion_puesto TEXT NOT NULL,
    requisitos_raw TEXT NOT NULL,
    modalidad VARCHAR(50), -- Presencial, Remoto, Híbrido
    ubicacion VARCHAR(100),
    fecha_limite DATE,
    estado VARCHAR(20) DEFAULT 'abierta', -- abierta, cerrada
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_empresa FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE,
    CONSTRAINT fk_carrera FOREIGN KEY (carrera_id) REFERENCES carreras(id) ON DELETE SET NULL
);

-- 6. TABLA POSTULACIONES
CREATE TABLE IF NOT EXISTS postulaciones (
    id SERIAL PRIMARY KEY,
    vacante_id INTEGER NOT NULL,

    dni VARCHAR(15) NOT NULL,
    nombre_completo VARCHAR(200) NOT NULL,
    correo_estudiante VARCHAR(150) NOT NULL, -- RESTRICCION @edu.pe en PHP
    celular VARCHAR(20) NOT NULL,
    url_cv_pdf TEXT,

    match_porcentaje DECIMAL(5,2) DEFAULT 0.00,
    ia_analisis_descripcion TEXT,
    estado_postulacion VARCHAR(20) DEFAULT 'en_espera',
    fecha_postulacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_vacante FOREIGN KEY (vacante_id) REFERENCES vacantes(id) ON DELETE CASCADE
);

-- DUMMY DATA PARA PRUEBAS
INSERT INTO administradores (usuario, password_hash, nombre)
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrador Global')
ON CONFLICT (usuario) DO NOTHING;

--auditoria

CREATE TABLE auditoria (
    id SERIAL PRIMARY KEY,
    tabla VARCHAR(100),
    accion VARCHAR(10), -- INSERT, UPDATE, DELETE
    registro_id TEXT,
    datos_antes JSONB,
    datos_despues JSONB,
    usuario TEXT,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE OR REPLACE FUNCTION fn_auditoria()
RETURNS TRIGGER AS $$
BEGIN

    IF (TG_OP = 'INSERT') THEN
        INSERT INTO auditoria (tabla, accion, registro_id, datos_despues)
        VALUES (TG_TABLE_NAME, 'INSERT', NEW.id::TEXT, to_jsonb(NEW));
        RETURN NEW;

    ELSIF (TG_OP = 'UPDATE') THEN
        INSERT INTO auditoria (tabla, accion, registro_id, datos_antes, datos_despues)
        VALUES (TG_TABLE_NAME, 'UPDATE', NEW.id::TEXT, to_jsonb(OLD), to_jsonb(NEW));
        RETURN NEW;

    ELSIF (TG_OP = 'DELETE') THEN
        INSERT INTO auditoria (tabla, accion, registro_id, datos_antes)
        VALUES (TG_TABLE_NAME, 'DELETE', OLD.id::TEXT, to_jsonb(OLD));
        RETURN OLD;

    END IF;

END;
$$ LANGUAGE plpgsql;


CREATE TRIGGER trg_empresas
AFTER INSERT OR UPDATE OR DELETE ON empresas
FOR EACH ROW EXECUTE FUNCTION fn_auditoria();
CREATE TRIGGER trg_administradores
AFTER INSERT OR UPDATE OR DELETE ON administradores
FOR EACH ROW EXECUTE FUNCTION fn_auditoria();
CREATE TRIGGER trg_carreras
AFTER INSERT OR UPDATE OR DELETE ON carreras
FOR EACH ROW EXECUTE FUNCTION fn_auditoria();
CREATE TRIGGER trg_vacantes
AFTER INSERT OR UPDATE OR DELETE ON vacantes
FOR EACH ROW EXECUTE FUNCTION fn_auditoria();
CREATE TRIGGER trg_postulaciones
AFTER INSERT OR UPDATE OR DELETE ON postulaciones
FOR EACH ROW EXECUTE FUNCTION fn_auditoria();
CREATE TRIGGER trg_configuracion
AFTER INSERT OR UPDATE OR DELETE ON configuracion
FOR EACH ROW EXECUTE FUNCTION fn_auditoria();
