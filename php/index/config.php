<?php
require __DIR__ . '/../vendor/autoload.php'; // Adjusted path to autoload.php

use Google\Client;
use Google\Service\Sheets;

function getGoogleClient() {
    $client = new Client();
    $client->setApplicationName('Celengan Digital');
    $client->setScopes([Sheets::SPREADSHEETS]);
    $client->setAuthConfig('credentials.json'); // Pastikan file JSON ini ada di folder proyek
    $client->setAccessType('offline');
    return $client;
}

function getSpreadsheetService() {
    $client = getGoogleClient();
    return new Sheets($client);
}
?>
