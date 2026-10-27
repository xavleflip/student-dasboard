# Student Dashboard — Nilai Mahasiswa

Proyek UTS PHP dan MySQL untuk menampilkan data nilai mahasiswa. Tampilan memakai CSS AdminLTE, dengan mode gelap dan formulir tambah, edit, serta hapus melalui modal.

Repositori: [xavleflip/student-dasboard](https://github.com/xavleflip/student-dasboard).

## Fitur

- Foto kecil di sebelah nama mahasiswa, dalam kolom yang sama.
- Pencarian berdasarkan nama dan filter predikat melalui dropdown.
- Pengurutan Nilai Akhir naik/turun melalui panah pada judul kolom.
- Pilihan 5, 10, atau 15 data per halaman.
- Tombol Pertama, Sebelumnya, Berikutnya, Terakhir, nomor halaman, dan lompat ke halaman.
- Tambah, edit, dan hapus data melalui modal. Nilai huruf dan predikat dihitung otomatis.
- Upload foto JPG, PNG, atau GIF, maksimal 1.000.000 byte.

## Persiapan

Gunakan Laragon atau server lokal dengan PHP dan MySQL. Proyek diuji menggunakan PHP 8.3 dan MySQL 8.4. Aktifkan ekstensi PHP `mysqli`, `mbstring`, dan `fileinfo`.

Pengaturan awal pada `connection.php`:

| Pengaturan | Nilai awal |
| --- | --- |
| Host | `localhost` |
| Port | `3306` |
| Username | `root` |
| Password | kosong |
| Database | `dbmahasiswa` |
| Tabel | `nilaimhsw` |

Jika pengaturan MySQL berbeda, sesuaikan nilai awal di `connection.php`, atau gunakan environment variable `DB_HOST`, `DB_PORT`, `DB_USER`, dan `DB_PASSWORD`. File `.env` tidak dibaca otomatis. Akun database perlu izin membuat database/tabel dan mengubah struktur tabel.

## Cara menjalankan setelah clone

1. Aktifkan Apache dan MySQL di Laragon.
2. Buka terminal di folder web Laragon (`laragon/www`), lalu jalankan:

   ```bash
   git clone https://github.com/xavleflip/student-dasboard.git
   ```

3. Buka phpMyAdmin, pilih menu **Import**, lalu pilih file `student-dasboard/dbmahasiswa.sql` dan jalankan import. File ini membuat database `dbmahasiswa`, tabel `nilaimhsw`, dan 20 data demo.
4. Buka [http://localhost/student-dasboard/view-nilai.php](http://localhost/student-dasboard/view-nilai.php). Sesuaikan alamat jika nama folder atau port server berbeda.
5. Pastikan folder `uploads` dapat ditulis oleh PHP agar upload foto berhasil.

Tidak ada `index.php`; halaman utama adalah `view-nilai.php`. Semua CSS yang dipakai sudah ada di folder proyek, sehingga tidak perlu folder `dist` terpisah.

Alternatif menggunakan server bawaan PHP: jalankan `php -S localhost:8000` di folder proyek, aktifkan MySQL, lalu buka [http://localhost:8000/view-nilai.php](http://localhost:8000/view-nilai.php).

## Database dan data demo

`dbmahasiswa.sql` adalah export database demo berisi 20 mahasiswa fiktif, mencakup lima predikat. Coba pencarian `Putri`, filter masing-masing predikat, panah Nilai Akhir, dan limit 5/10/15 untuk demonstrasi.

File SQL tidak menghapus tabel dan tidak menimpa NIM yang sudah ada. Import ulang hanya menambahkan NIM demo yang belum ada. Aplikasi dapat membuat database dan tabel kosong tanpa import; import diperlukan untuk mengisi data demo.

Untuk pengguna versi lama yang masih memakai tabel `nilai`, **buka aplikasi sekali sebelum import SQL**. Aplikasi otomatis mengganti nama `nilai` menjadi `nilaimhsw` jika tabel baru belum ada, dengan mempertahankan isi dan nama file foto. Jika kedua tabel sudah ada, aplikasi memakai `nilaimhsw` dan tabel lama tetap disimpan.

Gambar `uploads/foto-demo-*.png` adalah avatar ilustrasi untuk contoh, bukan foto orang sebenarnya, dan disertakan dalam Git. Foto upload pribadi diabaikan oleh Git; untuk memindahkan data pribadi ke perangkat lain, export database dan salin file foto terkait secara terpisah.

## Isi file

| File | Kegunaan |
| --- | --- |
| `connection.php` | Koneksi, penyiapan tabel, dan fungsi yang dipakai bersama |
| `view-nilai.php` | Halaman utama, tabel, navigasi, dan tampilan modal |
| `viewdata.php` | Query pencarian, filter, sorting, dan pagination |
| `nilaiui.php` | Formulir modal dan proses menyimpan data |
| `editnilai.php` | Memanggil proses edit |
| `hapusnilai.php` | Menghapus data mahasiswa |
| `adminlte.min.css` | CSS AdminLTE |
| `dbmahasiswa.sql` | Export database demo |
| `uploads/` | Gambar demo dan hasil upload |

Proyek ini digunakan untuk pembelajaran di server lokal. Belum tersedia login dan pembatasan akses pengguna. AdminLTE menggunakan lisensi MIT; keterangan lisensinya tetap ada dalam file CSS.
