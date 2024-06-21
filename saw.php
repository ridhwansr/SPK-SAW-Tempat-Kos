<?php
include 'koneksi.php';
include 'header.php';

$alternatif = array();
$nama_alternatif = array();
$ambil = $koneksi->query("SELECT * FROM alternatif ORDER BY kode_alternatif ASC");
while ($tiap = $ambil->fetch_assoc()) {
    $alternatif[] = $tiap;
    $nama_alternatif[$tiap['kode_alternatif']] = $tiap['nama_alternatif'];
}

$kriteria = array();
$atribut_kriteria = array();
$bobot_kriteria = array();
$ambil = $koneksi->query("SELECT * FROM kriteria ORDER BY kode_kriteria ASC");
while ($tiap = $ambil->fetch_assoc()) {
    $atribut_kriteria[$tiap['kode_kriteria']] = $tiap['atribut_kriteria'];
    $bobot_kriteria[$tiap['kode_kriteria']] = $tiap['bobot_kriteria'];
    $kriteria[] = $tiap;
}

$nilai = array();
$ambil = $koneksi->query("SELECT * FROM nilai LEFT JOIN crip ON nilai.id_crip=crip.id_crip ORDER BY id_nilai ASC");
while ($tiap = $ambil->fetch_assoc()) {
    $nilai[] = $tiap;
}

// echo "<pre>";
// print_r($alternatif);
// print_r($kriteria);
// print_r($nilai);
// echo "<pre>";

$analisa = array();
foreach ($alternatif as $key => $peralternatif) {
    $kode_alternatif = $peralternatif['kode_alternatif'];

    foreach ($kriteria as $key => $perkriteria) {
        $kode_kriteria = $perkriteria['kode_kriteria'];

        foreach ($nilai as $key => $pernilai) {
            if ($pernilai['kode_alternatif'] == $kode_alternatif && $pernilai['kode_kriteria'] == $kode_kriteria) {
                $analisa[$kode_alternatif][$kode_kriteria] = $pernilai['nilai_crip'];
            }
        }
    }
}

$nilai_kriteria = array();
foreach ($analisa as $kode_alternatif => $peralternatif) {
    foreach ($peralternatif as $kode_kriteria => $nilai) {
        $nilai_kriteria[$kode_kriteria][] = $nilai;
    }
}

$normalisasi = array();
foreach ($analisa as $kode_alternatif => $peralternatif) {
    foreach ($peralternatif as $kode_kriteria => $nilai) {
        if ($atribut_kriteria[$kode_kriteria] == 'cost') {
            $normalisasi[$kode_alternatif][$kode_kriteria] = min($nilai_kriteria[$kode_kriteria]) / $nilai;
        } else {
            $normalisasi[$kode_alternatif][$kode_kriteria] = $nilai / max($nilai_kriteria[$kode_kriteria]);
        }
    }
}

$perrangkingan = array();
foreach ($normalisasi as $kode_alternatif => $peralternatif) {
    $total = 0;
    foreach ($peralternatif as $kode_kriteria => $nilai_ternormalisasi) {
        $total += $nilai_ternormalisasi * $bobot_kriteria[$kode_kriteria];
    }
    $perrangkingan[$kode_alternatif] = $total;
}
arsort($perrangkingan);

// echo "<pre>";
// print_r($atribut_kriteria);
// print_r($nilai_kriteria);
// print_r($analisa);
// print_r($bobot_kriteria);
// print_r($normalisasi);
// print_r($perrangkingan);
// echo "<pre>";
?>


<div class="container">
    <div class="row">
        <div class="col-md-6">
            <h3>Alternatif</h3>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kode</th>
                        <th>Nama</th>
                        <th>Tipe</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($alternatif as $key => $value) : ?>
                        <tr>
                            <td><?php echo $key + 1; ?></td>
                            <td><?php echo $value['kode_alternatif']; ?></td>
                            <td><?php echo $value['nama_alternatif']; ?></td>
                            <td><?php echo $value['tipe_alternatif']; ?></td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        </div>
        <div class="col-md-6">
            <h3>Kriteria</h3>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kode</th>
                        <th>Nama</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($kriteria as $key => $value) : ?>
                        <tr>
                            <td><?php echo $key + 1; ?></td>
                            <td><?php echo $value['kode_kriteria']; ?></td>
                            <td><?php echo $value['nama_kriteria']; ?></td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        </div>
    </div>
    <h3>Nilai</h3>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>No</th>
                <th>Alternatif</th>
                <?php foreach ($kriteria as $key => $value) : ?>
                    <th><?php echo $value['nama_kriteria'] ?></th>
                <?php endforeach ?>
            </tr>
        </thead>
        <tbody><?php $nomor = 0 ?>
            <?php foreach ($analisa as $kode_alternatif => $peralternatif) : ?>
                <tr>
                    <td><?php echo $nomor += 1 ?></td>
                    <td><?php echo $nama_alternatif[$kode_alternatif] ?></td>
                    <?php foreach ($peralternatif as $key => $nilai) : ?>
                        <td><?php echo $nilai ?></td>
                    <?php endforeach ?>
                </tr>
            <?php endforeach ?>
        </tbody>
    </table>

    <h3>Perankingan</h3>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Ranking</th>
                <th>Alternatif</th>
                <th>Total Nilai</th>
            </tr>
        </thead>
        <tbody>
            <?php $ranking = 1; ?>
            <?php foreach ($perrangkingan as $kode_alternatif => $totalnilai) : ?>

                <tr>
                    <td><?php echo $ranking ?></td>
                    <td><?php echo $nama_alternatif[$kode_alternatif] ?></td>
                    <td><?php echo $totalnilai ?></td>
                </tr>
                <?php $ranking++; ?>
            <?php endforeach ?>
        </tbody>
    </table>
</div>

<?php
include 'footer.php';
?>