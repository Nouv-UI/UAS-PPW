# Jajan Pasar An-NaHL - Web Programming Academic Project

**Jajan Pasar An-NaHL** adalah platform berbasis web modern untuk memamerkan dan mengelola pesanan jajanan pasar tradisional Indonesia secara online. Proyek ini dibangun menggunakan arsitektur native PHP, MySQL, Bootstrap 5, dan JavaScript vanilla untuk memenuhi standar akademik tingkat universitas.

---

## 🌟 Fitur Utama

### Halaman Publik (Pelanggan)
1. **Beranda (Home):** Hero banner, promosi/highlight produk aktif dengan fun facts unik, serta tentang kami.
2. **Katalog Produk:** Daftar produk yang aktif, fitur pencarian kata kunci, filter kategori, dan paginasi interaktif.
3. **Detail Produk:** Informasi detail menu, informasi supplier (pemasok), serta form jumlah pemesanan.
4. **Keranjang Belanja (Cart):** Manajemen kuantitas item, hapus item, catatan pemesanan khusus, dan proses checkout otomatis.
5. **Profil Pengguna:** Manajemen data diri, riwayat lengkap transaksi pemesanan, dan detail item pesanan yang dinamis.

### Area Administrator (Admin Panel)
1. **Dashboard:** Statistik ringkasan (Total produk, supplier, kategori, total transaksi), daftar pesanan terbaru, dan daftar jajanan terlaris.
2. **Manajemen Produk (CRUD):** Tambah, edit, cari, paginasi, hapus produk beserta pengelolaan multi-kategori.
3. **Manajemen Kategori (CRUD):** Tambah, edit, hapus kategori jajanan (makanan, minuman, snack, dll).
4. **Manajemen Pemasok/Supplier (CRUD):** Pengelolaan data kemitraan pemasok titipan.
5. **Manajemen Highlight (CRUD):** Kelola promosi beranda harian dan fun fact produk dengan masa tayang tertentu.
6. **Manajemen Pesanan:** Verifikasi status pemesanan (`pending`, `confirmed`, `cancelled`).
7. **Analitik Produk:** Visualisasi performa penjualan, korelasi profit margin per produk, serta total keuntungan kotor & bersih.

---

## 🛠️ Arsitektur Database & Implementasi Akademik

Proyek ini memaksimalkan kemampuan DBMS MySQL dengan mengintegrasikan beberapa objek database lanjutan:

### 1. View MySQL (Digunakan di Halaman Web)
* `view_daftar_produk`: Mengambil data produk aktif beserta daftar kategori gabungan (digunakan di halaman Katalog).
* `view_highlight_ditampilkan`: Memfilter data highlight yang aktif sesuai rentang tanggal hari ini (digunakan di Beranda).
* `view_analitik_produk`: Menghitung agregasi kuantitas item terjual dan akumulasi pendapatan kotor per produk (digunakan di Admin Dashboard & Analytics).

### 2. Fungsi MySQL (Stored Functions)
* `hitung_margin_produk(product_id)`: Menghitung selisih harga jual dengan harga supplier untuk mengetahui laba kotor per item (digunakan di halaman Analitik).
* `hitung_total_order(order_id)`: Menghitung total harga belanjaan berdasarkan item-item pada pesanan tertentu (digunakan di halaman Profil & Admin Detail Pesanan).

### 3. Trigger MySQL (Validasi & Integritas Data)
Sistem web menangkap exception database (PDOException) dan menampilkannya sebagai pesan alert Bootstrap:
* `validasi_harga_products_insert` & `validasi_harga_products_update`: Memastikan harga jual tidak boleh negatif dan harus lebih besar dari harga supplier.
* `validasi_order_status`: Memastikan pesanan yang sudah dibatalkan tidak bisa diubah statusnya lagi, dan pesanan yang sudah dikonfirmasi tidak bisa kembali ke status pending.

---

## 📂 Struktur Direktori

```
jp-annahls/
├── assets/
│   ├── css/
│   │   └── style.css      # Custom styling, warna marun, floating navbar, card custom
│   ├── js/
│   │   └── main.js       # Validasi form, alert konfirmasi hapus/edit, event listeners
│   └── img/              # Tempat penyimpanan gambar produk
├── includes/
│   ├── config.php        # Koneksi PDO Database & Helper Global
│   ├── auth.php          # Pemeriksaan sesi & verifikasi admin/user
│   ├── header.php        # Layout atas & Floating Pill Navbar dinamis
│   └── footer.php        # Layout bawah & Navy Footer
├── pages/
│   ├── login.php         # Login aman (password_verify & migrasi password hash)
│   ├── register.php      # Pendaftaran akun pelanggan
│   ├── logout.php        # Pembersihan sesi user
│   ├── katalog.php       # Katalog filter & pencarian
│   ├── detail.php        # Halaman detail & quantity order
│   ├── cart.php          # Keranjang & transaksi checkout
│   ├── profile.php       # Profil user & riwayat order
│   ├── admin_dashboard.php
│   ├── admin_products.php
│   ├── admin_categories.php
│   ├── admin_suppliers.php
│   ├── admin_highlights.php
│   ├── admin_orders.php
│   └── admin_analytics.php
├── index.php             # Landing page utama
├── jp_annahl (1).sql     # Dump skema & data database awal
├── .gitignore
└── README.md
```

---

## 🚀 Panduan Instalasi Lokal (XAMPP/Laragon)

1. **Unduh/Salin Berkas:**
   Salin folder project ini (`jp-annahls`) ke dalam direktori server lokal Anda (biasanya `C:\xampp\htdocs\`).
   
2. **Impor Database:**
   * Buka browser dan akses `http://localhost/phpmyadmin/`.
   * Buat database baru bernama `jp_annahl`.
   * Masuk ke menu **Import** dan pilih file `jp_annahl (1).sql` yang ada di dalam root folder project ini. Klik **Go** untuk mengimpor skema, view, trigger, fungsi, dan data awal.

3. **Akses Website:**
   Buka browser dan kunjungi `http://localhost/jp-annahls/`.

4. **Kredensial Login Bawaan:**
   * **Akun Administrator:**
     * Username: `naufal`
     * Password: `rahasia123`
   * **Akun Pelanggan (User):**
     * Username: `budi01`
     * Password: `userpass1`
     * *Catatan: Sistem secara otomatis akan melakukan enkripsi satu arah (password_hash) saat akun-akun ini berhasil login untuk pertama kali.*
