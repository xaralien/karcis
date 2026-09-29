# Karcis — Website Tiket Event (CodeIgniter 3 + Bootstrap 5)

> Versi **tanpa rute kustom, tanpa file helper, dan tanpa library tiket**.
> Semua logika ada di controller, model, dan view. Library yang dipakai hanya `Duitku.php`
> buatan sendiri, plus library bawaan CodeIgniter (session, form_validation, upload, email).

Satu backend, dua tampilan: **desktop** (`application/views/desktop`) dan **mobile** (`application/views/mobile`).
Tampilan dipilih otomatis dari User Agent, atau manual lewat `/tampilan/desktop` dan `/tampilan/mobile`.

## Kebutuhan
- PHP 7.4 – 8.3 (ekstensi: mysqli, curl, gd, mbstring)
- MySQL 5.7+ / MariaDB 10.3+
- Apache dengan mod_rewrite (sudah ada `.htaccess`)

## Instalasi
1. Salin folder ini ke web server, misal `htdocs/karcis`.
2. Impor database: `mysql -u root -p < database/karcis.sql` (berisi 6 contoh event).
3. `application/config/config.php` → ganti `encryption_key`. `base_url` otomatis mengikuti alamat yang dibuka (localhost maupun IP jaringan lokal seperti `192.168.1.5`), jadi tidak perlu diubah sampai kamu punya domain tetap.
4. `application/config/database.php` → username, password, nama database.
5. `application/config/duitku.php` → merchant code & API key. Set `duitku_sandbox` = FALSE saat produksi.
6. `application/config/email.php` → SMTP (Gmail App Password, Mailtrap, dsb). Samakan `mail_from` di `config/tiket.php`.
7. Dashboard Duitku → Callback URL: `https://domainmu/payment/callback`.
8. Pastikan folder `uploads/qr/`, `uploads/events/`, dan `application/cache/` bisa ditulis server.
9. Jika di subfolder, aktifkan `RewriteBase /karcis/` di `.htaccess`.

## Aturan harga (`application/config/tiket.php`)
| Item | Nilai |
|---|---|
| Harga tiket | Rp100.000 (kolom `ticket_types.price`) |
| Biaya layanan | Rp2.000 × jumlah tiket |
| Biaya transaksi | Rp4.000 per transaksi |
| Maks. tiket | 4 per transaksi |

Contoh: 1 tiket = 100.000 + 2.000 + 4.000 = **Rp106.000**; 4 tiket = 400.000 + 8.000 + 4.000 = **Rp412.000**.
Harga selalu dihitung ulang di server.

## Alur pembelian (tanpa login)
1. **Detail event** (`/event/{slug}`) — deskripsi, fasilitas, bintang tamu, jadwal, daftar kategori tiket → tombol **Pesan tiket**
2. **Pilih tiket** (`/event/{slug}/tiket`) — pilih jumlah per kategori, maks 4 tiket total → **Lihat rincian tiket**
3. **Rincian tiket** (`/checkout`) — tabel rincian per kategori + biaya, isi nama, email (diketik ulang), HP → pop-up konfirmasi email → **Bayar**
4. Server validasi ulang harga & kuota → order `pending` → halaman bayar Duitku
5. Duitku → `POST /payment/callback` (signature diverifikasi) → order `paid`
6. Tiket diterbitkan (1 QR per tiket), stok `sold` bertambah, email dikirim
7. Pembeli kembali ke `/payment/return` (status dicek ulang ke Duitku bila callback belum masuk)

Isi QR: `{"ticket_id":"TKT-XXXXXXXXXX","event_id":1}`

## Akun pembeli (opsional)
Membeli tiket **tetap tanpa login**. Akun hanya menambah kemudahan:

- Daftar di `/account/register` (nama, email, HP, password).
- Pesanan lama yang dibuat tanpa login akan **otomatis masuk ke riwayat** saat akun dibuat atau saat login, selama emailnya sama.
- Di `/account` ada ringkasan: tiket dimiliki, event mendatang, total pesanan, total belanja, dan peringatan bila ada pesanan yang belum dibayar.
- Riwayat di `/account/orders` punya tombol Lihat tiket QR, Cetak, atau Lanjutkan pembayaran per pesanan.
- Saat checkout, nama, email, dan HP terisi otomatis dari akun.
- Tabel `users`, dan kolom `orders.user_id` yang boleh NULL untuk pembelian tanpa akun.

**Sudah punya database versi sebelumnya?** Jalankan `database/upgrade_users.sql`.

## Panel admin
Buka **`/admin`** — login awal: `admin@karcis.id` / `admin123` (**segera ganti** di menu Ganti password).

| Menu | Fungsi |
|---|---|
| Dashboard | Pendapatan, tiket terjual, check-in, grafik 14 hari, penjualan per event |
| Event | Tambah/edit/hapus event. Tab: Info, Jadwal, Kategori tiket, Fasilitas, Bintang tamu, Galeri (upload gambar) |
| Kategori | Kategori event + ikon |
| Pesanan | Cari & filter, kolom **Scan** (mis. 2/3 tiket sudah dipindai) dan ringkasan kehadiran, detail, kirim ulang tiket, cek status ke Duitku, batalkan check-in, unduh CSV |
| Check-in | Pindai QR dengan kamera HP/laptop atau ketik kode tiket. Kode tiket tampil besar pada kotak hasil |
| Laporan & ekspor | Data pembeli (remarketing), audit penjualan, dan data kehadiran — format Excel (.xls), CSV, dan PDF |
| Akun admin | Tambah akun **Admin** (akses penuh) atau **Petugas check-in** (hanya pemindai) |

Langkah membuat event baru: **Event → Tambah event** (isi info + banner/thumbnail) → tab **Jadwal** → tab **Kategori tiket** → ubah status menjadi **Tayang**.
Event tanpa kategori tiket otomatis tetap Draft.

**Gambar event (tab Info event → Gambar):**

| Gambar | Ukuran | Dipakai di |
|---|---|---|
| Banner | 1600×700 px | Carousel beranda desktop, halaman detail event |
| Banner mobile *(disarankan)* | 1080×1350 px (4:5) | Carousel beranda di HP, memenuhi satu kartu |
| Thumbnail | 800×600 px | Kartu event, katalog, checkout, riwayat |

Templat berisi area aman ada di `assets/template/` (juga bisa diunduh dari tautan "unduh templat" di form admin).
Judul dan tombol carousel menumpang di atas gambar (kiri bawah di desktop, bawah di HP), jadi sisakan area itu tanpa tulisan penting.
Kalau banner mobile kosong, HP memakai banner utama yang dipotong bagian tengahnya, jadi tulisan di kiri-kanan banner bisa terpotong. Karena itu banner mobile sangat disarankan untuk event yang tampil di carousel.
Database lama: jalankan `database/upgrade_banner_mobile.sql`.

**Urutan carousel:** di tab Info event, saat sakelar "Tampilkan di carousel beranda" menyala, muncul kolom **Urutan carousel**.
Angka kecil tampil lebih dulu; event tanpa angka mengikuti tanggal terdekat setelahnya. Maksimal 5 event tampil,
dan event yang tanggalnya sudah lewat otomatis keluar dari carousel.
Database lama: jalankan `database/upgrade_carousel.sql`.

**Pratinjau draft:** selama kamu login sebagai admin, halaman event berstatus Draft atau Selesai tetap bisa dibuka
(misalnya lewat tombol "Lihat halaman" di admin), lengkap dengan pita kuning "Pratinjau admin" dan tombol beli yang dinonaktifkan.
Pengunjung biasa tetap mendapat 404 sampai statusnya diubah menjadi Tayang.

Aturan pengaman:
- Event yang sudah punya pesanan tidak bisa dihapus (ubah status ke Draft/Selesai).
- Kategori tiket yang pernah dipesan tidak bisa dihapus; kuota tidak boleh di bawah jumlah terjual.
- Satu tiket hanya bisa check-in sekali, aman walau dua gate memindai bersamaan.
- Kamera pemindai membutuhkan HTTPS (atau `localhost`).

**Sudah punya database versi lama?** Jalankan `database/upgrade_admin.sql` alih-alih `karcis.sql` agar data tidak hilang.

## Struktur penting
```
application/
  config/      tiket.php, duitku.php, email.php (routes.php tanpa rute kustom)
  core/        MY_Controller.php        (Base_Controller, MY_Controller, Admin_Controller)
  controllers/ Home, Explore, Event, Checkout, Payment, Ticket, Order
  controllers/admin/ Auth, Dashboard, Events, Categories, Orders, Checkin, Users, Account
  models/      Fmt_model (pengganti helper), User_model (akun pembeli), Event_model, Category_model,
               Order_model, Ticket_model (QR + email), Admin_model
  libraries/   Duitku.php (satu-satunya library buatan sendiri)
  views/       desktop/, mobile/, shared/, emails/, admin/
  third_party/ phpqrcode/                (sudah ditambal untuk PHP 8)
assets/        css/base.css, desktop.css, mobile.css, js/app.js
database/      karcis.sql
uploads/qr/    file QR tiket
uploads/events/ gambar event yang diunggah dari admin
```

## URL (pola bawaan CodeIgniter: /controller/method/parameter)
| URL | Fungsi |
|---|---|
| `/` | Beranda |
| `/explore` | Jelajahi event (`?q=&kategori=&kota=&sort=&page=`) |
| `/event/detail/{slug}` | Detail event |
| `/event/tickets/{slug}` | Langkah 1: pilih kategori tiket |
| `/checkout` | Langkah 2: rincian tiket + data pembeli |
| `/checkout/process` | Langkah 3: buat pesanan lalu ke Duitku |
| `/payment/callback` | Callback Duitku (dikecualikan dari CSRF) |
| `/payment/status?order={kode}` | Halaman status pembayaran + tombol kirim ulang tiket |
| `/payment/resend` | Kirim ulang tiket (POST, dilindungi access token pesanan) |
| `/ticket/show/{kode}/{token}` | Tiket QR |
| `/ticket/printout/{kode}/{token}` | Cetak, 1 tiket per halaman A4 (`?id=TKT-xxx` untuk satu tiket, `&auto=1` langsung cetak) |
| `/account/login` `/account/register` `/account/logout` | Akun pembeli |
| `/account` | Ringkasan akun (tiket dimiliki, event mendatang, pesanan terbaru) |
| `/account/orders` | Riwayat semua pesanan & tiket |
| `/account/profile` | Ubah data diri & password |
| `/order` | Cek pesanan tanpa akun (kode pesanan + email) |
| `/order/resend` | Kirim ulang tiket ke email |
| `/home/mode/mobile` `/home/mode/desktop` | Ganti tampilan secara manual |
| `/admin` | Panel admin (dashboard) |
| `/admin/auth/login` `/admin/auth/logout` | Masuk & keluar admin |
| `/admin/events` `/admin/orders` `/admin/checkin` … | Menu admin lain |

`application/config/routes.php` hanya berisi `default_controller`; tidak ada rute kustom sama sekali.

## Pengganti helper & library
| Dulu | Sekarang |
|---|---|
| `application/helpers/tiket_helper.php` | `models/Fmt_model.php`, dipanggil `$this->fmt->rupiah()`, `->tgl()`, `->img()`, `->fees()`, `->url()`, `->base()` |
| `libraries/Ticket_issuer.php` | `models/Ticket_model.php` (terbitkan tiket, tulis QR PNG, kirim email, check-in) |
| `url helper` (`site_url`, `base_url`, `redirect`) | `$this->fmt->url()`, `$this->fmt->base()`, `$this->go()` |
| `form helper` (`form_open`, `set_value`, `form_error`) | tag `<form>` biasa + `$this->fmt->csrf()`, `$this->fmt->old()`, `$this->fmt->err()` |
| `library Pagination` | `$this->fmt->pages()` + `views/shared/pagination.php` |
| `download helper` (`force_download`) | header CSV langsung lewat `$this->output` |

## Laporan & ekspor (`/admin/reports`)
Semua laporan memakai filter yang sama: event, tanggal awal, dan tanggal akhir.

| Laporan | Isi | Format |
|---|---|---|
| **Data pembeli** | Satu baris per pembeli: email, nomor HP, jumlah pesanan, jumlah tiket, total belanja, tiket yang benar-benar dipakai masuk, pembelian pertama & terakhir, event dan kategori yang pernah dibeli. Untuk remarketing promotor. | .xls, .csv, PDF |
| **Audit penjualan** | Setiap transaksi dengan pemisahan harga tiket, biaya layanan, biaya transaksi, status, dan referensi Duitku untuk dicocokkan dengan mutasi rekening. Disertai rekap per kategori tiket. | .xls, .csv, PDF |
| **Data kehadiran** | Laporan akhir: tiap tiket beserta status **Sudah scan / Belum scan**, waktu masuk, dan petugas yang memindai. Disertai ringkasan hadir vs belum, kehadiran per kategori, dan kepadatan jam masuk. Ada juga tombol khusus "Hanya yang belum scan". | .xls, .csv, PDF |

File `.xls` berupa tabel yang dikenali Excel, LibreOffice, dan Google Sheets, tanpa library tambahan.
Tombol PDF membuka halaman siap cetak; pilih tujuan **Simpan sebagai PDF** di dialog cetak browser.

## Kalau email tiket tidak sampai
Pembeli punya tiga jalan tanpa perlu menghubungi admin:

1. **Halaman status pembayaran** (muncul otomatis setelah bayar) — ada keterangan kapan email terkirim dan tombol **Kirim ulang**. Alamat email bisa dibetulkan di situ bila salah ketik, lalu tiket dikirim ke alamat baru.
2. **Halaman tiket QR** — tombol yang sama, dan tiketnya sendiri sudah bisa dibuka atau dicetak walau emailnya belum sampai.
3. **Cek pesanan** (`/order`) — masukkan kode pesanan + email untuk mengirim ulang.

Batas kirim ulang 1 kali per 3 menit dari halaman status, dan 1 kali per 5 menit dari cek pesanan.
Admin juga bisa mengirim ulang sekaligus membetulkan alamat email dari **Pesanan → detail**; perubahan email dicatat di `application/logs/`.

## Pembaruan tampilan & cache
Setiap file CSS/JS dimuat dengan penanda versi otomatis (`base.css?v=...`) yang berubah setiap kali filenya diganti.
Jadi setelah mengunggah folder `assets` baru, browser pengunjung langsung memakai versi terbaru tanpa perlu hapus cache.

## Aset tanpa internet
Bootstrap, Bootstrap Icons, Chart.js, dan pemindai QR disimpan lokal di `assets/vendor/`,
jadi situs tetap bergaya normal walau server atau HP tidak punya akses internet.
Yang masih dari internet hanya font Google (otomatis jatuh ke font bawaan sistem bila gagal)
dan gambar contoh dari picsum.photos di data awal — ganti dengan gambar sendiri lewat panel admin.

## Catatan
- Uji di lokal tanpa internet: set env `EMAIL_DNS_CHECK=0` agar cek DNS email dilewati.
- Status `expired` diset saat pembeli membuka halaman status setelah batas waktu (default 60 menit). Untuk pembersihan rutin, jalankan cron:
  `UPDATE orders SET status='expired' WHERE status='pending' AND expired_at < NOW();`
- Gambar contoh memakai picsum.photos; ganti dengan gambar event asli.
