-- =========================================================
-- Karcis — database tiket event (MySQL 5.7+ / MariaDB 10.3+)
-- =========================================================
CREATE DATABASE IF NOT EXISTS karcis CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE karcis;
SET NAMES utf8mb4;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS admins, tickets, order_items, orders, users, event_gallery, event_guests,
  event_facilities, event_schedules, ticket_types, events, categories;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE categories (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(80) NOT NULL,
  slug VARCHAR(90) NOT NULL UNIQUE,
  icon VARCHAR(40) NOT NULL DEFAULT 'bi-ticket-perforated'
) ENGINE=InnoDB;

CREATE TABLE events (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  category_id INT UNSIGNED NOT NULL,
  title VARCHAR(160) NOT NULL,
  slug VARCHAR(180) NOT NULL UNIQUE,
  event_type VARCHAR(60) NOT NULL COMMENT 'Konser, Festival, Workshop, dsb',
  organizer VARCHAR(120) NOT NULL,
  short_desc VARCHAR(255) NOT NULL,
  description TEXT NOT NULL,
  banner VARCHAR(255) NOT NULL,
  thumbnail VARCHAR(255) NOT NULL,
  venue VARCHAR(160) NOT NULL,
  address VARCHAR(255) NOT NULL,
  city VARCHAR(80) NOT NULL,
  maps_url VARCHAR(255) NULL,
  start_date DATE NOT NULL,
  is_featured TINYINT(1) NOT NULL DEFAULT 0,
  status ENUM('draft','published','ended') NOT NULL DEFAULT 'published',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_event_cat FOREIGN KEY (category_id) REFERENCES categories(id)
) ENGINE=InnoDB;

-- Hari pelaksanaan: hari apa, jam berapa, di mana
CREATE TABLE event_schedules (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  event_id INT UNSIGNED NOT NULL,
  label VARCHAR(60) NOT NULL,
  event_date DATE NOT NULL,
  start_time TIME NOT NULL,
  end_time TIME NOT NULL,
  venue VARCHAR(160) NOT NULL,
  CONSTRAINT fk_sched_event FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE ticket_types (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  event_id INT UNSIGNED NOT NULL,
  name VARCHAR(80) NOT NULL,
  description VARCHAR(255) NOT NULL,
  price INT UNSIGNED NOT NULL DEFAULT 100000,
  quota INT UNSIGNED NOT NULL,
  sold INT UNSIGNED NOT NULL DEFAULT 0,
  sort_order INT NOT NULL DEFAULT 0,
  CONSTRAINT fk_tt_event FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE event_facilities (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  event_id INT UNSIGNED NOT NULL,
  name VARCHAR(80) NOT NULL,
  icon VARCHAR(40) NOT NULL DEFAULT 'bi-check2-circle',
  CONSTRAINT fk_fac_event FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE event_guests (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  event_id INT UNSIGNED NOT NULL,
  name VARCHAR(100) NOT NULL,
  role VARCHAR(80) NOT NULL,
  photo VARCHAR(255) NOT NULL,
  CONSTRAINT fk_guest_event FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE event_gallery (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  event_id INT UNSIGNED NOT NULL,
  image VARCHAR(255) NOT NULL,
  caption VARCHAR(160) NULL,
  CONSTRAINT fk_gal_event FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
) ENGINE=InnoDB;


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

CREATE TABLE orders (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_code VARCHAR(30) NOT NULL UNIQUE COMMENT 'merchantOrderId untuk Duitku',
  user_id INT UNSIGNED NULL COMMENT 'terisi bila pembeli sedang login',
  access_token CHAR(40) NOT NULL,
  event_id INT UNSIGNED NOT NULL,
  buyer_name VARCHAR(100) NOT NULL,
  buyer_email VARCHAR(150) NOT NULL,
  buyer_phone VARCHAR(20) NOT NULL,
  ticket_qty TINYINT UNSIGNED NOT NULL,
  subtotal INT UNSIGNED NOT NULL,
  service_fee INT UNSIGNED NOT NULL,
  transaction_fee INT UNSIGNED NOT NULL,
  total INT UNSIGNED NOT NULL,
  status ENUM('pending','paid','failed','expired') NOT NULL DEFAULT 'pending',
  duitku_reference VARCHAR(60) NULL,
  payment_url VARCHAR(255) NULL,
  payment_code VARCHAR(10) NULL,
  paid_at DATETIME NULL,
  expired_at DATETIME NOT NULL,
  email_sent_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_email (buyer_email),
  CONSTRAINT fk_order_event FOREIGN KEY (event_id) REFERENCES events(id),
  CONSTRAINT fk_order_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE order_items (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_id INT UNSIGNED NOT NULL,
  ticket_type_id INT UNSIGNED NOT NULL,
  ticket_name VARCHAR(80) NOT NULL,
  price INT UNSIGNED NOT NULL,
  qty TINYINT UNSIGNED NOT NULL,
  CONSTRAINT fk_item_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  CONSTRAINT fk_item_tt FOREIGN KEY (ticket_type_id) REFERENCES ticket_types(id)
) ENGINE=InnoDB;

-- 1 baris = 1 tiket fisik (1 QR)
CREATE TABLE tickets (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  ticket_code VARCHAR(30) NOT NULL UNIQUE COMMENT 'ID tiket yang ada di QR',
  order_id INT UNSIGNED NOT NULL,
  event_id INT UNSIGNED NOT NULL COMMENT 'ID konser yang ada di QR',
  ticket_type_id INT UNSIGNED NOT NULL,
  qr_file VARCHAR(120) NOT NULL,
  is_checked_in TINYINT(1) NOT NULL DEFAULT 0,
  checked_in_at DATETIME NULL,
  checked_in_by INT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_ticket_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  CONSTRAINT fk_ticket_event FOREIGN KEY (event_id) REFERENCES events(id),
  CONSTRAINT fk_ticket_tt FOREIGN KEY (ticket_type_id) REFERENCES ticket_types(id)
) ENGINE=InnoDB;


-- Akun admin panel (login: admin@karcis.id / admin123 — WAJIB diganti setelah login)
CREATE TABLE admins (
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

-- =========================================================
-- Data contoh
-- =========================================================
INSERT INTO categories (id, name, slug, icon) VALUES
(1,'Konser','konser','bi-music-note-beamed'),
(2,'Festival','festival','bi-stars'),
(3,'Stand Up Comedy','stand-up-comedy','bi-mic'),
(4,'Workshop','workshop','bi-easel'),
(5,'Olahraga','olahraga','bi-trophy'),
(6,'Pameran','pameran','bi-palette');

INSERT INTO events (id,category_id,title,slug,event_type,organizer,short_desc,description,banner,thumbnail,venue,address,city,maps_url,start_date,is_featured) VALUES
(1,1,'Senandung Senja 2026','senandung-senja-2026','Konser Musik','Senja Kolektif',
 'Dua malam musik folk dan pop dengan panggung terbuka menghadap danau.',
 'Senandung Senja kembali untuk edisi keempat. Selama dua malam, penonton diajak menikmati musik folk, pop, dan akustik di panggung terbuka yang menghadap danau.\n\nGerbang dibuka pukul 15.00 WIB. Tersedia area makanan lokal, zona istirahat, dan penitipan barang. Anak di bawah 12 tahun wajib didampingi orang tua.',
 'https://picsum.photos/seed/senja-banner/1600/700','https://picsum.photos/seed/senja/800/600',
 'Lapangan Danau Sunter','Jl. Danau Sunter Selatan, Tanjung Priok','Jakarta','https://maps.google.com/?q=Danau+Sunter','2026-11-14',1),
(2,2,'Pesta Rakyat Nusantara','pesta-rakyat-nusantara','Festival Budaya','Rumah Budaya ID',
 'Festival kuliner, tari, dan musik daerah dari 20 provinsi.',
 'Pesta Rakyat Nusantara menghadirkan kuliner, tari tradisional, dan musik daerah dari 20 provinsi dalam satu kawasan. Setiap tiket berlaku untuk satu hari kunjungan sesuai pilihan.',
 'https://picsum.photos/seed/nusantara-banner/1600/700','https://picsum.photos/seed/nusantara/800/600',
 'JIExpo Kemayoran','Jl. Benyamin Sueb, Kemayoran','Jakarta','https://maps.google.com/?q=JIExpo','2026-12-05',1),
(3,3,'Tawa Tengah Kota','tawa-tengah-kota','Stand Up Comedy','Panggung Receh',
 'Satu malam penuh tawa bersama komika pilihan.',
 'Pertunjukan komedi tunggal berdurasi dua jam. Materi ditujukan untuk penonton 18 tahun ke atas.',
 'https://picsum.photos/seed/tawa-banner/1600/700','https://picsum.photos/seed/tawa/800/600',
 'Teater Sabang','Jl. H. Agus Salim No. 40','Bandung','https://maps.google.com/?q=Bandung','2026-10-24',1),
(4,4,'Kelas Kopi Manual Brew','kelas-kopi-manual-brew','Workshop','Seduh Pelan',
 'Belajar V60, Aeropress, dan kalibrasi rasa dari barista juara.',
 'Workshop praktik langsung selama tiga jam. Peserta mendapat starter kit biji kopi dan sertifikat.',
 'https://picsum.photos/seed/kopi-banner/1600/700','https://picsum.photos/seed/kopi/800/600',
 'Seduh Pelan Roastery','Jl. Kaliurang Km 5','Yogyakarta','https://maps.google.com/?q=Yogyakarta','2026-10-18',0),
(5,5,'Lari Pagi Kota Tua 10K','lari-pagi-kota-tua-10k','Fun Run','Komunitas Jalan Kaki',
 'Rute 5K dan 10K melewati bangunan bersejarah.',
 'Start dan finish di Taman Fatahillah. Race pack berisi kaos, nomor dada, dan medali finisher.',
 'https://picsum.photos/seed/lari-banner/1600/700','https://picsum.photos/seed/lari/800/600',
 'Taman Fatahillah','Kota Tua, Tamansari','Jakarta','https://maps.google.com/?q=Kota+Tua','2026-11-01',0),
(6,6,'Ruang Rupa Muda','ruang-rupa-muda','Pameran Seni','Galeri Lintas',
 'Pameran karya 40 perupa muda Indonesia.',
 'Instalasi, lukisan, dan karya digital dari 40 perupa di bawah 30 tahun. Tur kuratorial setiap pukul 16.00.',
 'https://picsum.photos/seed/rupa-banner/1600/700','https://picsum.photos/seed/rupa/800/600',
 'Galeri Lintas','Jl. Tunjungan No. 12','Surabaya','https://maps.google.com/?q=Surabaya','2026-11-21',0);

INSERT INTO event_schedules (event_id,label,event_date,start_time,end_time,venue) VALUES
(1,'Hari 1','2026-11-14','15:00','23:00','Panggung Danau, Lapangan Danau Sunter'),
(1,'Hari 2','2026-11-15','15:00','23:00','Panggung Danau, Lapangan Danau Sunter'),
(2,'Hari 1','2026-12-05','10:00','22:00','Hall A–C, JIExpo Kemayoran'),
(2,'Hari 2','2026-12-06','10:00','22:00','Hall A–C, JIExpo Kemayoran'),
(2,'Hari 3','2026-12-07','10:00','21:00','Hall A–C, JIExpo Kemayoran'),
(3,'Pertunjukan','2026-10-24','19:30','21:30','Teater Sabang'),
(4,'Sesi Workshop','2026-10-18','13:00','16:00','Seduh Pelan Roastery'),
(5,'Race Day','2026-11-01','05:30','10:00','Taman Fatahillah'),
(6,'Pembukaan','2026-11-21','10:00','20:00','Galeri Lintas'),
(6,'Pameran','2026-11-22','10:00','20:00','Galeri Lintas');

INSERT INTO ticket_types (event_id,name,description,price,quota,sold,sort_order) VALUES
(1,'Hari 1 — Reguler','Masuk tanggal 14 November',100000,500,120,1),
(1,'Hari 2 — Reguler','Masuk tanggal 15 November',100000,500,98,2),
(1,'Festival Pit','Area depan panggung, hari 1',100000,150,150,3),
(2,'Tiket Harian','Berlaku satu hari pilihan',100000,2000,340,1),
(2,'Tiket Keluarga','Termasuk voucher kuliner',100000,300,40,2),
(3,'Kursi Tribun','Tempat duduk bebas area tribun',100000,250,60,1),
(3,'Kursi Depan','Tiga baris terdepan',100000,60,22,2),
(4,'Peserta Workshop','Termasuk starter kit',100000,25,9,1),
(5,'Kategori 5K','Race pack + medali',100000,800,210,1),
(5,'Kategori 10K','Race pack + medali',100000,600,180,2),
(6,'Tiket Masuk','Berlaku satu kali kunjungan',100000,1000,75,1);

INSERT INTO event_facilities (event_id,name,icon) VALUES
(1,'Area kuliner','bi-cup-hot'),(1,'Musala','bi-moon-stars'),(1,'Toilet bersih','bi-droplet'),
(1,'Penitipan barang','bi-bag-check'),(1,'Tim medis','bi-heart-pulse'),(1,'Parkir motor & mobil','bi-p-circle'),
(2,'Area kuliner','bi-cup-hot'),(2,'Ruang laktasi','bi-person-hearts'),(2,'Musala','bi-moon-stars'),(2,'Akses kursi roda','bi-universal-access'),
(3,'Kursi bernomor','bi-grid-3x3'),(3,'Pendingin ruangan','bi-snow'),(3,'Toilet','bi-droplet'),
(4,'Starter kit kopi','bi-cup'),(4,'Sertifikat','bi-award'),
(5,'Race pack','bi-bag'),(5,'Medali finisher','bi-award'),(5,'Pos minum','bi-droplet'),(5,'Tim medis','bi-heart-pulse'),
(6,'Tur kuratorial','bi-people'),(6,'Pendingin ruangan','bi-snow');

INSERT INTO event_guests (event_id,name,role,photo) VALUES
(1,'Nadia Rinjani','Penyanyi folk','https://picsum.photos/seed/guest1/400/400'),
(1,'Kelana Band','Band pop','https://picsum.photos/seed/guest2/400/400'),
(1,'Arya Mahesa','Gitaris akustik','https://picsum.photos/seed/guest3/400/400'),
(2,'Sanggar Tari Lestari','Tari tradisional','https://picsum.photos/seed/guest4/400/400'),
(2,'Orkes Kroncong Muda','Musik keroncong','https://picsum.photos/seed/guest5/400/400'),
(3,'Bima Lontar','Komika','https://picsum.photos/seed/guest6/400/400'),
(3,'Sari Kemuning','Komika','https://picsum.photos/seed/guest7/400/400'),
(4,'Dimas Arabika','Barista','https://picsum.photos/seed/guest8/400/400'),
(6,'Laras Wening','Kurator','https://picsum.photos/seed/guest9/400/400');

INSERT INTO event_gallery (event_id,image,caption) VALUES
(1,'https://picsum.photos/seed/g11/900/600','Panggung utama 2025'),(1,'https://picsum.photos/seed/g12/900/600','Penonton saat senja'),
(1,'https://picsum.photos/seed/g13/900/600','Area kuliner'),(1,'https://picsum.photos/seed/g14/900/600','Penampilan penutup'),
(2,'https://picsum.photos/seed/g21/900/600','Pawai budaya'),(2,'https://picsum.photos/seed/g22/900/600','Stan kuliner'),
(3,'https://picsum.photos/seed/g31/900/600','Pertunjukan tahun lalu'),
(4,'https://picsum.photos/seed/g41/900/600','Sesi seduh'),
(5,'https://picsum.photos/seed/g51/900/600','Garis start'),
(6,'https://picsum.photos/seed/g61/900/600','Ruang instalasi');
