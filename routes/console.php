<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('google-sheets:test', function () {
    $this->info('Testing Google Sheets connection...');
    
    try {
        $service = new \App\Services\GoogleSheetService();
        $this->info('GoogleSheetService initialized successfully.');
        
        $this->info('Attempting to append a test row...');
        $service->appendRow(['Connection Test', date('Y-m-d H:i:s')]);
        $this->info('Successfully appended a test row.');
        
    } catch (\Exception $e) {
        $this->error('Connection failed: ' . $e->getMessage());
        
        // Add specific advice based on error
        if (str_contains($e->getMessage(), 'invalid_grant')) {
            $this->error('Tip: Check your system time and ensure "private_key" in google-credentials.json is correct.');
        }
    }
})->purpose('Test the Google Sheets connection and credentials');
