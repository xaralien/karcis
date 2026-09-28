-- Tambahan akun pembeli. Jalankan bila database versi sebelumnya sudah terpasang.
USE karcis;
SET NAMES utf8mb4;

-- Akun pembeli (opsional: tiket tetap bisa dibeli tanpa login)
CREATE TABLE users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  phone VARCHAR(20) NULL,
  password VARCHAR(255) NOT NULL,
  last_login_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

ALTER TABLE orders ADD COLUMN user_id INT UNSIGNED NULL COMMENT 'terisi bila pembeli sedang login' AFTER order_code;
ALTER TABLE orders ADD CONSTRAINT fk_order_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL;

-- Kaitkan pesanan lama ke akun yang dibuat kemudian (dijalankan otomatis juga saat login/daftar)
UPDATE orders o JOIN users u ON u.email = o.buyer_email SET o.user_id = u.id WHERE o.user_id IS NULL;
