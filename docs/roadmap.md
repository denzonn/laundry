# Roadmap Sistem Manajemen Laundry

Status: `[ ]` belum · `[~]` dikerjakan · `[x]` selesai

Urutan fase = urutan kerja. Tiap fase harus bisa dipakai (shippable) sebelum lanjut.
Referensi: [database.md](database.md) · [alur-bisnis.md](alur-bisnis.md) · [ui-guidelines.md](ui-guidelines.md)

---

## Fase 0 — Bersih-bersih Base Project

Tujuan: login → dashboard kosong jalan tanpa error.

- [x] Hapus route Breeze yang controllernya tidak ada di `routes/auth.php` (sisakan login & logout)
- [x] Hapus test Breeze yang tidak relevan (`tests/Feature/Auth/*` kecuali Authentication, `ProfileTest`)
- [x] Buat route + view `dashboard` (placeholder)
- [x] Ganti sidebar HRIS → menu laundry (lihat struktur menu di bawah)
- [x] Navbar: nama & role dari `auth()->user()`, hapus notifikasi hardcode
- [x] Footer & logo: "Base Project / HRIS" → nama laundry (dari `config('app.name')`)
- [x] Hapus double load jQuery
- [x] `.env`: `APP_NAME`, `APP_LOCALE=id`, `APP_FAKER_LOCALE=id_ID`, `DB_DATABASE=laundry`
- [x] `phpunit.xml` pakai sqlite `:memory:` (sebelumnya test akan menghapus DB MySQL)
- [x] Login view memakai `layouts.auth` yang tidak ada → `landing.blade.php` di-rename jadi `auth.blade.php`

**Selesai bila**: login sukses → dashboard, `php artisan test` hijau.

## Fase 1 — Auth, Role & Pengaturan Toko

- [x] Migration: tambah `role` (`owner`, `kasir`), `no_hp`, `foto`, `aktif` di `users`
- [x] Middleware `role` (`role:owner`)
- [x] Seeder: 1 owner, 1 kasir
- [x] CRUD User/Karyawan (owner only)
- [x] Halaman Pengaturan Toko: nama, alamat, no HP/WA, logo, footer struk, ukuran kertas struk (58/80mm)
- [x] Ganti password & profil sendiri (+ foto)
- [x] Login ditolak bila akun nonaktif; menonaktifkan user memutus sesinya
- [x] Owner tidak bisa hapus/nonaktifkan/menurunkan role dirinya sendiri
- [x] Menu sidebar khusus owner disembunyikan dari kasir

**Selesai bila**: kasir tidak bisa akses menu owner (ada test).

## Fase 2 — Master Data

- [x] **Layanan**: nama, kategori (kiloan/satuan), satuan (kg/pcs/m²), harga, estimasi durasi (jam), aktif
- [x] **Pelanggan**: nama, no HP (unik), alamat, catatan; cari cepat by nama/HP
- [x] **Parfum** (opsional, daftar pilihan saat order)
- [x] **Metode Pembayaran**: Tunai, Transfer, QRIS (seeder, bisa ditambah), minimal 1 aktif
- [x] No HP pelanggan dinormalisasi ke format `62xxx` (siap wa.me, unik konsisten)
- [x] Seeder contoh layanan & parfum (`MasterSeeder`)

**Selesai bila**: semua master bisa CRUD di HP tanpa scroll horizontal.

## Fase 3 — Transaksi Order (INTI)

- [x] Halaman **Order Baru** (mobile-first, 1 layar):
  - pilih/cari pelanggan, atau tambah cepat (nama + HP) inline
  - tambah item layanan → qty/berat → subtotal otomatis
  - diskon (nominal), biaya antar/jemput (opsional), catatan, parfum
  - estimasi selesai otomatis dari layanan terlama (bisa diubah)
  - pembayaran: Bayar Nanti / DP / Lunas
- [x] Generate kode invoice `LND-YYMMDD-XXXX`
- [x] Hitung total di server (jangan percaya total dari client)
- [x] **Daftar Order**: filter status, status bayar, tanggal; cari kode/nama/HP
- [x] **Detail Order**: item, riwayat status, riwayat pembayaran
- [x] Update status sesuai alur (lihat [alur-bisnis.md](alur-bisnis.md)), tercatat di `order_status_logs`
- [x] Tambah pembayaran (pelunasan) → status bayar otomatis
- [x] Batalkan order (owner only, wajib alasan)
- [x] Test: hitung total, DP/pelunasan, transisi status ilegal ditolak
- [x] Cegah hapus layanan/pelanggan/parfum/metode yang sudah dipakai order (FK `restrictOnDelete` → pesan "nonaktifkan saja")
- [x] Detail pelanggan: tampilkan riwayat order (5 terakhir + jumlah & total belanja)
- [x] Bottom nav HP: Beranda · Order · + Baru · Pelanggan (`@section('no_bottom_nav', true)` untuk menyembunyikan)
- [ ] Edit order setelah dibuat (ubah item/harga, owner only), belum dibuat, tunggu kebutuhan

**Selesai bila**: satu order bisa dibuat → diproses → diambil → lunas, dari HP.

## Fase 4 — Struk & Notifikasi WhatsApp

- [ ] Halaman cetak struk (CSS `@media print`, lebar 58/80mm), tombol Print
- [ ] Tombol "Kirim WA" → link `https://wa.me/62xxx?text=...` (nota + link lacak). Tanpa API berbayar dulu
- [ ] Template pesan WA di pengaturan (order masuk, order selesai)
- [ ] Halaman publik **Lacak Order** `/lacak/{kode}` (tanpa login, tampil status & total saja)

**Selesai bila**: kasir bisa cetak struk & kirim WA dalam 2 tap.

## Fase 5 — Dashboard & Laporan

- [ ] Dashboard: order hari ini, pendapatan hari ini, order per status (antrian), order jatuh tempo/terlambat
- [ ] Laporan Pendapatan (periode, per metode bayar)
- [ ] Laporan Order (periode, per layanan, per status)
- [ ] Laporan Piutang (order belum lunas)
- [ ] Export Excel/PDF (nanti; awalnya cukup print-friendly)

## Fase 6 — Pengeluaran & Laba Sederhana

- [ ] Kategori pengeluaran (deterjen, listrik, gaji, dll)
- [ ] Input pengeluaran harian (+ foto nota opsional)
- [ ] Laporan Laba Rugi sederhana: pendapatan − pengeluaran per periode

## Fase 7 — Lanjutan (dikerjakan bila dibutuhkan)

- [ ] Multi cabang/outlet (`outlet_id` di order, user, pengeluaran)
- [ ] Antar-jemput & kurir (role `kurir`, jadwal, status pickup/delivery)
- [ ] Member / paket deposit / poin
- [ ] Inventori bahan (stok deterjen, parfum, plastik)
- [ ] WhatsApp API otomatis (Fonnte/Wablas, via queue)
- [ ] PWA (install di HP, ikon home screen)

---

## Struktur Menu Sidebar

| Grup | Menu | Role |
|---|---|---|
| — | Dashboard | semua |
| Transaksi | Order Baru, Daftar Order | semua |
| Master | Pelanggan | semua |
| Master | Layanan, Parfum, Metode Bayar | owner |
| Keuangan | Pengeluaran | owner (kasir input saja: opsional) |
| Laporan | Pendapatan, Order, Piutang, Laba Rugi | owner |
| Pengaturan | Toko, User | owner |

Mobile: bottom nav 4 item → **Beranda · Order · + Baru · Pelanggan**, sisanya di sidebar (hamburger).
