<?php
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Gunakan modal edit pada halaman data mahasiswa.');
}
$isEdit = true;
require __DIR__ . '/nilaiui.php';
