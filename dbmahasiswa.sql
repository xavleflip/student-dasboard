-- Database demo UTS: 20 mahasiswa fiktif dan avatar ilustrasi.
-- Buka aplikasi sekali sebelum import jika masih memakai tabel lama nilai.
-- Tidak menghapus tabel; NIM yang sudah ada tidak ditimpa.

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `dbmahasiswa` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;

USE `dbmahasiswa`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE IF NOT EXISTS `nilaimhsw` (
  `nim` char(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nm_lengkap` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `photo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nilai_akhir` decimal(5,2) NOT NULL,
  `nilai_huruf` char(1) COLLATE utf8mb4_unicode_ci NOT NULL,
  `predikat` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`nim`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT  IGNORE INTO `nilaimhsw` VALUES ('DEMO000001','Aditya Pratama','foto-demo-1.png',95.00,'A','Sangat Memuaskan','2026-10-27 00:01:32','2026-10-27 00:01:32'),('DEMO000002','Aulia Putri','foto-demo-2.png',82.00,'A','Sangat Memuaskan','2026-10-27 00:01:32','2026-10-27 00:01:32'),('DEMO000003','Bagas Saputra','foto-demo-3.png',88.75,'A','Sangat Memuaskan','2026-10-27 00:01:32','2026-10-27 00:01:32'),('DEMO000004','Bima Ramadhan','foto-demo-4.png',100.00,'A','Sangat Memuaskan','2026-10-27 00:01:32','2026-10-27 00:01:32'),('DEMO000005','Citra Lestari','foto-demo-5.png',80.50,'B','Memuaskan','2026-10-27 00:01:32','2026-10-27 00:01:32'),('DEMO000006','Dewi Anggraini','foto-demo-1.png',75.00,'B','Memuaskan','2026-10-27 00:01:32','2026-10-27 00:01:32'),('DEMO000007','Dimas Maulana','foto-demo-2.png',65.25,'B','Memuaskan','2026-10-27 00:01:32','2026-10-27 00:01:32'),('DEMO000008','Fajar Hidayat','foto-demo-3.png',78.00,'B','Memuaskan','2026-10-27 00:01:32','2026-10-27 00:01:32'),('DEMO000009','Farah Nabila','foto-demo-4.png',60.50,'C','Cukup','2026-10-27 00:01:32','2026-10-27 00:01:32'),('DEMO000010','Gilang Permana','foto-demo-5.png',55.00,'C','Cukup','2026-10-27 00:01:32','2026-10-27 00:01:32'),('DEMO000011','Hana Safitri','foto-demo-1.png',58.25,'C','Cukup','2026-10-27 00:01:32','2026-10-27 00:01:32'),('DEMO000012','Intan Permata','foto-demo-2.png',51.00,'C','Cukup','2026-10-27 00:01:32','2026-10-27 00:01:32'),('DEMO000013','Kevin Wijaya','foto-demo-3.png',50.50,'D','Kurang','2026-10-27 00:01:32','2026-10-27 00:01:32'),('DEMO000014','Laras Kusuma','foto-demo-4.png',42.00,'D','Kurang','2026-10-27 00:01:32','2026-10-27 00:01:32'),('DEMO000015','Nadia Putri','foto-demo-5.png',47.75,'D','Kurang','2026-10-27 00:01:32','2026-10-27 00:01:32'),('DEMO000016','Naufal Akbar','foto-demo-1.png',35.00,'D','Kurang','2026-10-27 00:01:32','2026-10-27 00:01:32'),('DEMO000017','Raka Firmansyah','foto-demo-2.png',34.50,'E','Tidak Lulus','2026-10-27 00:01:32','2026-10-27 00:01:32'),('DEMO000018','Salsa Amelia','foto-demo-3.png',20.00,'E','Tidak Lulus','2026-10-27 00:01:32','2026-10-27 00:01:32'),('DEMO000019','Tegar Setiawan','foto-demo-4.png',30.25,'E','Tidak Lulus','2026-10-27 00:01:32','2026-10-27 00:01:32'),('DEMO000020','Vina Maharani','foto-demo-5.png',0.00,'E','Tidak Lulus','2026-10-27 00:01:32','2026-10-27 00:01:32');
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
