-- =========================================
-- DATABASE: startraining
-- =========================================

-- 1. TABLA CONFIGURACIÓN GLOBAL
CREATE TABLE IF NOT EXISTS configuracion (
    clave VARCHAR(50) PRIMARY KEY,
    valor TEXT,
    creado_por VARCHAR(100),
    actualizado_por VARCHAR(100),
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO configuracion (clave, valor) VALUES 
('nombre_sitio', 'StarTraining Pro'),
('modo_mantenimiento', 'off'),
('logo_sitio', '/assets/img/logo.png'),
('footer_descripcion', 'Plataforma líder en reclutamiento de talento profesional.'),
('email_contacto', 'contacto@startraining.com'),
('telefono_contacto', '+51 987 654 321'),
('facebook_url', '#'),
('instagram_url', '#'),
('linkedin_url', '#'),
('twitter_url', '#')
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
    creado_por VARCHAR(100),
    actualizado_por VARCHAR(100),
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. TABLA ADMINISTRADORES
CREATE TABLE IF NOT EXISTS administradores (
    id SERIAL PRIMARY KEY,
    usuario VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    nombre VARCHAR(100),
    foto_perfil TEXT,
    creado_por VARCHAR(100),
    actualizado_por VARCHAR(100),
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 4. TABLA CARRERAS (Para filtros)
CREATE TABLE IF NOT EXISTS carreras (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(100) UNIQUE NOT NULL,
    creado_por VARCHAR(100),
    actualizado_por VARCHAR(100),
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
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
    creado_por VARCHAR(100),
    actualizado_por VARCHAR(100),
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

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
    notificacion_leida BOOLEAN DEFAULT FALSE,
    creado_por VARCHAR(100),
    actualizado_por VARCHAR(100),
    fecha_postulacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_vacante FOREIGN KEY (vacante_id) REFERENCES vacantes(id) ON DELETE CASCADE,
    CONSTRAINT unique_postulacion_dni UNIQUE (vacante_id, dni)
);

-- =========================================
-- DUMMY DATA PARA PRUEBAS (LIMPIEZA Y REGASTRE)
-- =========================================
-- Limpiamos datos previos y reseteamos contadores para que los IDs coincidan (1, 2, 3, etc)
TRUNCATE TABLE postulaciones, vacantes, carreras, empresas, administradores RESTART IDENTITY CASCADE;

INSERT INTO administradores (usuario, password_hash, nombre)
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrador Global')
ON CONFLICT (usuario) DO NOTHING;

-- 4. INSERT CARRERAS PROFESIONALES (IDs: 1 al 12)
INSERT INTO carreras (nombre) VALUES 
('Ingeniería de Sistemas'),      -- ID 1
('Ingeniería Industrial'),       -- ID 2
('Ingeniería Civil'),            -- ID 3
('Ingeniería de Minas'),         -- ID 4
('Arquitectura y Urbanismo'),    -- ID 5
('Administración de Empresas'),  -- ID 6
('Marketing Digital'),           -- ID 7
('Contabilidad y Finanzas'),     -- ID 8
('Derecho Corporativo'),         -- ID 9
('Psicología Organizacional'),   -- ID 10
('Ciencias de la Comunicación'), -- ID 11
('Economía y Negocios')           -- ID 12
ON CONFLICT (nombre) DO NOTHING;

-- 5. INSERT 5 EMPRESAS REALES (IDs: 1 al 5)
INSERT INTO empresas (nombre_comercial, ruc, sector, correo_contacto, password_hash, estado, es_top, direccion) VALUES 
('BBVA Perú', '20100130204', 'Finanzas', 'talento@bbva.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'activo', true, 'Av. República de Panamá 3055, San Isidro'),
('Alicorp S.A.A.', '20100055237', 'Consumo Masivo', 'rrhh@alicorp.com.pe', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'activo', true, 'Av. Argentina 4793, Carmen de la Legua'),
('Ferreyros S.A.', '20100028698', 'Maquinaria Pesada', 'empleos@ferreyros.com.pe', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'activo', true, 'Av. Industrial 675, Lima'),
('Intercorp Retail', '20506564177', 'Retail', 'seleccion@intercorp.com.pe', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'activo', true, 'Av. Carlos Villarán 140, La Victoria'),
('Globant Perú', '20536440704', 'Tecnología', 'jobs@globant.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'activo', true, 'Av. Alfredo Benavides 1561, Miraflores')
ON CONFLICT (ruc) DO NOTHING;

-- 6. INSERT 40 VACANTES (8 por empresa)
-- Empresa 1: BBVA (ID 1)
INSERT INTO vacantes (empresa_id, carrera_id, titulo_puesto, descripcion_puesto, requisitos_raw, modalidad, ubicacion, fecha_limite) VALUES 
(1, 1, 'Analista de Ciberseguridad Jr.', 'Protección de infraestructura bancaria.', 'Conocimiento en Firewalls, ISO 27001.', 'Híbrido', 'San Isidro', '2024-12-15'),
(1, 8, 'Asistente de Auditoría', 'Revisión de estados financieros.', 'Estudiante de 10mo ciclo.', 'Presencial', 'Lima', '2024-11-20'),
(1, 12, 'Practicante de Riesgos', 'Análisis de riesgo crediticio.', 'Excel avanzado, SQL intermedio.', 'Híbrido', 'Remoto/San Isidro', '2024-12-01'),
(1, 6, 'Gestor de Banca Negocios', 'Atención a cartera de clientes corporativos.', 'Habilidades comerciales.', 'Presencial', 'Lince', '2024-12-10'),
(1, 10, 'Analista de Selección', 'Entrevistas por competencias.', 'Bachiller en Psicología.', 'Remoto', 'Lima', '2024-11-30'),
(1, 1, 'Desarrollador Java Spring Boot', 'Mantenimiento del core bancario.', 'Java 17, Microservicios.', 'Remoto', 'Lima', '2024-12-28'),
(1, 8, 'Contador Tributario', 'Declaraciones mensuales y anuales.', 'Manejo de PDT, SAP.', 'Presencial', 'San Isidro', '2024-12-05'),
(1, 9, 'Abogado Compliance', 'Monitoreo de normatividad SBS.', 'Especialista en derecho financiero.', 'Híbrido', 'San Isidro', '2024-12-20');

-- Empresa 2: Alicorp (ID 2)
INSERT INTO vacantes (empresa_id, carrera_id, titulo_puesto, descripcion_puesto, requisitos_raw, modalidad, ubicacion, fecha_limite) VALUES 
(2, 2, 'Ingeniero de Procesos Junior', 'Optimización de líneas de producción.', 'Lean Manufacturing, Six Sigma.', 'Presencial', 'Callao', '2024-12-15'),
(2, 7, 'Community Manager Senior', 'Gestión de marcas globales.', 'Experiencia en agencias.', 'Remoto', 'Lima', '2024-11-25'),
(2, 6, 'Key Account Manager', 'Gestión de cuentas Retail.', 'Experiencia en negociación.', 'Presencial', 'Miraflores', '2024-12-05'),
(2, 2, 'Practicante de Logística', 'Control de inventarios y almacén.', 'Manejo de SAP R3.', 'Presencial', 'Callao', '2024-11-15'),
(2, 11, 'Asistente de Comunicaciones', 'Comunicación interna y eventos.', 'Redacción impecable.', 'Híbrido', 'Lima', '2024-12-12'),
(2, 10, 'Capacitador de Planta', 'Entrenamiento para operarios.', 'Psicología u Educación.', 'Presencial', 'Callao', '2024-12-20'),
(2, 8, 'Analista de Costos', 'Cálculo de costos de manufactura.', 'Contabilidad de costos avanzada.', 'Híbrido', 'Callao', '2024-11-30'),
(2, 12, 'Pricing Analyst', 'Estrategia de precios regional.', 'Estadística avanzada.', 'Remoto', 'Lima', '2024-12-18');

-- Empresa 3: Ferreyros (ID 3)
INSERT INTO vacantes (empresa_id, carrera_id, titulo_puesto, descripcion_puesto, requisitos_raw, modalidad, ubicacion, fecha_limite) VALUES 
(3, 2, 'Ingeniero Mecánico Junior', 'Mantenimiento de maquinaria Caterpillar.', 'Licencia de conducir, Inglés técnico.', 'Presencial', 'Arequipa', '2024-12-10'),
(3, 4, 'Planificador de Mantenimiento', 'Gestión de paradas de planta.', 'Software de activos (Maximo/SAP).', 'Presencial', 'Cajamarca', '2024-11-28'),
(3, 6, 'Vendedor de Repuestos', 'Venta técnica de componentes.', 'Conocimiento en maquinaria pesada.', 'Presencial', 'Lima', '2024-12-05'),
(3, 3, 'Supervisor de Obras Civiles', 'Construcción de talleres mineros.', 'Colegiado habilitado.', 'Presencial', 'Chimbote', '2024-11-20'),
(3, 10, 'Trabajadora Social', 'Gestión de beneficios para técnicos.', 'Experiencia en campamentos.', 'Presencial', 'Puno', '2024-12-15'),
(3, 8, 'Asistente Contable', 'Conciliaciones bancarias.', 'Dominio de Excel.', 'Híbrido', 'Lima', '2024-12-01'),
(3, 2, 'Practicante de Seguridad (SST)', 'Prevención de riesgos laborales.', 'Conocimiento ley 29783.', 'Presencial', 'Lima', '2024-11-30'),
(3, 9, 'Asesor Legal Junior', 'Contratos con sub-contratistas.', 'Derecho civil.', 'Híbrido', 'Lima', '2024-12-24');

-- Empresa 4: Intercorp Retail (ID 4)
INSERT INTO vacantes (empresa_id, carrera_id, titulo_puesto, descripcion_puesto, requisitos_raw, modalidad, ubicacion, fecha_limite) VALUES 
(4, 7, 'Ecommerce Manager', 'Gestión de canales digitales.', 'Experiencia en Shopify o VTEX.', 'Remoto', 'Lima', '2024-11-25'),
(4, 5, 'Visual Merchandiser Senior', 'Diseño de escaparates y tiendas.', 'Arquitecto o Diseñador.', 'Híbrido', 'Lima', '2024-12-05'),
(4, 6, 'Gerente de Tienda (Trainee)', 'Entrenamiento para gestión retail.', 'Liderazgo de equipos.', 'Presencial', 'Cusco', '2024-12-15'),
(4, 10, 'Analista de Clima Laboral', 'Mejora de cultura organizacional.', 'Experiencia en GPTW.', 'Híbrido', 'La Victoria', '2024-11-30'),
(4, 8, 'Auditor de Inventarios', 'Control de mermas en tiendas.', 'Contador o Admin.', 'Presencial', 'Nacional', '2024-12-20'),
(4, 1, 'Data Analyst Retail', 'Análisis de comportamiento de compra.', 'Power BI, SQL.', 'Remoto', 'Lima', '2024-12-10'),
(4, 11, 'Diseñador Gráfico', 'Piezas publicitarias para redes.', 'Adobe Premiere, After Effects.', 'Remoto', 'Lima', '2024-12-22'),
(4, 3, 'Residente de Mantenimiento', 'Gestión de locales comerciales.', 'Ingeniero Civil.', 'Presencial', 'Trujillo', '2024-11-28');

-- Empresa 5: Globant (ID 5)
INSERT INTO vacantes (empresa_id, carrera_id, titulo_puesto, descripcion_puesto, requisitos_raw, modalidad, ubicacion, fecha_limite) VALUES 
(5, 1, 'React Developer Web UI', 'Desarrollo de interfaces premium.', 'React, TypeScript, Redux.', 'Remoto', 'Perú/Global', '2024-12-30'),
(5, 1, 'QA Automation Engineer', 'Automatización de pruebas móviles.', 'Selenium, Appium, Java.', 'Remoto', 'Perú', '2024-12-15'),
(5, 5, 'UX Designer', 'Prototipado de experiencias de usuario.', 'Figma, Adobe XD.', 'Remoto', 'Lima', '2024-12-05'),
(5, 1, 'Cloud Engineer (AWS)', 'Gestión de infraestructura en nube.', 'Terraform, Docker.', 'Remoto', 'Lima', '2024-12-20'),
(5, 1, 'Data Scientist Jr.', 'Modelos predictivos aplicados.', 'Python, Pandas, ML.', 'Remoto', 'Lima', '2024-12-10'),
(5, 10, 'Technical Recruiter', 'Búsqueda de perfiles IT.', 'Inglés avanzado obligatorio.', 'Remoto', 'Lima', '2024-12-12'),
(5, 11, 'Digital Content Creator', 'Contenido para marca empleadora.', 'Inglés fluido.', 'Híbrido', 'Miraflores', '2024-12-18'),
(5, 6, 'Business Analyst', 'Traducción de requerimientos de negocio.', 'Agile, SCRUM.', 'Remoto', 'Lima', '2024-12-24');

