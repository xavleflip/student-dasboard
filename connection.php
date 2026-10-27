<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
    $conn = mysqli_connect(getenv('DB_HOST') ?: 'localhost', getenv('DB_USER') ?: 'root', getenv('DB_PASSWORD') ?: '', '', (int) (getenv('DB_PORT') ?: 3306));
    mysqli_set_charset($conn, 'utf8mb4');
} catch (mysqli_sql_exception $error) {
    http_response_code(503);
    exit('Database belum dapat diakses. Aktifkan MySQL di Laragon dan periksa connection.php.');
}

session_start();

// Siapkan database dan tabel jika belum ada. Data lama tidak dihapus.
try {
    $conn->query("CREATE DATABASE IF NOT EXISTS dbmahasiswa CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $conn->select_db('dbmahasiswa');
    // Ganti nama tabel versi lama tanpa menghapus isi dan fotonya.
    $hasTable = $conn->query("SHOW TABLES LIKE 'nilaimhsw'")->num_rows > 0;
    $hasOldTable = $conn->query("SHOW TABLES LIKE 'nilai'")->num_rows > 0;
    if (!$hasTable && $hasOldTable) {
        $conn->query('RENAME TABLE nilai TO nilaimhsw');
    }
    $conn->query("CREATE TABLE IF NOT EXISTS nilaimhsw (
        nim CHAR(10) PRIMARY KEY,
        nm_lengkap VARCHAR(100) NOT NULL,
        photo VARCHAR(255) NULL,
        nilai_akhir DECIMAL(5,2) NOT NULL,
        nilai_huruf CHAR(1) NOT NULL,
        predikat VARCHAR(100) NOT NULL,
        created_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        modified_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    if ($conn->query("SHOW COLUMNS FROM nilaimhsw LIKE 'photo'")->num_rows === 0) {
        $conn->query('ALTER TABLE nilaimhsw ADD COLUMN photo VARCHAR(255) NULL AFTER nm_lengkap');
    }
} catch (mysqli_sql_exception $error) {
    http_response_code(503);
    exit('Database belum siap. Periksa pengaturan MySQL pada connection.php.');
}

// Menampilkan teks pada HTML, termasuk tanda kutip dan simbol.
function e($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

// Mengubah nilai angka menjadi nilai huruf dan predikat.
function convertNilaiAkhir($nilai) {
    if ($nilai <= 34.50) {
        return ['E', 'Tidak Lulus'];
    } elseif ($nilai <= 50.50) {
        return ['D', 'Kurang'];
    } elseif ($nilai <= 60.50) {
        return ['C', 'Cukup'];
    } elseif ($nilai <= 80.50) {
        return ['B', 'Memuaskan'];
    } else {
        return ['A', 'Sangat Memuaskan'];
    }
}

// Mengambil satu nilai dari alamat URL, misalnya search atau page.
function queryString($key, $default = '') {
    return isset($_GET[$key]) && is_string($_GET[$key]) ? trim($_GET[$key]) : $default;
}

// Membawa pilihan pencarian dan halaman setelah data disimpan atau dihapus.
function listingUrl($query = '') {
    parse_str($query, $params);
    $allowed = [];
    foreach (['search', 'filter_predikat', 'sort', 'limit', 'page'] as $key) {
        if (isset($params[$key]) && is_string($params[$key])) $allowed[$key] = $params[$key];
    }
    return 'view-nilai.php' . ($allowed ? '?' . http_build_query($allowed) : '');
}

// Membuat link untuk pindah halaman atau mengganti urutan nilai.
function pageUrl($changes = []) {
    global $search, $filter, $sort, $limit, $page;
    return '?' . http_build_query(array_merge(['search' => $search, 'filter_predikat' => $filter, 'sort' => $sort, 'limit' => $limit, 'page' => $page], $changes));
}

// Memeriksa ukuran dan jenis gambar, lalu memindahkannya ke folder uploads.
function uploadPhoto() {
    $savedPhoto = null;
    $photo = $_FILES['photo'] ?? null;
    if ($photo !== null && (!is_array($photo) || !isset($photo['error']) || is_array($photo['error']))) {
        throw new RuntimeException('Data foto tidak valid.');
    }
    if ($photo && $photo['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($photo['error'] !== UPLOAD_ERR_OK) throw new RuntimeException('Upload foto gagal. Batas foto 1 MB.');
        if ($photo['size'] > 1000000 || !is_uploaded_file($photo['tmp_name'])) throw new RuntimeException('Foto maksimal 1 MB.');
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($photo['tmp_name']);
        $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif'];
        if (!isset($extensions[$mime]) || @getimagesize($photo['tmp_name']) === false) {
            throw new RuntimeException('Foto harus berupa JPG, PNG, atau GIF yang valid.');
        }
        $directory = __DIR__ . '/uploads';
        if (!is_dir($directory) && !mkdir($directory, 0755, true)) throw new RuntimeException('Folder foto tidak dapat dibuat.');
        $savedPhoto = bin2hex(random_bytes(16)) . '.' . $extensions[$mime];
        if (!move_uploaded_file($photo['tmp_name'], $directory . '/' . $savedPhoto)) {
            $savedPhoto = null;
            throw new RuntimeException('Foto tidak dapat disimpan.');
        }
    }
    return $savedPhoto;
}

// Mencari alamat foto. Jika belum ada, tabel menampilkan huruf awal nama.
function photoUrl($filename) {
    if (!$filename || basename($filename) !== $filename || !is_file(__DIR__ . '/uploads/' . $filename)) return null;
    return 'uploads/' . rawurlencode($filename);
}

// Menghapus file foto yang sudah tidak dipakai oleh mahasiswa mana pun.
function removeUnusedPhoto($filename) {
    global $conn;
    if (!$filename || !preg_match('/^[a-f0-9]{32}\.(jpg|png|gif)$/', $filename)) return;
    $stmt = $conn->prepare('SELECT COUNT(*) FROM nilaimhsw WHERE photo = ?');
    $stmt->bind_param('s', $filename);
    $stmt->execute();
    if ((int) $stmt->get_result()->fetch_row()[0] === 0) {
        $path = __DIR__ . '/uploads/' . $filename;
        if (is_file($path) && !@unlink($path)) error_log('Tidak dapat membersihkan foto: ' . $filename);
    }
}

