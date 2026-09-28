-- Jalankan HANYA jika database karcis versi sebelumnya sudah terpasang
USE karcis;
SET NAMES utf8mb4;

-- Akun admin panel (login: admin@karcis.id / admin123 — WAJIB diganti setelah login)
CREATE TABLE IF NOT EXISTS admins (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin','staff') NOT NULL DEFAULT 'admin' COMMENT 'staff hanya bisa check-in',
  last_login_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO admins (name, email, password, role) VALUES
('Administrator', 'admin@karcis.id', '$2y$10$6ur5x5SaReAG2HCRSGWubeFPxvOK6f/THwilSGjOr2QVFEV5Yt1D2', 'admin');

ALTER TABLE tickets ADD COLUMN checked_in_by INT UNSIGNED NULL AFTER checked_in_at;
