<?php

namespace App\Services;

class ExternalApiService {
    
    private static function getApiKey() {
        return trim($_ENV['INTI_API_KEY'] ?? '');
    }

    private static function getBaseUrl() {
        return rtrim($_ENV['INTI_BASE_URL'] ?? 'https://apiperu.dev/api', '/');
    }

    public static function consultRuc($ruc) {
        $url = self::getBaseUrl() . "/ruc/" . $ruc;
        return self::makeRequest($url);
    }

    public static function consultDni($dni) {
        $url = self::getBaseUrl() . "/dni/" . $dni;
        return self::makeRequest($url);
    }

    private static function makeRequest($url) {
        $key = self::getApiKey();
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer $key",
            "Content-Type: application/json",
            "Accept: application/json"
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $json = json_decode($response, true);
        
        if ($httpCode === 200 && ($json['success'] ?? false)) {
            $raw = $json['data'] ?? [];
            $nombres        = $raw['nombres'] ?? '';
            $apellidoPat    = $raw['apellido_paterno'] ?? '';
            $apellidoMat    = $raw['apellido_materno'] ?? '';
            $nombreCompleto = trim("$nombres $apellidoPat $apellidoMat");
            if (empty($nombreCompleto)) { $nombreCompleto = $raw['nombre_completo'] ?? ''; }
            
            $razonSocial = $raw['nombre_o_razon_social'] ?? $raw['razon_social'] ?? '';
            $sector      = $raw['actividad_economica'] ?? 'GENERAL';
            if (is_array($sector)) $sector = $sector[0] ?? 'GENERAL';

            return [
                'success' => true,
                'data' => [
                    'nombre_completo'   => $nombreCompleto ?: $razonSocial,
                    'nombres'           => $nombres,
                    'apellido_paterno'  => $apellidoPat,
                    'apellido_materno'  => $apellidoMat,
                    'nombre_comercial'  => $razonSocial,
                    'razon_social'      => $razonSocial,
                    'direccion'         => $raw['direccion_completa'] ?? $raw['direccion'] ?? '',
                    'sector'            => $sector,
                ]
            ];
        }

        $errorMsg = $json['message'] ?? 'API Error ' . $httpCode;
        return ['success' => false, 'error' => $errorMsg];
    }

    /**
     * Envía el PDF y requisitos al webhook de n8n.
     */
    public static function analizarCvConN8n(string $cvFilePath, string $requisitos): array {
        $webhookUrl = 'https://n8n-dqmewasf.us-west-1.clawcloudrun.com/webhook/analizar-cv';

        if (!file_exists($cvFilePath)) {
            return ['success' => false, 'error' => 'Archivo no encontrado: ' . $cvFilePath];
        }

        $cvFile = new \CURLFile($cvFilePath, 'application/pdf', 'curriculum.pdf');

        // Intentamos enviar múltiples alias para las claves, por si n8n espera uno específico
        $postData = [
            'file'         => $cvFile,
            'cv'           => $cvFile, // Shorthand
            'cv_pdf'       => $cvFile,
            'requisitos'   => $requisitos,
            'requirements' => $requisitos,
        ];

        $ch = curl_init($webhookUrl);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $postData,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 120, // Aumentado a 2 minutos
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTPHEADER     => [
                'Accept: application/json'
            ]
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr  = curl_error($ch);
        curl_close($ch);

        if ($curlErr) {
            return ['success' => false, 'error' => 'Falla de conexión: ' . $curlErr];
        }

        // Si n8n devuelve 200 pero vacío, es que el flujo no retornó data
        if (empty($response) || trim($response) === '') {
            return [
                'success' => false, 
                'error' => 'n8n aceptó el archivo (200) pero no envió un análisis. Verifica el flujo n8n.'
            ];
        }

        $data = json_decode($response, true);
        if (is_array($data) && isset($data[0])) $data = $data[0];

        if ($httpCode >= 200 && $httpCode < 300) {
            // Mapeo flexible de campos de IA
            $puntaje     = $data['puntaje'] ?? $data['score'] ?? $data['match'] ?? 0;
            $descripcion = $data['descripcion'] ?? $data['notes'] ?? $data['analisis'] ?? $data['output'] ?? 'Análisis completado sin comentarios.';

            // Normalización: si el puntaje viene entre 0 y 1, multiplicamos por 100
            if ($puntaje > 0 && $puntaje <= 1) {
                $puntaje = $puntaje * 100;
            }

            return [
                'success'     => true,
                'puntaje'     => floatval($puntaje),
                'descripcion' => (string)$descripcion,
                'raw'         => $data
            ];
        }

        return [
            'success' => false,
            'error' => 'HTTP ' . $httpCode . ': ' . ($data['message'] ?? substr($response, 0, 300))
        ];
    }
}
