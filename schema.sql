-- ============================================================
-- DATABASE SCHEMA - PARLINE (Aplikasi Parkir UKK)
-- Database Name : ukk_parkir
-- Engine         : MySQL / MariaDB
-- Charset        : utf8mb4
-- ============================================================

-- Buat database (jika belum ada)
CREATE DATABASE IF NOT EXISTS `ukk_parkir`
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_general_ci;

USE `ukk_parkir`;

-- ============================================================
-- 1. TABEL tb_user
--    Menyimpan data pengguna sistem (Admin, Petugas, Owner).
-- ============================================================
CREATE TABLE `tb_user` (
  `id_user`       INT(11)      NOT NULL AUTO_INCREMENT,
  `nama_lengkap`  VARCHAR(100) NOT NULL,
  `username`      VARCHAR(50)  NOT NULL UNIQUE,
  `password`      VARCHAR(255) NOT NULL COMMENT 'Disimpan dalam format MD5',
  `role`          ENUM('admin','petugas','owner') NOT NULL DEFAULT 'petugas',
  `status_aktif`  TINYINT(1)   NOT NULL DEFAULT 1 COMMENT '1 = Aktif, 0 = Non-Aktif',
  PRIMARY KEY (`id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `tb_user` (`nama_lengkap`, `username`, `password`, `role`, `status_aktif`) VALUES
('Administrator', 'admin', '0192023a7bbd73250516f069df18b500', 'admin', 1),
('Petugas Satu', 'petugas', '0192023a7bbd73250516f069df18b500', 'petugas', 1),
('Owner Parkir', 'owner', '0192023a7bbd73250516f069df18b500', 'owner', 1);

-- ============================================================
-- 2. TABEL tb_tarif
-- ============================================================
CREATE TABLE `tb_tarif` (
  `id_tarif`         INT(11)      NOT NULL AUTO_INCREMENT,
  `jenis_kendaraan`  VARCHAR(50)  NOT NULL COMMENT 'Contoh: motor, mobil',
  `tarif_per_jam`    INT(11)      NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_tarif`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `tb_tarif` (`jenis_kendaraan`, `tarif_per_jam`) VALUES
('motor', 2000),
('mobil', 5000);
('lainnya', 12000)

-- ============================================================
-- 3. TABEL tb_area_parkir
--    Menyimpan data area/zona parkir beserta kapasitas terisi.
-- ============================================================
CREATE TABLE `tb_area_parkir` (
  `id_area`    INT(11)      NOT NULL AUTO_INCREMENT,
  `nama_area`  VARCHAR(100) NOT NULL,
  `kapasitas`  INT(11)      NOT NULL DEFAULT 50,
  `terisi`     INT(11)      NOT NULL DEFAULT 0 COMMENT 'Jumlah slot yang sedang terpakai',
  PRIMARY KEY (`id_area`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `tb_area_parkir` (`nama_area`, `kapasitas`, `terisi`) VALUES
('Lantai 1', 50, 0),
('Blok A - Depan', 50, 0),
('Blok B - Samping', 60, 0);

-- ============================================================
-- 4. TABEL tb_kendaraan
--    Menyimpan identitas kendaraan secara detail.
-- ============================================================
CREATE TABLE `tb_kendaraan` (
  `id_kendaraan`    INT(11)      NOT NULL AUTO_INCREMENT,
  `plat_nomor`      VARCHAR(20)  NOT NULL,
  `jenis_kendaraan` VARCHAR(50)  NOT NULL,
  `warna`           VARCHAR(50)  DEFAULT '-',
  `pemilik`         VARCHAR(100) DEFAULT '-',
  `id_user`         INT(11)      NOT NULL COMMENT 'Petugas yang mendaftarkan',
  PRIMARY KEY (`id_kendaraan`),
  FOREIGN KEY (`id_user`) REFERENCES `tb_user`(`id_user`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- 5. TABEL tb_transaksi
--    Transaksi parkir utama menggunakan id_parkir.
-- ============================================================
CREATE TABLE `tb_transaksi` (
  `id_parkir`       INT(11)      NOT NULL AUTO_INCREMENT,
  `id_kendaraan`    INT(11)      NOT NULL,
  `id_tarif`        INT(11)      NOT NULL,
  `id_area`         INT(11)      NOT NULL,
  `id_user`         INT(11)      NOT NULL COMMENT 'Petugas yang melayani',
  `waktu_masuk`     DATETIME     NOT NULL,
  `waktu_keluar`    DATETIME     DEFAULT NULL,
  `durasi_jam`      INT(11)      DEFAULT NULL,
  `biaya_total`     INT(11)      NOT NULL DEFAULT 0,
  `status`          ENUM('masuk','keluar') NOT NULL DEFAULT 'masuk',
  PRIMARY KEY (`id_parkir`),
  FOREIGN KEY (`id_kendaraan`) REFERENCES `tb_kendaraan`(`id_kendaraan`) ON DELETE CASCADE,
  FOREIGN KEY (`id_tarif`)     REFERENCES `tb_tarif`(`id_tarif`) ON DELETE CASCADE,
  FOREIGN KEY (`id_area`)      REFERENCES `tb_area_parkir`(`id_area`) ON DELETE CASCADE,
  FOREIGN KEY (`id_user`)      REFERENCES `tb_user`(`id_user`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- 6. TABEL tb_log_aktivitas
-- ============================================================
CREATE TABLE `tb_log_aktivitas` (
  `id_log`     INT(11)      NOT NULL AUTO_INCREMENT,
  `id_user`    INT(11)      NOT NULL,
  `aktivitas`  VARCHAR(255) NOT NULL,
  `waktu_aktivitas` DATETIME     NOT NULL,
  PRIMARY KEY (`id_log`),
  FOREIGN KEY (`id_user`) REFERENCES `tb_user`(`id_user`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- 7. TABEL tb_log
-- ============================================================
CREATE TABLE `tb_log` (
  `id_log`        INT(11)      NOT NULL AUTO_INCREMENT,
  `user_petugas`  VARCHAR(100) DEFAULT NULL,
  `aktivitas`     VARCHAR(255) NOT NULL,
  `waktu`         DATETIME     NOT NULL,
  PRIMARY KEY (`id_log`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- 8. TABEL sessions (Dibutuhkan Laravel untuk Session Driver Database)
-- ============================================================
CREATE TABLE `sessions` (
  `id` VARCHAR(255) NOT NULL,
  `user_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `ip_address` VARCHAR(45) NULL DEFAULT NULL,
  `user_agent` TEXT NULL DEFAULT NULL,
  `payload` LONGTEXT NOT NULL,
  `last_activity` INT NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
