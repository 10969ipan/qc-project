<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleSheetService
{
    private $sheetId;
    private $credentialsPath;

    public function __construct()
    {
        $this->sheetId = config('services.google.sheets_id');
        $this->credentialsPath = $this->resolveCredentialsPath();
    }

    /**
     * Attempt to find the credentials file in multiple common locations.
     */
    private function resolveCredentialsPath()
    {
        $configuredPath = config('services.google.sheets_credentials_path');
        
        // List of paths to check
        $pathsToCheck = [
            $configuredPath,
            storage_path('app/google-credentials.json'),
            base_path('google-credentials.json'),
            base_path('storage/app/google-credentials.json'),
            public_path('google-credentials.json'), // Not recommended but checking for user error
        ];

        // Remove duplicates and empty values
        $pathsToCheck = array_unique(array_filter($pathsToCheck));

        foreach ($pathsToCheck as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        // Return the configured path (even if missing) so the error message shows the primary expectation
        return $configuredPath;
    }

    /**
     * Generate an access token using JWT and the Service Account credentials.
     * @throws \Exception
     */
    private function getAccessToken()
    {
        if (!file_exists($this->credentialsPath)) {
            // Detailed error message for debugging
            $cwd = getcwd();
            $checked = $this->credentialsPath;
            throw new \Exception("Credentials file not found. Checked: '$checked'. Current Dir: '$cwd'. Tips: Pastikan file 'google-credentials.json' ada di folder 'storage/app/'.");
        }

        $content = file_get_contents($this->credentialsPath);
        $credentials = json_decode($content, true);
        
        if (!$credentials || !isset($credentials['client_email']) || !isset($credentials['private_key'])) {
            throw new \Exception("Invalid JSON structure in credentials file. Pastikan Anda mengunduh file JSON yang benar dari Google Console.");
        }

        $header = ['alg' => 'RS256', 'typ' => 'JWT'];
        $now = time();
        $payload = [
            'iss' => $credentials['client_email'],
            'sub' => $credentials['client_email'],
            'aud' => 'https://oauth2.googleapis.com/token',
            'iat' => $now,
            'exp' => $now + 3600,
            'scope' => 'https://www.googleapis.com/auth/spreadsheets'
        ];

        $base64UrlHeader = $this->base64UrlEncode(json_encode($header));
        $base64UrlPayload = $this->base64UrlEncode(json_encode($payload));

        $signature = '';
        // Use SHA256 explicitly. Ensure OpenSSL extension is enabled.
        $success = openssl_sign(
            $base64UrlHeader . "." . $base64UrlPayload,
            $signature,
            $credentials['private_key'],
            'SHA256'
        );

        if (!$success) {
            $opensslError = '';
            while ($msg = openssl_error_string()) {
                $opensslError .= $msg . '; ';
            }
            throw new \Exception("Failed to sign JWT: " . $opensslError);
        }

        $jwt = $base64UrlHeader . "." . $base64UrlPayload . "." . $this->base64UrlEncode($signature);

        // Exchange JWT for Access Token
        // 'withoutVerifying()' is added to bypass local SSL certificate issues (cURL error 77)
        $response = Http::withoutVerifying()->asForm()->post('https://oauth2.googleapis.com/token', [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt
        ]);

        if ($response->successful()) {
            return $response->json()['access_token'];
        } else {
            throw new \Exception("Google OAuth Token Error: " . $response->body());
        }
    }

    private function base64UrlEncode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Append a row to the configured Google Sheet.
     * @param array $values List of values to append (e.g., ['Column1', 'Column2'])
     * @throws \Exception
     */
    public function appendRow(array $values)
    {
        if (!$this->sheetId) {
            throw new \Exception("Google Sheet ID is not configured in .env (GOOGLE_SHEETS_ID).");
        }

        $token = $this->getAccessToken();

        // Default to Sheet1!A1. We append, so it adds to the bottom.
        // valueInputOption=USER_ENTERED allows google to parse numbers/dates.
        $url = "https://sheets.googleapis.com/v4/spreadsheets/{$this->sheetId}/values/Sheet1!A1:append?valueInputOption=USER_ENTERED";

        $response = Http::withoutVerifying()->withToken($token)->post($url, [
            'range' => 'Sheet1!A1',
            'majorDimension' => 'ROWS',
            'values' => [$values]
        ]);

        if ($response->successful()) {
            return true;
        } else {
            throw new \Exception("Google Sheets Append Error: " . $response->body());
        }
    }

    /**
     * Clear all values in the sheet or a specific range.
     * @param string $range The range to clear (default: 'Sheet1')
     */
    public function clearSheet($range = 'Sheet1')
    {
        if (!$this->sheetId) {
            throw new \Exception("Google Sheet ID is not configured.");
        }

        $token = $this->getAccessToken();
        // Clear specified range
        $url = "https://sheets.googleapis.com/v4/spreadsheets/{$this->sheetId}/values/{$range}:clear";

        $response = Http::withoutVerifying()->withToken($token)->post($url);

        if ($response->successful()) {
            return true;
        } else {
            throw new \Exception("Google Sheets Clear Error: " . $response->body());
        }
    }

    /**
     * Append multiple rows to the sheet.
     * @param array $rows Array of rows (e.g. [['A', 'B'], ['C', 'D']])
     */
    public function appendRows(array $rows)
    {
        if (!$this->sheetId) {
            throw new \Exception("Google Sheet ID is not configured.");
        }

        $token = $this->getAccessToken();
        $url = "https://sheets.googleapis.com/v4/spreadsheets/{$this->sheetId}/values/Sheet1!A1:append?valueInputOption=USER_ENTERED";

        $response = Http::withoutVerifying()->withToken($token)->post($url, [
            'range' => 'Sheet1!A1',
            'majorDimension' => 'ROWS',
            'values' => $rows
        ]);

        if ($response->successful()) {
            return true;
        } else {
            throw new \Exception("Google Sheets Append Rows Error: " . $response->body());
        }
    }
}
