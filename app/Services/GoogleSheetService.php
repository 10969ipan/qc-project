<?php

namespace App\Services;

use Google\Client;
use Google\Service\Sheets;
use Google\Service\Sheets\ValueRange;
use Google\Service\Sheets\ClearValuesRequest;
use Illuminate\Support\Facades\Log;

class GoogleSheetService
{
    private $client;
    private $service;
    private $sheetId;
    private $credentialsPath;

    public function __construct()
    {
        $this->sheetId = config('services.google.sheets_id');
        $this->credentialsPath = $this->resolveCredentialsPath();

        $this->client = new Client();
        
        // Configure Guzzle Client to ignore SSL errors if needed (matching original code's behavior)
        $this->client->setHttpClient(new \GuzzleHttp\Client(['verify' => false]));
        
        if (!file_exists($this->credentialsPath)) {
            $cwd = getcwd();
            throw new \Exception("Credentials file not found. Checked: '{$this->credentialsPath}'. Current Dir: '$cwd'. Tips: Pastikan file 'google-credentials.json' ada di folder 'storage/app/'.");
        }

        $this->client->setAuthConfig($this->credentialsPath);
        $this->client->addScope(Sheets::SPREADSHEETS);
        $this->client->setAccessType('offline');

        $this->service = new Sheets($this->client);
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
            public_path('google-credentials.json'),
        ];

        // Remove duplicates and empty values
        $pathsToCheck = array_unique(array_filter($pathsToCheck));

        foreach ($pathsToCheck as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        // Return the configured path (even if missing)
        return $configuredPath;
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

        try {
            $body = new ValueRange([
                'values' => [$values]
            ]);
            
            $params = [
                'valueInputOption' => 'USER_ENTERED'
            ];
            
            $this->service->spreadsheets_values->append($this->sheetId, 'Sheet1!A1', $body, $params);
            
            return true;
        } catch (\Exception $e) {
            throw new \Exception("Google Sheets Append Error: " . $e->getMessage());
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

        try {
            $requestBody = new ClearValuesRequest();
            $this->service->spreadsheets_values->clear($this->sheetId, $range, $requestBody);
            
            return true;
        } catch (\Exception $e) {
            throw new \Exception("Google Sheets Clear Error: " . $e->getMessage());
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

        try {
            $body = new ValueRange([
                'values' => $rows
            ]);
            
            $params = [
                'valueInputOption' => 'USER_ENTERED'
            ];
            
            $this->service->spreadsheets_values->append($this->sheetId, 'Sheet1!A1', $body, $params);
            
            return true;
        } catch (\Exception $e) {
            throw new \Exception("Google Sheets Append Rows Error: " . $e->getMessage());
        }
    }
}
