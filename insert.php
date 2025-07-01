<?php
$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "kelembapan";

// Koneksi ke database
$conn = new mysqli($servername, $username, $password, $dbname);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Cek parameter GET
if (
    isset($_GET['kelembapan']) &&
    isset($_GET['suhu']) &&
    isset($_GET['kelembapan_udara']) &&
    isset($_GET['status'])
) {
    // Sanitasi dan validasi
    $kelembapan       = intval($_GET['kelembapan']);                // 0 - 4095
    $suhu             = floatval($_GET['suhu']);                    // misal: 30.5
    $kelembapan_udara = floatval($_GET['kelembapan_udara']);       // misal: 65.0
    $status           = htmlspecialchars(trim($_GET['status']));   // "Hidup" atau "Mati"

    // Validasi nilai
    if (
        ($status === "Hidup" || $status === "Mati") &&
        $kelembapan >= 0 && $kelembapan <= 4095 &&
        $suhu >= -40 && $suhu <= 80 &&                     // DHT11 range suhu
        $kelembapan_udara >= 0 && $kelembapan_udara <= 100 // DHT11 range kelembapan
    ) {
        $sql = "INSERT INTO data_sensor (kelembapan, suhu, kelembapan_udara, status)
                VALUES ('$kelembapan', '$suhu', '$kelembapan_udara', '$status')";

        if ($conn->query($sql) === TRUE) {
            echo "Data berhasil disimpan";
        } else {
            echo "Gagal menyimpan data: " . $conn->error;
        }
    } else {
        echo "Data tidak valid";
    }
} else {
    echo "Parameter tidak lengkap";
}

$conn->close();
?>
