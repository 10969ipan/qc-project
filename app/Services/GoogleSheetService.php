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
    private $resolvedSheetName = null;
    private $userDefinedSheetName = null;

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

        // Load the credentials and sanitize the private key
        $authConfig = json_decode(file_get_contents($this->credentialsPath), true);
        if (!is_array($authConfig)) {
            throw new \Exception("Invalid credentials file format: " . $this->credentialsPath);
        }

        if (isset($authConfig['private_key'])) {
            $key = $authConfig['private_key'];
            // Replace literal \n and \r with actual characters or remove them
            $key = str_replace(['\\n', '\\r'], ["\n", ""], $key);
            $authConfig['private_key'] = $key;
            
            // Validate the key format if OpenSSL is available
            if (extension_loaded('openssl')) {
                // Suppress warnings to capture error via openssl_error_string
                $pkey = @openssl_pkey_get_private($key);
                if (!$pkey) {
                    $errorMsg = "The private_key in google-credentials.json is invalid.";
                    while ($msg = openssl_error_string()) {
                        $errorMsg .= " OpenSSL: " . $msg;
                    }
                    // Clear error buffer
                    while(openssl_error_string()){}
                    
                    throw new \Exception($errorMsg);
                }
            }
        }

        $this->client->setAuthConfig($authConfig);
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
     * Set the sheet name manually (e.g., 'Sheet2').
     * @param string $name
     */
    public function setSheetName($name)
    {
        $this->userDefinedSheetName = $name;
    }

    /**
     * Resolve the sheet name dynamically from the spreadsheet.
     * Caches the result for the instance.
     * 
     * @return string
     */
    public function getSheetName()
    {
        if ($this->userDefinedSheetName) {
            return $this->userDefinedSheetName;
        }

        if ($this->resolvedSheetName) {
            return $this->resolvedSheetName;
        }

        try {
            // Get spreadsheet metadata to find the first sheet's name
            $spreadsheet = $this->service->spreadsheets->get($this->sheetId);
            $sheets = $spreadsheet->getSheets();
            if (count($sheets) > 0) {
                $this->resolvedSheetName = $sheets[0]->getProperties()->getTitle();
            } else {
                // Fallback if no sheets found (unlikely)
                $this->resolvedSheetName = 'Sheet1';
            }
        } catch (\Exception $e) {
            Log::warning("Failed to fetch sheet name: " . $e->getMessage());
            // Fallback to 'Sheet1' if API fails (e.g. permission issue, but let append try)
            $this->resolvedSheetName = 'Sheet1';
        }

        return $this->resolvedSheetName;
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
            
            $range = $this->getSheetName() . '!A1';
            $this->service->spreadsheets_values->append($this->sheetId, $range, $body, $params);
            
            return true;
        } catch (\Exception $e) {
            throw new \Exception("Google Sheets Append Error: " . $e->getMessage());
        }
    }

    /**
     * Clear all values in the sheet or a specific range.
     * @param string|null $range The range to clear. If null, uses the first sheet.
     */
    public function clearSheet($range = null)
    {
        if (!$this->sheetId) {
            throw new \Exception("Google Sheet ID is not configured.");
        }

        if ($range === null) {
            $range = $this->getSheetName();
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
            
            $range = $this->getSheetName() . '!A1';
            $this->service->spreadsheets_values->append($this->sheetId, $range, $body, $params);
            
            return true;
        } catch (\Exception $e) {
            throw new \Exception("Google Sheets Append Rows Error: " . $e->getMessage());
        }
    }
}
