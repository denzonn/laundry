# CLAUDE.md

Sistem Manajemen Laundry, dibangun di atas base project Laravel milik user.
Roadmap & detail ada di [docs/](docs/), baca [docs/roadmap.md](docs/roadmap.md) dulu sebelum mengerjakan fitur.

## Stack

- **Backend**: Laravel 12, PHP 8.2+, MySQL (Laragon). DB: `DB_DATABASE` di `.env`.
- **Frontend**: Blade + Tailwind CSS 3 (via PostCSS) + Vite, jQuery 3, SweetAlert2, Select2, Flatpickr, Font Awesome 6 (CDN).
- **Test**: Pest 4 (`php artisan test` / `./vendor/bin/pest`).
- **Format**: Laravel Pint (`./vendor/bin/pint`).

## Perintah

```bash
composer dev            # serve + queue + pail + vite sekaligus
npm run dev             # vite saja
npm run build           # build aset produksi
php artisan migrate --seed
php artisan test
./vendor/bin/pint
```

## Pola Base Project (WAJIB diikuti)

Halaman CRUD memakai pola **Blade shell + AJAX JSON**:

1. Route `GET /xxx` → return view (layout `layouts.app`, section `title`, `page_title`, `page_description`, `content`, push `addon-script`).
2. Route `GET /xxx/data` → return JSON paginate (`->paginate()`), dirender di JS.
3. Store/update/delete → JSON `{ message }`, error validasi 422 bawaan Laravel.

Helper JS global (di [resources/js/](resources/js/), dipakai, jangan dibuat ulang):

| Helper | Kegunaan |
|---|---|
| `ajaxRequest({url,type,data,before,success,error,complete})` | wrapper `$.ajax`, error default SweetAlert |
| `renderPagination(res, 'loadData', '#pagination')` | pagination dari response paginate |
| `renderInfo(selector, res)` | info "menampilkan x dari y" |
| `loadingSkeleton(selector, rows, skeleton.table)` | skeleton loading |
| `emptyTable(colspan, title, subtitle)` / `tableError(colspan)` | state kosong / gagal |
| `debounceSearch(selector, cb)` / `refreshData(btn, search, cb)` | search & refresh |
| `hapusData({url,name,reload})` | konfirmasi + delete |
| `initSelect2()` / `initDateRangePicker(...)` | select & date range |
| `showModal(id)` / `closeModal(id)` | modal (`.modal-overlay` > `.modal-content`) |
| `showToast(msg, type)` | toast |
| `esc(str)` | escape HTML, **wajib** untuk data user yang dirender lewat template string |
| `formatHp('62812...')` | tampil `0812-xxxx-xxx` |
| `badgeOrder(status)` | badge status order / status bayar; warna & label dari `Order::WARNA` / `Order::LABEL` (global `window.WARNA_ORDER`) |
| `formError(xhr, form)` | error handler form AJAX: tutup loading, 422 → isi `<p class="err" data-for="field">` |
| `formatRupiah`, `toNumber`, `getNominal`, `formatTanggal`, `formatTanggalIndo`, `formatTanggalJam`, `formatBadgeStatus` | format |
| `App.loading.show()` / `App.submitLoading()` | loading overlay (di `includes/script.blade.php`) |

Komponen Blade `<x-input>`, `<x-select>` di [resources/views/components/](resources/views/components/) masih **kosong**, jangan dipakai sebelum diisi.
CSRF untuk semua `$.ajax` sudah di-set global (`$.ajaxSetup` di `includes/script.blade.php`).
`App.submitLoading()` aktif global di layout: setiap `submit` form memunculkan loading, jadi form AJAX wajib memanggil `App.loading.hide()` di **awal** `success`/`error` (bukan di `complete`, karena `hide()` = `Swal.close()` yang akan menutup alert error).
Flash session `success|error|warning|info` otomatis jadi toast SweetAlert.

Warna tema di [tailwind.config.js](tailwind.config.js): `primary`, `primary_hover`, `secondary`, `background`. Font `font-poppins`.
Kelas Tailwind yang dibentuk dinamis di JS → tambahkan ke `safelist`.

## Aturan UI: Mobile & Tablet First

Target utama: kasir pakai **HP & tablet** di meja laundry. Desktop tetap rapi. Detail di [docs/ui-guidelines.md](docs/ui-guidelines.md).

- Tulis kelas mobile dulu, lalu `md:` (tablet) dan `lg:` (desktop).
- Tabel di mobile → tampil sebagai **card list** (`md:hidden` card, `hidden md:table` tabel).
- Tombol/target sentuh minimal 44px (`h-11`). Input `text-base` di mobile (cegah zoom iOS).
- Aksi utama (Order Baru) mudah dijangkau jempol: bottom nav / FAB di mobile.

### Tampilan Tiap Halaman Harus Unik

Jangan copy-paste satu template CRUD "judul + tombol + tabel" ke semua halaman. Setiap halaman dirancang sesuai isi & tugasnya:
- Pilih layout yang paling cocok dengan datanya: grid kartu (user, layanan), list/timeline (order, riwayat status), form + preview langsung (pengaturan struk), kanban per status (antrian order), ringkasan angka + grafik (dashboard/laporan).
- Beri tiap halaman identitas visual sendiri: header/hero kecil dengan ikon & aksen warna khas modul, ilustrasi state kosong yang relevan (bukan ikon folder generik), komposisi berbeda.
- Tetap konsisten di **dasar**: warna tema, font, radius (`rounded-2xl`), ukuran tombol, badge status, helper JS. Unik di komposisi, bukan di sistem desain.
- Sebelum membuat halaman baru, cek halaman yang sudah ada supaya tidak mengulang layout yang sama.

## Konvensi

- Logika status & uang order ada di model [Order.php](app/Models/Order.php) (`ubahStatus`, `bayar`, `statusBerikutnya`, `kodeBaru`). Controller hanya validasi + transaksi DB + `lockForUpdate`. Jangan duplikasi aturan ini di JS selain untuk tampilan.
- Pesan WhatsApp dibangun di PHP satu tempat: `Order::pesanWa('masuk'|'selesai'|'tagihan')` / `Order::waUrl(...)` (template dari `Pengaturan::toko()`, `tagihan` dari `Order::TEMPLATE_TAGIHAN`). Jangan merakit pesan WA di JS.
- Kriteria order terlambat & piutang ada di scope `Order::terlambat()` / `Order::piutang()`; label status bayar di `Order::LABEL_BAYAR`. Pakai ini, jangan tulis ulang kondisinya.
- Dashboard & laporan (read-only) dirender server-side di Blade, filter lewat query string `?start=&end=` (partial [laporan/_periode](resources/views/laporan/_periode.blade.php)), bukan pola AJAX JSON. Query agregat di controller, hanya fungsi SQL yang jalan di MySQL & SQLite (`DATE()`, bukan `DATE_FORMAT`).
- Cetak: layout sudah menyembunyikan sidebar/header/footer/bottom nav saat print; di halaman pakai `print:hidden` untuk tombol dan `hidden print:block` untuk judul cetak.
- Halaman publik (`/lacak`) tidak memakai `layouts.app`; kode order bisa ditebak, jadi jangan tampilkan no HP/alamat/item, nama pelanggan disamarkan.
- Hapus master yang dipakai order → FK `restrictOnDelete` → handler di [bootstrap/app.php](bootstrap/app.php) membalas 422 dengan pesan jelas.
- Bottom nav HP ada di layout; elemen `fixed bottom-*` di halaman harus di atas `bottom-16` di HP/tablet, atau sembunyikan nav dengan `@section('no_bottom_nav', true)`.
- Layout `<main>` memakai `overflow-x-clip` (bukan `hidden`) agar `sticky` di dalam halaman tetap jalan.
- Data kecil (layanan, parfum, metode) → endpoint `data` tanpa pagination; data besar (pelanggan, order) → `paginate()`.

- Bahasa domain & UI: **Bahasa Indonesia** (nama tabel/kolom boleh Indonesia, konsisten dengan [docs/database.md](docs/database.md)).
- Uang disimpan `unsignedBigInteger` (Rupiah bulat), berat `decimal(8,2)` kg.
- Validasi pakai Form Request bila rules > 3 field; selain itu `$request->validate()` di controller.
- Satu controller per modul, tanpa repository/service layer kecuali logika dipakai >1 tempat (mis. hitung total order).
- Otorisasi role via middleware `role:owner,kasir` (lihat Fase 1 roadmap).
- Setiap fitur yang ada logika uang/status wajib ada minimal 1 Pest feature test.

## Menu Sidebar

Menu didefinisikan sebagai array di [sidebar.blade.php](resources/views/includes/sidebar.blade.php): `[nama_route, label, ikon]`.
Item hanya tampil bila route-nya sudah terdaftar (`Route::has`), jadi cukup buat route dengan nama yang sama.

## Testing

`phpunit.xml` memakai sqlite `:memory:`, jadi aman dari DB MySQL. Jangan dihapus, karena `RefreshDatabase` menghapus isi database.
Login seeder: `owner@laundry.test` / `password` (owner), `kasir@laundry.test` / `password` (kasir).
