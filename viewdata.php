<?php
require_once __DIR__ . '/connection.php';
// Jika file ini dibuka langsung, tampilkan halaman utamanya.
if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    header('Location: ' . listingUrl($_SERVER['QUERY_STRING'] ?? ''));
    exit;
}

$search = mb_substr(queryString('search'), 0, 100);
$predicates = ['Sangat Memuaskan', 'Memuaskan', 'Cukup', 'Kurang', 'Tidak Lulus'];
$filter = queryString('filter_predikat');
if (!in_array($filter, $predicates, true)) $filter = '';
$sort = strtolower(queryString('sort', 'desc')) === 'asc' ? 'asc' : 'desc';
$limit = (int) queryString('limit', '5');
if (!in_array($limit, [5, 10, 15], true)) $limit = 5;
$page = max(1, (int) queryString('page', '1'));
// Escape LIKE wildcards so search text is treated as a literal name.
$pattern = '%' . strtr($search, ['!' => '!!', '%' => '!%', '_' => '!_']) . '%';
$where = "WHERE nm_lengkap LIKE ? ESCAPE '!' AND (? = '' OR predikat = ?)";
$count = $conn->prepare("SELECT COUNT(*) FROM nilaimhsw $where");
$count->bind_param('sss', $pattern, $filter, $filter);
$count->execute();
$total = (int) $count->get_result()->fetch_row()[0];
$pages = max(1, (int) ceil($total / $limit));
$page = min($page, $pages);
$offset = ($page - 1) * $limit;
$order = strtoupper($sort);
$stmt = $conn->prepare("SELECT * FROM nilaimhsw $where ORDER BY nilai_akhir $order, nim ASC LIMIT ? OFFSET ?");
$stmt->bind_param('sssii', $pattern, $filter, $filter, $limit, $offset);
$stmt->execute();
$rows = $stmt->get_result();
$error = $_SESSION['error'] ?? '';
$success = $_SESSION['success'] ?? '';
$old = $_SESSION['old'] ?? [];
$editNim = queryString('edit');
$editing = null;
if ($editNim !== '') {
    $editStmt = $conn->prepare('SELECT * FROM nilaimhsw WHERE nim = ?');
    $editStmt->bind_param('s', $editNim);
    $editStmt->execute();
    $editing = $editStmt->get_result()->fetch_assoc();
    if (!$editing) $error = 'Data mahasiswa yang ingin diedit tidak ditemukan.';
}
$editValues = [];
if ($editing) {
    $editValues = $editing;
    if (($_SESSION['edit_old']['original_nim'] ?? null) === $editNim) {
        $editValues = array_merge($editValues, $_SESSION['edit_old']['values']);
    }
}
$initialModal = $editing ? 'edit' : ($_SESSION['modal'] ?? '');
if (!$initialModal && queryString('modal') === 'add') $initialModal = 'add';
$deleteValues = $_SESSION['delete_old'] ?? [];
unset($_SESSION['error'], $_SESSION['success'], $_SESSION['old'], $_SESSION['edit_old'], $_SESSION['modal'], $_SESSION['delete_old']);
