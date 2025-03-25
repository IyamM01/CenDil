<?php
require 'config.php';

$spreadsheetId = '1Ah4kw6mVAENSXvEZzIJR2VL0-Z_-G0EVjBDiY7kl3NU'; // Ganti dengan ID spreadsheet kamu
$range = 'Sheet1!A:B'; // Menulis di kolom A dan B

$service = getSpreadsheetService();

// Fungsi untuk membaca total uang yang sudah ada di spreadsheet
function getTotalUang($service, $spreadsheetId) {
    try {
        $range = 'Sheet1!B:B'; // Kolom B menyimpan jumlah uang
        $response = $service->spreadsheets_values->get($spreadsheetId, $range);
        $values = $response->getValues();
    
        $total = 0;
        if (!empty($values)) {
            foreach ($values as $row) {
                if (isset($row[0])) {
                    $total += floatval($row[0]);
                }
            }
        }
        return $total;
    } catch (Exception $e) {
        return 0;
    }
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
    
    try {
        $body = new Google\Service\Sheets\ValueRange([
            'values' => $values
        ]);
    
        $params = ['valueInputOption' => 'RAW'];
        $service->spreadsheets_values->append($spreadsheetId, 'Sheet1!A:C', $body, $params);
    
        echo json_encode(["status" => "success", "total" => $totalSekarang]);
    } catch (Exception $e) {
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
    exit();
}

$totalTerkini = getTotalUang($service, $spreadsheetId);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Celengan Digital</title>
    <script>
        function tambahUang() {
            var jumlahInput = document.getElementById("jumlah");
            var jumlah = jumlahInput.value;
            if (jumlah === "") return;
            
            var formData = new FormData();
            formData.append("jumlah", jumlah);
            
            fetch("", {
                method: "POST",
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === "success") {
                    document.getElementById("totalUang").innerText = "Rp " + new Intl.NumberFormat("id-ID").format(data.total);
                    jumlahInput.value = ""; // Kosongkan input setelah submit
                } else {
                    alert("Terjadi kesalahan: " + data.message);
                }
            })
            .catch(error => console.error("Error:", error));
        }
    </script>
</head>
<body>
    <h2>Masukkan Uang ke Celengan</h2>
    <form onsubmit="event.preventDefault(); tambahUang();">
        <label>Jumlah (Rp):</label>
        <input type="number" id="jumlah" required>
        <button type="submit">Simpan</button>
    </form>
    <h3>Total Uang Saat Ini: <span id="totalUang">Rp <?php echo number_format($totalTerkini, 0, ',', '.'); ?></span></h3>
</body>
</html>
