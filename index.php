<?php
/* =====================================================
 * 1. Koneksi database
 * ===================================================== */
$mysqli = new mysqli('localhost', 'root', '', 'kelembapan');
if ($mysqli->connect_errno) {
    die("<div class='alert alert-danger text-center m-4'>Gagal terhubung ke database: {$mysqli->connect_error}</div>");
}

/* =====================================================
 * 2. Variabel default
 * ===================================================== */
$kelembapanTanah = 0;        // ADC soil moisture
$suhu            = 0.0;      // °C
$kelembapanUdara = 0.0;      // % RH
$statusTanah     = '-';
$statusPompa     = '-';      // 'Hidup' / 'Mati'
$waktu           = '-';
$data_kelembapan = [];
$label_waktu     = [];

/* =====================================================
 * 3. Ambil 1 data terbaru
 * ===================================================== */
$sqlLatest = "SELECT kelembapan, suhu, kelembapan_udara, status, waktu
              FROM data_sensor
              ORDER BY id DESC LIMIT 1";
if ($result = $mysqli->query($sqlLatest)) {
    if ($row = $result->fetch_assoc()) {
        $kelembapanTanah = (int)   $row['kelembapan'];
        $suhu            = (float) $row['suhu'];
        $kelembapanUdara = (float) $row['kelembapan_udara'];
        $statusPompa     = $row['status'];                       // 'Hidup' / 'Mati'
        // Determine soil status based on kelembapanTanah value
        $statusTanah     = ($kelembapanTanah > 2000) ? 'Kering' : 'Lembap'; // Assuming higher ADC value means drier
        $waktu           = $row['waktu'];
    }
    $result->free();
}

/* =====================================================
 * 4. Ambil 10 data terakhir untuk grafik (kelembapan tanah)
 * ===================================================== */
$sqlChart = "SELECT kelembapan, waktu FROM data_sensor ORDER BY id DESC LIMIT 10";
if ($result = $mysqli->query($sqlChart)) {
    while ($row = $result->fetch_assoc()) {
        $data_kelembapan[] = (int) $row['kelembapan'];
        $label_waktu[]     = date('H:i:s', strtotime($row['waktu']));
    }
    $data_kelembapan = array_reverse($data_kelembapan); // urut lama→baru
    $label_waktu     = array_reverse($label_waktu);
    $result->free();
}
$mysqli->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Monitoring Kelembapan Tanah</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="refresh" content="5">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <style>
        body{background:#f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;}
        .card-custom{
            border:none;
            border-radius:15px;
            box-shadow:0 6px 20px rgba(0,0,0,.08);
            background:#fff;
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        }
        .card-custom:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,.12);
        }
        .sensor-icon{font-size:3.5rem;color:rgb(192,200,210); margin-bottom: 10px;}
        .sensor-value{font-size:1.8rem;font-weight:700; color:#333;}
        .sensor-label{font-size:1.1rem;font-weight:600; color:#555;}
        .navbar-brand{font-weight:700;letter-spacing:.5px;font-size:1.6rem;}
        .card-img-top {
            border-top-left-radius: 15px;
            border-top-right-radius: 15px;
            max-height: 200px; /* Adjust as needed */
            object-fit: cover;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-success shadow py-3">
    <div class="container">
        <span class="navbar-brand">🌱 Monitoring Tanaman Cabai</span>
    </div>
</nav>

<div class="container mt-5 pt-4 pb-5">
    <div class="row g-4 justify-content-center">

        <div class="col-md-4 col-lg-3 d-flex">
            <div class="card card-custom p-4 text-center w-100 h-100">
                <div class="sensor-icon"><i class="fas fa-seedling text-success"></i></div>
                <div class="sensor-label">Kelembapan Tanah</div>
                <div class="sensor-value"><?= $kelembapanTanah; ?></div>
                <div class="text-muted small">ADC Value (<?= $statusTanah; ?>)</div>
            </div>
        </div>

        <div class="col-md-4 col-lg-3 d-flex">
            <div class="card card-custom p-4 text-center w-100 h-100">
                <div class="sensor-icon"><i class="fas fa-thermometer-half text-danger"></i></div>
                <div class="sensor-label">Suhu</div>
                <div class="sensor-value"><?= number_format($suhu,1); ?> °C</div>
                <div class="text-muted small">&nbsp;</div> </div>
        </div>

        <div class="col-md-4 col-lg-3 d-flex">
            <div class="card card-custom p-4 text-center w-100 h-100">
                <div class="sensor-icon"><i class="fas fa-tint text-primary"></i></div>
                <div class="sensor-label">Kelembapan Udara</div>
                <div class="sensor-value"><?= number_format($kelembapanUdara,1); ?> %</div>
                <div class="text-muted small">&nbsp;</div> </div>
        </div>

        <div class="col-md-4 col-lg-3 d-flex">
            <div class="card card-custom p-4 text-center w-100 h-100">
                <div class="sensor-icon"><i class="fas fa-water text-info"></i></div>
                <div class="sensor-label">Status Pompa</div>
                <div class="sensor-value">
                    <span class="fw-bold text-<?= ($statusPompa === 'Hidup') ? 'success' : 'secondary'; ?>">
                        <?= $statusPompa; ?>
                    </span>
                </div>
                <div class="text-muted small">&nbsp;</div> </div>
        </div>

        <div class="col-md-4 col-lg-3 d-flex">
            <div class="card card-custom p-4 text-center w-100 h-100">
                <div class="sensor-icon"><i class="fas fa-clock text-secondary"></i></div>
                <div class="sensor-label">Waktu Update</div>
                <div class="sensor-value" style="font-size:1.2rem">
                    <?= date('d-m-Y H:i:s', strtotime($waktu)); ?>
                </div>
                <div class="text-muted small">&nbsp;</div> </div>
        </div>


    </div>

    <div class="row mt-5 justify-content-center">
        <div class="col-lg-10">
            <div class="card card-custom p-4">
                <h5 class="text-center mb-4 text-primary">Grafik Kelembapan Tanah (10 Data Terakhir)</h5>
                <canvas id="kelembapanChart" height="100"></canvas>
            </div>
        </div>
    </div>

    <div class="text-center mt-5">
        <p class="text-muted">⏱️ Halaman diperbarui otomatis setiap 5 detik</p>
        <a href="" class="btn btn-outline-success btn-sm">Refresh Manual</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const labels = <?= json_encode($label_waktu); ?>;
const data   = <?= json_encode($data_kelembapan); ?>;

new Chart(
    document.getElementById('kelembapanChart').getContext('2d'),
    {
        type:'line',
        data:{
            labels:labels,
            datasets:[{
                label:'Kelembapan Tanah (ADC)',
                data:data,
                fill:true,
                borderColor:'rgba(46,204,113,1)',
                backgroundColor:'rgba(46,204,113,.2)',
                borderWidth:2,
                pointRadius:4,
                tension:.3
            }]
        },
        options:{
            responsive:true,
            scales:{
                y:{
                    beginAtZero:true,
                    title: {
                        display: true,
                        text: 'ADC Value'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Waktu'
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed.y !== null) {
                                label += context.parsed.y + ' ADC';
                            }
                            return label;
                        }
                    }
                }
            }
        }
    }
);
</script>
</body>
</html>