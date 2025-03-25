<?php
require 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $jumlah = $_POST['jumlah']; // Ambil input jumlah uang
    $tanggal = date("Y-m-d H:i:s"); // Tanggal saat ini

    $spreadsheetId = '1Ah4kw6mVAENSXvEZzIJR2VL0-Z_-G0EVjBDiY7kl3NU'; // Ganti dengan ID spreadsheet kamu
    $range = 'Sheet1!A:B'; // Menulis di kolom A dan B

    $service = getSpreadsheetService();

    $values = [
        [$tanggal, $jumlah] // Data yang akan dimasukkan
    ];
    $body = new Google\Service\Sheets\ValueRange([
        'values' => $values
    ]);

    $params = ['valueInputOption' => 'RAW'];
    $service->spreadsheets_values->append($spreadsheetId, $range, $body, $params);

    echo "Data berhasil disimpan!";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Celengan Digital</title>
</head>
<body>
    <h2>Masukkan Uang ke Celengan</h2>
    <form method="post">
        <label>Jumlah (Rp):</label>
        <input type="number" name="jumlah" required>
        <button type="submit">Simpan</button>
    </form>
</body>
</html>
