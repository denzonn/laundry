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
| `formatRupiah`, `toNumber`, `getNominal`, `formatTanggal`, `formatTanggalIndo`, `formatTanggalJam`, `formatBadgeStatus` | format |
| `App.loading.show()` / `App.submitLoading()` | loading overlay (di `includes/script.blade.php`) |

Komponen Blade: `<x-input>`, `<x-select>` di [resources/views/components/](resources/views/components/).
Flash session `success|error|warning|info` otomatis jadi toast SweetAlert.

Warna tema di [tailwind.config.js](tailwind.config.js): `primary`, `primary_hover`, `secondary`, `background`. Font `font-poppins`.
Kelas Tailwind yang dibentuk dinamis di JS → tambahkan ke `safelist`.

## Aturan UI: Mobile & Tablet First

Target utama: kasir pakai **HP & tablet** di meja laundry. Desktop tetap rapi. Detail di [docs/ui-guidelines.md](docs/ui-guidelines.md).

- Tulis kelas mobile dulu, lalu `md:` (tablet) dan `lg:` (desktop).
- Tabel di mobile → tampil sebagai **card list** (`md:hidden` card, `hidden md:table` tabel).
- Tombol/target sentuh minimal 44px (`h-11`). Input `text-base` di mobile (cegah zoom iOS).
- Aksi utama (Order Baru) mudah dijangkau jempol: bottom nav / FAB di mobile.

## Konvensi

- Bahasa domain & UI: **Bahasa Indonesia** (nama tabel/kolom boleh Indonesia, konsisten dengan [docs/database.md](docs/database.md)).
- Uang disimpan `unsignedBigInteger` (Rupiah bulat), berat `decimal(8,2)` kg.
- Validasi pakai Form Request bila rules > 3 field; selain itu `$request->validate()` di controller.
- Satu controller per modul, tanpa repository/service layer kecuali logika dipakai >1 tempat (mis. hitung total order).
- Otorisasi role via middleware `role:owner,kasir` (lihat Fase 1 roadmap).
- Setiap fitur yang ada logika uang/status wajib ada minimal 1 Pest feature test.

## Catatan Kondisi Base Project (sisa template HRIS)

Belum dibersihkan, lihat Fase 0 di roadmap:
- [routes/auth.php](routes/auth.php) memanggil controller Breeze yang tidak ada (Register, Password, Verify, dll). Hanya `AuthenticatedSessionController` yang ada.
- Route `dashboard` belum ada, padahal login redirect ke sana.
- [sidebar.blade.php](resources/views/includes/sidebar.blade.php) masih menu HRIS (`otoritas_id`, cabang, payroll, dll).
- [navbar.blade.php](resources/views/includes/navbar.blade.php) nama user & role masih hardcode; pakai `auth()->user()->foto` yang belum ada kolomnya.
- Tests di `tests/Feature/Auth` untuk fitur Breeze yang tidak ada.
- `jquery` di-load 2x (style.blade.php 3.6.0 & script.blade.php 3.7.1).
