<?php
require_once __DIR__ . '/connection.php';

// Edit memakai proses simpan yang sama; dibedakan oleh $isEdit.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $savedPhoto = null;
    $isEdit = $isEdit ?? false;
    $originalNim = is_string($_POST['original_nim'] ?? null) ? $_POST['original_nim'] : '';
    $returnUrl = listingUrl(is_string($_POST['return_query'] ?? null) ? $_POST['return_query'] : '');
    $oldPhoto = null;
    $inTransaction = false;
    $saved = false;
    try {
        $values = [];
        foreach (['nim', 'nm_lengkap', 'nilai_akhir'] as $field) {
            if (!is_string($_POST[$field] ?? null) || trim($_POST[$field]) === '') {
                throw new RuntimeException('NIM, nama lengkap, dan nilai akhir wajib diisi.');
            }
            $values[$field] = trim($_POST[$field]);
        }
        if (mb_strlen($values['nim']) > 10 || mb_strlen($values['nm_lengkap']) > 100) {
            throw new RuntimeException('NIM maksimal 10 karakter dan nama maksimal 100 karakter.');
        }
        if (!preg_match('/^\d{1,3}(?:\.\d{1,2})?$/', $values['nilai_akhir']) || (float) $values['nilai_akhir'] > 100) {
            throw new RuntimeException('Nilai akhir harus 0–100, maksimal dua angka desimal.');
        }
        $conn->begin_transaction();
        $inTransaction = true;
        if ($isEdit) {
            $existing = $conn->prepare('SELECT photo FROM nilaimhsw WHERE nim = ? FOR UPDATE');
            $existing->bind_param('s', $originalNim);
            $existing->execute();
            $record = $existing->get_result()->fetch_assoc();
            if (!$record) throw new RuntimeException('Data mahasiswa sudah tidak ditemukan.');
            $oldPhoto = $record['photo'];
        }
        $savedPhoto = uploadPhoto();
        $score = (float) $values['nilai_akhir'];
        [$letter, $predicate] = convertNilaiAkhir($score);
        $photoToSave = $savedPhoto ?? $oldPhoto;
        if ($isEdit) {
            $stmt = $conn->prepare('UPDATE nilaimhsw SET nim = ?, nm_lengkap = ?, nilai_akhir = ?, nilai_huruf = ?, predikat = ?, photo = ?, modified_date = CURRENT_TIMESTAMP WHERE nim = ?');
            $stmt->bind_param('ssdssss', $values['nim'], $values['nm_lengkap'], $score, $letter, $predicate, $photoToSave, $originalNim);
        } else {
            $stmt = $conn->prepare('INSERT INTO nilaimhsw (nim, nm_lengkap, nilai_akhir, nilai_huruf, predikat, photo) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->bind_param('ssdsss', $values['nim'], $values['nm_lengkap'], $score, $letter, $predicate, $photoToSave);
        }
        $stmt->execute();
        $conn->commit();
        $inTransaction = false;
        $saved = true;
        unset($_SESSION['old'], $_SESSION['edit_old']);
        $_SESSION['success'] = $isEdit ? 'Data mahasiswa berhasil diperbarui.' : 'Data mahasiswa berhasil disimpan.';
    } catch (RuntimeException $error) {
        if ($inTransaction) $conn->rollback();
        if ($savedPhoto !== null) removeUnusedPhoto($savedPhoto);
        $_SESSION['error'] = $error instanceof mysqli_sql_exception
            ? ($error->getCode() === 1062 ? 'NIM sudah terdaftar. Gunakan NIM lain.' : 'Data gagal disimpan. Periksa struktur database.')
            : $error->getMessage();
        if ($isEdit) {
            $_SESSION['edit_old'] = ['original_nim' => $originalNim, 'values' => $values ?? []];
            $returnUrl .= (strpos($returnUrl, '?') === false ? '?' : '&') . http_build_query(['edit' => $originalNim]);
            $returnUrl .= '#edit-dialog';
        } else {
            $_SESSION['old'] = $values ?? [];
            $_SESSION['modal'] = 'add';
        }
    }
    if ($saved && $savedPhoto !== null && $oldPhoto !== null) removeUnusedPhoto($oldPhoto);
    header('Location: ' . $returnUrl, true, 303);
    exit;
}

// Alamat ini juga bisa dipakai untuk membuka modal tambah.
if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    header('Location: view-nilai.php?modal=add');
    exit;
}
?>
<dialog id="add-dialog" class="student-modal" aria-labelledby="add-title" data-auto-open="<?= $initialModal === 'add' ? 'true' : 'false' ?>">
<div class="modal-heading"><div><h2 id="add-title">Tambah data mahasiswa</h2><p>Masukkan identitas, nilai, dan foto mahasiswa.</p></div><button type="button" class="modal-close" data-close-modal aria-label="Tutup modal tambah">&times;</button></div>
<form id="add-form" action="nilaiui.php" method="post" enctype="multipart/form-data">
<?php $prefix = 'add'; $values = $old; ?>
<input type="hidden" name="return_query" value="<?= e(ltrim(pageUrl(), '?')) ?>">
<?php if ($prefix === 'edit'): ?><input type="hidden" name="original_nim" id="edit-original-nim" value="<?= e($editNim) ?>"><?php endif ?>
<?php if ($initialModal === $prefix && $error): ?><div class="alert alert-danger" role="alert"><?= e($error) ?> Pilih ulang foto jika diperlukan.</div><?php endif ?>
<label for="<?= $prefix ?>-nim">NIM</label>
<input id="<?= $prefix ?>-nim" name="nim" maxlength="10" value="<?= e($values['nim'] ?? '') ?>" required autofocus>
<label for="<?= $prefix ?>-name">Nama lengkap</label>
<input id="<?= $prefix ?>-name" name="nm_lengkap" maxlength="100" value="<?= e($values['nm_lengkap'] ?? '') ?>" required>
<label for="<?= $prefix ?>-score">Nilai akhir</label>
<input id="<?= $prefix ?>-score" type="number" name="nilai_akhir" min="0" max="100" step="0.01" value="<?= e($values['nilai_akhir'] ?? '') ?>" required>
<p class="modal-help">Nilai huruf dan predikat dihitung otomatis.</p>
<label for="<?= $prefix ?>-photo"><?= $prefix === 'edit' ? 'Ganti foto' : 'Foto mahasiswa' ?> (opsional)</label>
<input id="<?= $prefix ?>-photo" type="file" name="photo" accept="image/jpeg,image/png,image/gif">
<p class="modal-help">JPG, PNG, atau GIF, maksimal 1 MB. <?= $prefix === 'edit' ? 'Kosongkan untuk mempertahankan foto lama.' : '' ?></p>
<div class="modal-actions"><button type="button" class="btn btn-outline-secondary" data-close-modal>Batal</button><button type="submit" class="btn btn-primary"><?= $prefix === 'edit' ? 'Simpan perubahan' : 'Simpan mahasiswa' ?></button></div>


</form>
</dialog>
<dialog id="edit-dialog" class="student-modal" aria-labelledby="edit-title" data-auto-open="<?= $initialModal === 'edit' ? 'true' : 'false' ?>">
<div class="modal-heading"><div><h2 id="edit-title">Edit data mahasiswa</h2><p>Perbarui identitas, nilai, atau foto mahasiswa.</p></div><button type="button" class="modal-close" data-close-modal aria-label="Tutup modal edit">&times;</button></div>
<form id="edit-form" action="editnilai.php" method="post" enctype="multipart/form-data">
<?php $prefix = 'edit'; $values = $editValues; ?>
<input type="hidden" name="return_query" value="<?= e(ltrim(pageUrl(), '?')) ?>">
<?php if ($prefix === 'edit'): ?><input type="hidden" name="original_nim" id="edit-original-nim" value="<?= e($editNim) ?>"><?php endif ?>
<?php if ($initialModal === $prefix && $error): ?><div class="alert alert-danger" role="alert"><?= e($error) ?> Pilih ulang foto jika diperlukan.</div><?php endif ?>
<label for="<?= $prefix ?>-nim">NIM</label>
<input id="<?= $prefix ?>-nim" name="nim" maxlength="10" value="<?= e($values['nim'] ?? '') ?>" required autofocus>
<label for="<?= $prefix ?>-name">Nama lengkap</label>
<input id="<?= $prefix ?>-name" name="nm_lengkap" maxlength="100" value="<?= e($values['nm_lengkap'] ?? '') ?>" required>
<label for="<?= $prefix ?>-score">Nilai akhir</label>
<input id="<?= $prefix ?>-score" type="number" name="nilai_akhir" min="0" max="100" step="0.01" value="<?= e($values['nilai_akhir'] ?? '') ?>" required>
<p class="modal-help">Nilai huruf dan predikat dihitung otomatis.</p>
<label for="<?= $prefix ?>-photo"><?= $prefix === 'edit' ? 'Ganti foto' : 'Foto mahasiswa' ?> (opsional)</label>
<input id="<?= $prefix ?>-photo" type="file" name="photo" accept="image/jpeg,image/png,image/gif">
<p class="modal-help">JPG, PNG, atau GIF, maksimal 1 MB. <?= $prefix === 'edit' ? 'Kosongkan untuk mempertahankan foto lama.' : '' ?></p>
<div class="modal-actions"><button type="button" class="btn btn-outline-secondary" data-close-modal>Batal</button><button type="submit" class="btn btn-primary"><?= $prefix === 'edit' ? 'Simpan perubahan' : 'Simpan mahasiswa' ?></button></div>


</form>
</dialog>
<dialog id="delete-dialog" class="student-modal" aria-labelledby="delete-title" aria-describedby="delete-description" data-auto-open="<?= $initialModal === 'delete' ? 'true' : 'false' ?>">
<div class="modal-heading"><h2 id="delete-title">Hapus data mahasiswa?</h2><button type="button" class="modal-close" data-close-modal aria-label="Tutup modal hapus">&times;</button></div>
<form id="delete-form" action="hapusnilai.php" method="post">
<?php if ($initialModal === 'delete' && $error): ?><div class="alert alert-danger" role="alert"><?= e($error) ?></div><?php endif ?>
<input type="hidden" name="return_query" value="<?= e(ltrim(pageUrl(), '?')) ?>">
<input type="hidden" id="delete-nim" name="nim" value="<?= e($deleteValues['nim'] ?? '') ?>">
<input type="hidden" id="delete-name" name="nm_lengkap" value="<?= e($deleteValues['nm_lengkap'] ?? '') ?>">
<p id="delete-description">Data <strong id="delete-student"><?= e(($deleteValues['nm_lengkap'] ?? '') . ' (' . ($deleteValues['nim'] ?? '') . ')') ?></strong> beserta fotonya akan dihapus. Tindakan ini tidak dapat dibatalkan.</p>
<div class="modal-actions"><button type="button" class="btn btn-outline-secondary" data-close-modal autofocus>Batal</button><button type="submit" class="btn btn-danger">Ya, hapus mahasiswa</button></div>
</form>
</dialog>

