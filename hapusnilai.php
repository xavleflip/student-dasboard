<?php
require_once __DIR__ . '/connection.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Gunakan tombol hapus pada daftar mahasiswa.');
}
$returnUrl = listingUrl(is_string($_POST['return_query'] ?? null) ? $_POST['return_query'] : '');
$photo = null;
$inTransaction = false;
$deleted = false;
try {
    $nim = is_string($_POST['nim'] ?? null) ? trim($_POST['nim']) : '';
    if ($nim === '' || mb_strlen($nim) > 10) throw new RuntimeException('NIM tidak valid.');
    $conn->begin_transaction();
    $inTransaction = true;
    $stmt = $conn->prepare('SELECT photo FROM nilaimhsw WHERE nim = ? FOR UPDATE');
    $stmt->bind_param('s', $nim);
    $stmt->execute();
    $record = $stmt->get_result()->fetch_assoc();
    if (!$record) throw new RuntimeException('Data mahasiswa sudah tidak ditemukan.');
    $photo = $record['photo'];
    $stmt = $conn->prepare('DELETE FROM nilaimhsw WHERE nim = ?');
    $stmt->bind_param('s', $nim);
    $stmt->execute();
    $conn->commit();
    $inTransaction = false;
    $deleted = true;
    $_SESSION['success'] = 'Data mahasiswa berhasil dihapus.';
} catch (RuntimeException $error) {
    if ($inTransaction) $conn->rollback();
    $_SESSION['delete_old'] = ['nim' => is_string($_POST['nim'] ?? null) ? $_POST['nim'] : '', 'nm_lengkap' => is_string($_POST['nm_lengkap'] ?? null) ? $_POST['nm_lengkap'] : ''];
    $_SESSION['modal'] = 'delete';
    $_SESSION['error'] = $error instanceof mysqli_sql_exception ? 'Data gagal dihapus. Silakan coba kembali.' : $error->getMessage();
}
if ($deleted) removeUnusedPhoto($photo);
header('Location: ' . $returnUrl, true, 303);
exit;
