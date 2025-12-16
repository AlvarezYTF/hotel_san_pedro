<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class FactusApiService
{
    private string $baseUrl;
    private string $clientId;
    private string $clientSecret;
    private string $username;
    private string $password;
    private string $tokenEndpoint;

    public function __construct()
    {
        $this->baseUrl = config('factus.api_url');
        $this->clientId = config('factus.client_id');
        $this->clientSecret = config('factus.client_secret');
        $this->username = config('factus.username');
        $this->password = config('factus.password');
        $this->tokenEndpoint = '/oauth/token';

        // Validar que las credenciales estén configuradas
        if (empty($this->baseUrl) || empty($this->clientId) || empty($this->clientSecret) || empty($this->username) || empty($this->password)) {
            throw new \Exception('Las credenciales de Factus no están configuradas correctamente. Verifica las variables de entorno.');
        }
    }

    public function getAuthToken(): string
    {
        $tokenData = Cache::get('factus_token_data');
        
        if ($tokenData && isset($tokenData['access_token'])) {
            $expiresAt = $tokenData['expires_at'] ?? null;
            
            if ($expiresAt && now()->lt($expiresAt)) {
                return $tokenData['access_token'];
            }
            
            if (isset($tokenData['refresh_token'])) {
                try {
                    return $this->refreshAccessToken($tokenData['refresh_token']);
                } catch (\Exception $e) {
                    Log::warning('Error al renovar token con refresh_token, obteniendo nuevo token', [
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }

        return $this->requestNewAccessToken();
    }

    private function requestNewAccessToken(): string
    {
        $tokenUrl = "{$this->baseUrl}{$this->tokenEndpoint}";
        
        Log::info('Solicitando nuevo token de acceso de Factus', [
            'url' => $tokenUrl,
            'has_credentials' => !empty($this->clientId) && !empty($this->username),
        ]);

        $httpClient = Http::withHeaders([
            'Accept' => 'application/json',
        ]);
        
        if (!config('factus.verify_ssl', true)) {
            $httpClient = $httpClient->withoutVerifying();
        }
        
        $response = $httpClient->asForm()->post($tokenUrl, [
            'grant_type' => 'password',
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'username' => $this->username,
            'password' => $this->password,
        ]);

        if (!$response->successful()) {
            $errorBody = $response->body();
            Log::error('Error al autenticar con Factus OAuth2', [
                'status' => $response->status(),
                'url' => $tokenUrl,
                'response' => $errorBody,
            ]);
            throw new \Exception("Error al autenticar con Factus OAuth2 (HTTP {$response->status()}): {$errorBody}");
        }

        $data = $response->json();
        $accessToken = $data['access_token'] ?? null;
        $refreshToken = $data['refresh_token'] ?? null;
        $expiresIn = $data['expires_in'] ?? 600;
        $tokenType = $data['token_type'] ?? 'Bearer';

        if (!$accessToken) {
            Log::error('No se recibió access_token en la respuesta de Factus', [
                'response_data' => $data,
            ]);
            throw new \Exception('No se recibió access_token de Factus');
        }

        $expiresAt = now()->addSeconds($expiresIn - 60);

        $tokenData = [
            'access_token' => $accessToken,
            'token_type' => $tokenType,
            'expires_at' => $expiresAt,
            'expires_in' => $expiresIn,
        ];

        if ($refreshToken) {
            $tokenData['refresh_token'] = $refreshToken;
        }

        Cache::put('factus_token_data', $tokenData, now()->addSeconds($expiresIn));

        Log::info('Nuevo token de acceso obtenido de Factus', [
            'expires_at' => $expiresAt->toIso8601String(),
            'expires_in' => $expiresIn,
            'token_type' => $tokenType,
            'has_refresh_token' => !empty($refreshToken),
        ]);

        return $accessToken;
    }

    private function refreshAccessToken(string $refreshToken): string
    {
        $httpClient = Http::withHeaders([
            'Accept' => 'application/json',
        ]);
        
        if (!config('factus.verify_ssl', true)) {
            $httpClient = $httpClient->withoutVerifying();
        }
        
        $response = $httpClient->asForm()->post("{$this->baseUrl}{$this->tokenEndpoint}", [
            'grant_type' => 'refresh_token',
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'refresh_token' => $refreshToken,
        ]);

        if (!$response->successful()) {
            throw new \Exception('Error al renovar token con Factus OAuth2: ' . $response->body());
        }

        $data = $response->json();
        $accessToken = $data['access_token'] ?? null;
        $newRefreshToken = $data['refresh_token'] ?? $refreshToken;
        $expiresIn = $data['expires_in'] ?? 600;
        $tokenType = $data['token_type'] ?? 'Bearer';

        if (!$accessToken) {
            throw new \Exception('No se recibió access_token al renovar token');
        }

        $expiresAt = now()->addSeconds($expiresIn - 60);

        $tokenData = [
            'access_token' => $accessToken,
            'token_type' => $tokenType,
            'refresh_token' => $newRefreshToken,
            'expires_at' => $expiresAt,
            'expires_in' => $expiresIn,
        ];

        Cache::put('factus_token_data', $tokenData, now()->addSeconds($expiresIn));

        Log::info('Token de acceso renovado usando refresh_token', [
            'expires_at' => $expiresAt->toIso8601String(),
            'expires_in' => $expiresIn,
            'token_type' => $tokenType,
            'has_new_refresh_token' => ($newRefreshToken !== $refreshToken),
        ]);

        return $accessToken;
    }

    public function get(string $endpoint, array $params = []): array
    {
        $token = $this->getAuthToken();

        if (empty($token)) {
            throw new \Exception('No se pudo obtener token de autenticación de Factus');
        }

        $httpClient = Http::withHeaders([
            'Authorization' => "Bearer {$token}",
            'Accept' => 'application/json',
        ]);
        
        if (!config('factus.verify_ssl', true)) {
            $httpClient = $httpClient->withoutVerifying();
        }

        $fullUrl = "{$this->baseUrl}{$endpoint}";
        $response = $httpClient->get($fullUrl, $params);

        if (!$response->successful()) {
            $errorBody = $response->body();
            $statusCode = $response->status();
            
            if ($statusCode === 401) {
                Log::warning('Token expirado o inválido en GET request, renovando token', [
                    'endpoint' => $endpoint,
                    'url' => $fullUrl,
                    'response' => $errorBody
                ]);
                
                // Limpiar token y obtener uno nuevo
                Cache::forget('factus_token_data');
                $token = $this->getAuthToken();
                
                if (empty($token)) {
                    throw new \Exception('No se pudo renovar el token de autenticación de Factus');
                }
                
                $httpClient = Http::withHeaders([
                    'Authorization' => "Bearer {$token}",
                    'Accept' => 'application/json',
                ]);
                
                if (!config('factus.verify_ssl', true)) {
                    $httpClient = $httpClient->withoutVerifying();
                }
                
                $response = $httpClient->get($fullUrl, $params);
                
                if (!$response->successful()) {
                    $errorBody = $response->body();
                    Log::error("Error en GET {$endpoint} después de renovar token", [
                        'status' => $response->status(),
                        'url' => $fullUrl,
                        'response' => $errorBody,
                    ]);
                    throw new \Exception("Error en GET {$endpoint} después de renovar token (HTTP {$response->status()}): {$errorBody}");
                }
            } else {
                Log::error("Error en GET {$endpoint}", [
                    'status' => $statusCode,
                    'url' => $fullUrl,
                    'response' => $errorBody,
                ]);
                throw new \Exception("Error en GET {$endpoint} (HTTP {$statusCode}): {$errorBody}");
            }
        }

        return $response->json();
    }

    public function post(string $endpoint, array $data): array
    {
        $token = $this->getAuthToken();

        $httpClient = Http::withHeaders([
            'Authorization' => "Bearer {$token}",
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ]);
        
        if (!config('factus.verify_ssl', true)) {
            $httpClient = $httpClient->withoutVerifying();
        }

        $response = $httpClient->post("{$this->baseUrl}{$endpoint}", $data);

        if (!$response->successful()) {
            if ($response->status() === 401) {
                Log::warning('Token expirado en POST request, renovando token', ['endpoint' => $endpoint]);
                Cache::forget('factus_token_data');
                $token = $this->getAuthToken();
                
                $httpClient = Http::withHeaders([
                    'Authorization' => "Bearer {$token}",
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ]);
                
                if (!config('factus.verify_ssl', true)) {
                    $httpClient = $httpClient->withoutVerifying();
                }
                
                $response = $httpClient->post("{$this->baseUrl}{$endpoint}", $data);
                
                if (!$response->successful()) {
                    $errorBody = $response->body();
                    Log::error("Error en POST {$endpoint} después de renovar token", [
                        'status' => $response->status(),
                        'body' => $errorBody,
                        'data_sent' => $data,
                    ]);
                    throw new \Exception("Error en POST {$endpoint} después de renovar token: {$errorBody}");
                }
            } else {
                $errorBody = $response->body();
                Log::error("Error en POST {$endpoint}", [
                    'status' => $response->status(),
                    'body' => $errorBody,
                    'data_sent' => $data,
                ]);
                throw new \Exception("Error en POST {$endpoint}: {$errorBody}");
            }
        }

        return $response->json();
    }

    /**
     * Obtiene el listado de facturas desde Factus con filtros y paginación
     * 
     * @param array $filters Filtros: identification, names, number, prefix, reference_code, status
     * @param int $page Número de página
     * @param int $perPage Resultados por página (máximo 100 según Factus)
     * @return array Respuesta con facturas y paginación
     * @throws \Exception Si falla la petición
     */
    public function getBills(array $filters = [], int $page = 1, int $perPage = 10): array
    {
        $params = [
            'page' => $page,
            'per_page' => min($perPage, 100), // Limitar a 100 según Factus
        ];

        // Agregar filtros
        foreach ($filters as $key => $value) {
            if (!empty($value)) {
                $params["filter[{$key}]"] = $value;
            }
        }

        $response = $this->get('/v1/bills', $params);

        return $response;
    }

    /**
     * Obtiene el estado de una factura específica desde Factus
     * 
     * @param string $number Número de factura (ej: SETP990000203)
     * @return array|null Datos de la factura o null si no se encuentra
     * @throws \Exception Si falla la petición
     */
    public function getBillByNumber(string $number): ?array
    {
        $response = $this->getBills(['number' => $number], 1, 1);
        
        // La respuesta puede tener estructura: ['data' => ['data' => [...]]] o ['data' => [...]]
        $data = $response['data']['data'] ?? $response['data'] ?? null;
        
        if (!is_array($data) || empty($data)) {
            return null;
        }

        // Buscar la factura exacta por número
        foreach ($data as $bill) {
            if (isset($bill['number']) && $bill['number'] === $number) {
                return $bill;
            }
        }

        return null;
    }

    /**
     * Descarga el PDF de una factura desde Factus
     * 
     * @param string $number Número de factura (ej: SETP990000203)
     * @return array Respuesta con file_name y pdf_base_64_encoded
     * @throws \Exception Si falla la petición
     */
    public function downloadPdf(string $number): array
    {
        $endpoint = "/v1/bills/download-pdf/{$number}";
        $response = $this->get($endpoint);

        if (!isset($response['data'])) {
            throw new \Exception('Respuesta inválida de Factus API al descargar PDF');
        }

        return $response['data'];
    }
}
