<?php
require __DIR__ . '/../vendor/autoload.php';

use Google\Client;
use Google\Service\Sheets;

function getGoogleClient() {
    $client = new Client();
    $client->setApplicationName('Celengan Digital');
    $client->setScopes([Sheets::SPREADSHEETS]);
    $client->setAuthConfig('credentials.json');
    $client->setAccessType('offline');
    return $client;
}

// Kirim notifikasi ke Telegram
function sendTelegramNotification($message) {
    $telegramBotToken = "8186039702:AAGsA-TW87U07NSi9ZRvgEKyQyQKO7Q_KA0"; // Ganti dengan token bot Telegram Anda
    $chatId = "8186039702"; // Ganti dengan ID chat Telegram Anda
    $url = "https://api.telegram.org/bot$telegramBotToken/sendMessage?chat_id=$chatId&text=" . urlencode($message);
    
    file_get_contents($url); // Kirim request ke Telegram
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $status = $_POST["status"] ?? "Tidak diketahui";
    $jarak = $_POST["jarak"] ?? "0";

    if ($status === "penuh") { // Hanya kirim jika penuh
        $service = new Sheets(getGoogleClient());
        $spreadsheetId = "1Ah4kw6mVAENSXvEZzIJR2VL0-Z_-G0EVjBDiY7kl3NU";
        $range = "Sheet2!A2:B";

        $values = [[date("Y-m-d H:i:s"), $status, $jarak]];
        $body = new Google\Service\Sheets\ValueRange(["values" => $values]);
        $params = ["valueInputOption" => "RAW"];

        try {
            $service->spreadsheets_values->append($spreadsheetId, $range, $body, $params);
            sendTelegramNotification("Celengan sudah penuh! Jarak: " . $jarak . " cm");
            echo "Data berhasil dikirim ke Spreadsheet & notifikasi dikirim!";
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }
    } else {
        echo "Celengan belum penuh, tidak mengirim data.";
    }
}
?>
