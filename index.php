<?php

// PROSES SIMPAN DATA
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $data = [
        "bulan" => $_POST['bulan'],
        "minggu" => $_POST['minggu'],
        "jenis" => $_POST['jenis'],
        "isi" => $_POST['isi']
    ];

    $jsonData = json_encode($data);

    $url = "https://script.google.com/macros/s/AKfycbwPnbsogbydVnA9n-J1_by9Vj6mGF8Giv62WehNJCbY3PH2DrImRIO4ARMvN1BP3z-l/exec";

    $options = [
        'http' => [
            'header'  => "Content-type: application/json",
            'method'  => 'POST',
            'content' => $jsonData
        ]
    ];

    $context = stream_context_create($options);

    $result = file_get_contents($url, false, $context);

    echo "Data berhasil disimpan!";
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Planner Konten</title>
</head>
<body>

<h2>Input Konten</h2>

<form method="POST">

    <label>Bulan</label>
    <select name="bulan">
        <option value="April">April</option>
        <option value="Mei">Mei</option>
        <option value="Juni">Juni</option>
        <option value="Juli">Juli</option>
        <option value="Agustus">Agustus</option>
        <option value="September">September</option>
        <option value="Oktober">Oktober</option>
    </select>

    <br><br>

    <label>Minggu</label>
    <select name="minggu">
        <option value="1">1</option>
        <option value="2">2</option>
        <option value="3">3</option>
        <option value="4">4</option>
    </select>

    <br><br>

    <label>Jenis Konten</label>
    <select name="jenis">
        <option value="Video">Video</option>
        <option value="Design">Design</option>
        <option value="Podcast">Podcast</option>
    </select>

    <br><br>

    <label>Isi Konten</label>
    <textarea name="isi"></textarea>

    <br><br>

    <button type="submit">Simpan</button>

</form>

</body>
</html>