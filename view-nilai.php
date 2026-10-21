<?php
require __DIR__ . '/viewdata.php';
?>
<!doctype html>
<html lang="id" data-bs-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="color-scheme" content="dark">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Nilai Mahasiswa</title>
    <link rel="stylesheet" href="adminlte.min.css">
    <style>
/* Tampilan halaman sederhana. CSS tabel dan tombol diimpor dari dist. */
body { margin: 0; background: #15191f; color: #e2e8f0; font-family: Arial, sans-serif; }
.halaman { width: 95%; max-width: 1250px; margin: 28px auto; }
.judul-halaman { display: flex; align-items: center; justify-content: space-between; gap: 16px; margin-bottom: 20px; }
.judul-halaman h1 { font-size: 24px; margin: 0; }
.daftar-mahasiswa { background: #212529; border: 1px solid #3b424d; }
.daftar-mahasiswa .card-header { border-bottom: 1px solid #3b424d; }
.daftar-mahasiswa .card-footer { border-top: 1px solid #3b424d; }
@media (max-width: 600px) { .judul-halaman { align-items: start; flex-direction: column; } }
.student { display: flex; align-items: center; gap: 12px; min-width: 200px; }
.avatar { width: 42px; height: 42px; flex-shrink: 0; border-radius: 50%; object-fit: cover; }
.placeholder-avatar { display: inline-flex; align-items: center; justify-content: center; background: #283c58; color: #b9d4ff; font-weight: 700; }
.table > :not(caption) > * > * { padding: 14px 16px; }
.table th { white-space: nowrap; }
.sort-link { text-decoration: none; }
.page-input { width: 76px; }
body.modal-open { overflow: hidden; }
.student-modal { width: min(520px, calc(100% - 32px)); max-height: calc(100dvh - 32px); margin: auto; padding: 24px; border: 1px solid #49515d; border-radius: 6px; color: #e2e8f0; background: #212529; box-shadow: 0 5px 20px #0003; overflow-y: auto; }
.student-modal::backdrop { background: #000a; }
.modal-heading { display: flex; align-items: start; justify-content: space-between; gap: 16px; margin-bottom: 20px; }
.modal-heading h2 { font-size: 1.25rem; margin: 0 0 6px; }
.modal-heading p, .modal-help { color: #b0bac9; font-size: .875rem; margin: 0; }
.modal-close { border: 0; background: transparent; color: #b0bac9; font-size: 1.75rem; line-height: 1; padding: 2px 6px; cursor: pointer; }
.student-modal label { display: block; font-weight: 600; margin: 16px 0 6px; }
.student-modal input:not([type="hidden"]) { display: block; width: 100%; box-sizing: border-box; padding: 10px 12px; border: 1px solid #49515d; border-radius: 6px; background: #171c23; color: #e2e8f0; font: inherit; }
.student-modal input:focus-visible, .modal-close:focus-visible { outline: 2px solid #4275e0; outline-offset: 2px; }
.modal-help { margin-top: 6px; }
.modal-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 24px; }


/* Warna kontrol mengikuti mode gelap, termasuk tombol pilih foto. */
.student-modal input::file-selector-button { background: #343d49; color: #e2e8f0; border: 1px solid #596575; border-radius: 4px; padding: 4px 8px; margin-right: 10px; cursor: pointer; }
.btn-outline-primary { --bs-btn-color: #8ab4ff; --bs-btn-border-color: #6a94d0; }
.btn-outline-danger { --bs-btn-color: #ff9aa4; --bs-btn-border-color: #d77883; }
.btn-outline-secondary { color: #c1cad7; border-color: #667180; }
.btn-outline-secondary:hover { background: #495360; color: #fff; }
</style>
</head>
<body>
<main class="halaman">
<header class="judul-halaman">
    <h1>Data Nilai Mahasiswa</h1>
    <button type="button" class="btn btn-primary" data-open-add aria-haspopup="dialog" aria-controls="add-dialog">+ Tambah mahasiswa</button>
</header>

<?php if ($success): ?>
    <div class="alert alert-success" role="status"><?= e($success) ?></div>
<?php endif ?>
<?php if ($error && !$initialModal): ?>
    <div class="alert alert-danger" role="alert"><?= e($error) ?></div>
<?php endif ?>

<section class="daftar-mahasiswa" aria-label="Daftar nilai mahasiswa">
    <div class="card-header p-3">
<form method="get" class="row g-3 align-items-end">
<div class="col-md-4"><label for="search" class="form-label">Cari nama lengkap</label><input type="search" id="search" name="search" class="form-control" placeholder="Ketik nama mahasiswa…" value="<?= e($search) ?>"></div>
<div class="col-md-3"><label for="predicate" class="form-label">Predikat</label><select id="predicate" name="filter_predikat" class="form-select"><option value="">Semua predikat</option><?php foreach ($predicates as $item): ?><option <?= $filter === $item ? 'selected' : '' ?> value="<?= e($item) ?>"><?= e($item) ?></option><?php endforeach ?></select></div>
<div class="col-md-2"><label for="limit" class="form-label">Data per halaman</label><select id="limit" name="limit" class="form-select"><?php foreach ([1, 5, 15, 25] as $size): ?><option value="<?= $size ?>" <?= $limit === $size ? 'selected' : '' ?>><?= $size ?></option><?php endforeach ?></select></div>
<input type="hidden" name="sort" value="<?= e($sort) ?>">
<div class="col-md-3 d-flex gap-2"><button class="btn btn-primary" type="submit">Terapkan</button><a class="btn btn-outline-secondary" href="view-nilai.php">Reset</a></div>
</form>
</div>

    <div class="table-responsive">
<table class="table table-hover align-middle mb-0">
<thead><tr><th>No.</th><th>NIM</th><th>Nama Lengkap</th><th aria-sort="<?= $sort === 'asc' ? 'ascending' : 'descending' ?>"><a class="sort-link" href="<?= e(pageUrl(['sort' => $sort === 'asc' ? 'desc' : 'asc', 'page' => 1])) ?>" title="Urutkan nilai <?= $sort === 'asc' ? 'tertinggi ke terendah' : 'terendah ke tertinggi' ?>">Nilai Akhir <?= $sort === 'asc' ? '↑' : '↓' ?></a></th><th>Nilai Huruf</th><th>Predikat</th><th class="d-none d-xxl-table-cell">Tgl Dibuat</th><th class="d-none d-xxl-table-cell">Tgl Diubah</th><th>Aksi</th></tr></thead>
<tbody>
<?php $number = $offset; while ($row = $rows->fetch_assoc()): ?>
<tr>
<td><?= ++$number ?></td><td><?= e($row['nim']) ?></td>
<td><div class="student"><?php if ($photo = photoUrl($row['photo'])): ?><img class="avatar" src="<?= e($photo) ?>" alt="Foto <?= e($row['nm_lengkap']) ?>" loading="lazy"><?php else: ?><span class="avatar placeholder-avatar" aria-label="Belum ada foto"><?= e(mb_strtoupper(mb_substr($row['nm_lengkap'], 0, 1))) ?></span><?php endif ?><span class="fw-semibold"><?= e($row['nm_lengkap']) ?></span></div></td>
<td class="fw-semibold"><?= e(number_format((float) $row['nilai_akhir'], 2, ',', '.')) ?></td><td><?= e($row['nilai_huruf']) ?></td><td><span class="badge text-bg-secondary border"><?= e($row['predikat']) ?></span></td><td class="text-body-secondary small d-none d-xxl-table-cell"><?= e($row['created_date']) ?></td><td class="text-body-secondary small d-none d-xxl-table-cell"><?= e($row['modified_date']) ?></td>
<td><div class="d-flex gap-2">
<button type="button" class="btn btn-outline-primary btn-sm edit-button" aria-haspopup="dialog" aria-controls="edit-dialog" aria-label="Edit <?= e($row['nm_lengkap']) ?>" data-student="<?= e(json_encode(['nim' => $row['nim'], 'nm_lengkap' => $row['nm_lengkap'], 'nilai_akhir' => $row['nilai_akhir']], JSON_INVALID_UTF8_SUBSTITUTE)) ?>">Edit</button>
<button type="button" class="btn btn-outline-danger btn-sm delete-button" aria-haspopup="dialog" aria-controls="delete-dialog" aria-label="Hapus <?= e($row['nm_lengkap']) ?>" data-student="<?= e(json_encode(['nim' => $row['nim'], 'nm_lengkap' => $row['nm_lengkap']], JSON_INVALID_UTF8_SUBSTITUTE)) ?>">Hapus</button></div></td>
</tr>
<?php endwhile ?>
<?php if ($total === 0): ?><tr><td colspan="9" class="text-center py-5 text-body-secondary">Data mahasiswa tidak ditemukan.</td></tr><?php endif ?>
</tbody></table>
</div>

    <div class="card-footer p-3 d-flex flex-wrap justify-content-between align-items-center gap-3">
<p class="mb-0 text-body-secondary small">Menampilkan <?= $total ? $offset + 1 : 0 ?>–<?= min($offset + $limit, $total) ?> dari <?= $total ?> mahasiswa · Halaman <?= $page ?> dari <?= $pages ?></p>
<nav aria-label="Navigasi halaman"><ul class="pagination pagination-sm mb-0">
<?php if ($page > 1): ?><li class="page-item"><a class="page-link" href="<?= e(pageUrl(['page' => $page - 1])) ?>">Sebelumnya</a></li><?php endif ?>
<?php
$visible = array_unique(array_merge([1], range(max(1, $page - 2), min($pages, $page + 2)), [$pages]));
$previous = 0;
foreach ($visible as $p):
if ($previous && $p > $previous + 1): ?><li class="page-item disabled"><span class="page-link">…</span></li><?php endif ?>
<li class="page-item <?= $p === $page ? 'active' : '' ?>"><a class="page-link" <?= $p === $page ? 'aria-current="page"' : '' ?> href="<?= e(pageUrl(['page' => $p])) ?>"><?= $p ?></a></li>
<?php $previous = $p; endforeach ?>
<?php if ($page < $pages): ?><li class="page-item"><a class="page-link" href="<?= e(pageUrl(['page' => $page + 1])) ?>">Berikutnya</a></li><?php endif ?>
</ul></nav>
<form method="get" class="d-flex gap-2 align-items-center">
<input type="hidden" name="search" value="<?= e($search) ?>"><input type="hidden" name="filter_predikat" value="<?= e($filter) ?>"><input type="hidden" name="sort" value="<?= e($sort) ?>"><input type="hidden" name="limit" value="<?= $limit ?>">
<label for="page" class="small text-nowrap">Ke halaman</label><input class="form-control form-control-sm page-input" id="page" type="number" name="page" min="1" max="<?= $pages ?>" value="<?= $page ?>"><button class="btn btn-outline-primary btn-sm">Buka</button>
</form>
</div>

</section>

<?php require __DIR__ . '/nilaiui.php'; ?>
</main>
<script>
// Membuka dan menutup modal dengan dialog HTML bawaan browser.
let opener = null;
const dialogs = document.querySelectorAll('dialog.student-modal');

function openModal(dialog, button = null) {
    opener = button;
    const error = dialog.querySelector('[role="alert"]');
    if (error) error.hidden = button !== null;
    dialog.showModal();
    document.body.classList.add('modal-open');
}

dialogs.forEach(dialog => {
    dialog.querySelectorAll('[data-close-modal]').forEach(button => {
        button.addEventListener('click', () => dialog.close());
    });
    dialog.addEventListener('close', () => {
        document.body.classList.remove('modal-open');
        const url = new URL(window.location.href);
        if (url.searchParams.has('edit') || url.searchParams.has('modal')) {
            url.searchParams.delete('edit');
            url.searchParams.delete('modal');
            url.hash = '';
            history.replaceState(null, '', url);
        }
        if (opener) opener.focus();
    });
});
const initial = document.querySelector('dialog[data-auto-open="true"]');
if (initial) openModal(initial);

// Tombol tambah membuka formulir kosong.
document.querySelectorAll('[data-open-add]').forEach(button => {
    button.addEventListener('click', () => {
        const dialog = document.getElementById('add-dialog');
        dialog.querySelector('form').reset();
        openModal(dialog, button);
    });
});

// Isi formulir edit dari data pada baris tabel.
document.querySelectorAll('.edit-button').forEach(button => {
    button.addEventListener('click', () => {
        const student = JSON.parse(button.dataset.student);
        const dialog = document.getElementById('edit-dialog');
        dialog.querySelector('form').reset();
        document.getElementById('edit-original-nim').value = student.nim;
        document.getElementById('edit-nim').value = student.nim;
        document.getElementById('edit-name').value = student.nm_lengkap;
        document.getElementById('edit-score').value = student.nilai_akhir;
        openModal(dialog, button);
    });
});

// Tampilkan nama dan NIM sebelum data dihapus.
document.querySelectorAll('.delete-button').forEach(button => {
    button.addEventListener('click', () => {
        const student = JSON.parse(button.dataset.student);
        document.getElementById('delete-nim').value = student.nim;
        document.getElementById('delete-name').value = student.nm_lengkap;
        document.getElementById('delete-student').textContent = student.nm_lengkap + ' (' + student.nim + ')';
        openModal(document.getElementById('delete-dialog'), button);
    });
});

</script>
</body>
</html>
