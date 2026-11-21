# Panduan UI — Mobile & Tablet First

Pengguna utama: kasir di meja laundry memakai HP (360–430px) atau tablet (768–1024px). Desktop (≥1024px) untuk owner melihat laporan.

## Breakpoint (Tailwind default)

| Perangkat | Prefix | Layout |
|---|---|---|
| HP | (none) | 1 kolom, bottom nav, sidebar jadi drawer |
| Tablet | `md:` 768px | 2 kolom (form + ringkasan), tabel muncul |
| Desktop | `lg:` 1024px | sidebar tetap (sudah ada di layout), grid 3–4 kolom |

## Aturan

1. **Mobile dulu**: tulis kelas tanpa prefix untuk HP, tambah `md:`/`lg:`. Jangan desain desktop lalu "dikecilkan".
2. **Tabel → card di HP**:
   ```html
   <div id="listCard" class="md:hidden space-y-3"></div>      <!-- HP -->
   <table class="hidden md:table w-full">...</table>          <!-- tablet+ -->
   ```
   Render keduanya dari data yang sama di `loadData()`.
3. **Target sentuh** ≥ 44px: tombol `h-11`, ikon-tombol `w-11 h-11`.
4. **Input** `text-base` di HP (cegah auto-zoom iOS), `md:text-sm` boleh. Pakai `inputmode="numeric"` untuk nominal/berat, `type="tel"` untuk HP.
5. **Aksi utama** di jangkauan jempol:
   - Bottom nav fixed di HP (`lg:hidden`): Beranda · Order · **+ Baru** (menonjol) · Pelanggan.
   - Tombol simpan form panjang: sticky di bawah (`sticky bottom-0`) di HP.
   - Beri `pb-24 lg:pb-8` pada `<main>` agar konten tidak tertutup bottom nav.
6. **Modal di HP** = bottom sheet / full-screen (`w-full md:max-w-lg`, `rounded-t-2xl md:rounded-2xl`).
7. **Header layout**: `px-8` → `px-4 lg:px-8`; sembunyikan `page_description` di HP.
8. **Tanpa scroll horizontal** di 360px. Cek tiap halaman di DevTools: 360, 768, 1280.
9. **Status warna konsisten** (badge):
   | status | warna |
   |---|---|
   | antrian | slate |
   | dicuci | blue |
   | disetrika | amber |
   | siap_diambil | emerald |
   | diambil | gray |
   | batal | red |
   | belum / dp / lunas | red / amber / emerald |
   Kelas yang dibuat di JS → masukkan `safelist` di `tailwind.config.js`.
10. **Angka uang** selalu `formatRupiah()`; input nominal format ribuan saat diketik, kirim `getNominal()`.

## Halaman Order Baru (paling penting)

- HP: satu kolom berurutan → Pelanggan → Item → Ringkasan & Bayar (sticky total di bawah + tombol Simpan).
- Tablet/Desktop: kiri form pelanggan + item, kanan ringkasan sticky (`md:grid-cols-5`, kiri `col-span-3`, kanan `col-span-2`).
- Item layanan dipilih via tombol/chip besar (bukan dropdown kecil), qty dengan tombol − / + dan input desimal.

## Checklist per halaman

- [ ] Tampil benar di 360px, 768px, 1280px
- [ ] Tidak ada scroll horizontal
- [ ] Semua tombol ≥ 44px
- [ ] State loading (skeleton), kosong (`emptyTable`), error (`tableError`) ada
