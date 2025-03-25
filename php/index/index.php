<?php
require 'config.php';

$spreadsheetId = '1Ah4kw6mVAENSXvEZzIJR2VL0-Z_-G0EVjBDiY7kl3NU'; // Ganti dengan ID spreadsheet kamu
$range = 'Sheet1!A:C'; // Menulis di kolom A, B, dan C

$service = getSpreadsheetService();

// Fungsi untuk membaca total uang yang sudah ada di spreadsheet
function getTotalUang($service, $spreadsheetId) {
    $range = 'Sheet1!C:C'; // Kolom C menyimpan total uang
    $response = $service->spreadsheets_values->get($spreadsheetId, $range);
    $values = $response->getValues();
    
    if (!empty($values)) {
        $lastRow = end($values);
        return isset($lastRow[0]) ? floatval($lastRow[0]) : 0;
    }
    return 0;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $jumlah = $_POST['jumlah']; // Ambil input jumlah uang
    date_default_timezone_set('Asia/Jakarta'); 
    $tanggal = date("Y-m-d H:i:s"); // Tanggal saat ini
    
    // Ambil total sebelumnya
    $totalSebelumnya = getTotalUang($service, $spreadsheetId);
    $totalSekarang = $totalSebelumnya + $jumlah;
    
    $values = [
        [$tanggal, $jumlah, $totalSekarang] // Data yang akan dimasukkan
    ];
    
    $body = new Google\Service\Sheets\ValueRange([
        'values' => $values
    ]);
    
    $params = ['valueInputOption' => 'RAW'];
    $service->spreadsheets_values->append($spreadsheetId, 'Sheet1!A:C', $body, $params);
    
    echo json_encode(["success" => true, "total" => $totalSekarang]);
    exit;
}

$totalTerkini = getTotalUang($service, $spreadsheetId);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Celengan Digital</title>
    <script>
        function submitForm(event) {
            event.preventDefault();
            let formData = new FormData(document.getElementById("celenganForm"));
            fetch("", {
                method: "POST",
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById("jumlah").value = ""; // Kosongkan input
                    document.getElementById("totalUang").innerText = "Rp " + new Intl.NumberFormat("id-ID").format(data.total);
                }
            })
            .catch(error => console.error("Error:", error));
        }
    </script>
</head>
<body>
    <h2>Masukkan Uang ke Celengan</h2>
    <form id="celenganForm" onsubmit="submitForm(event)">
        <label>Jumlah (Rp):</label>
        <input type="number" name="jumlah" id="jumlah" required>
        <button type="submit">Simpan</button>
    </form>
    <h3>Total Uang Saat Ini: <span id="totalUang">Rp <?php echo number_format($totalTerkini, 0, ',', '.'); ?></span></h3>
</body>
</html>
