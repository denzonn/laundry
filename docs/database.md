# Rancangan Database

Uang = `unsignedBigInteger` (Rupiah). Berat/qty = `decimal(8,2)`. Semua tabel punya `timestamps`.
Tabel bawaan Laravel (`sessions`, `cache`, `jobs`, `password_reset_tokens`) tidak ditulis.

## users (Fase 1, ubah tabel bawaan)
| kolom | tipe | catatan |
|---|---|---|
| name, email, password | bawaan | |
| role | enum(`owner`,`kasir`) | default `kasir` |
| no_hp | string null | |
| foto | string null | path storage |
| aktif | boolean | default true |

## pengaturan (Fase 1) — 1 baris saja
nama_toko, alamat, no_hp, logo, footer_struk, ukuran_struk (`58`/`80`), template_wa_masuk, template_wa_selesai

## layanan (Fase 2)
| kolom | tipe | catatan |
|---|---|---|
| nama | string | "Cuci Kering Setrika" |
| kategori | enum(`kiloan`,`satuan`) | |
| satuan | string | kg / pcs / m² |
| harga | unsignedBigInteger | per satuan |
| durasi_jam | unsignedSmallInteger | estimasi |
| aktif | boolean | |

## pelanggan (Fase 2)
nama, no_hp (unique), alamat null, catatan null

## parfum (Fase 2)
nama, aktif

## metode_pembayaran (Fase 2)
nama (Tunai/Transfer/QRIS), aktif

## orders (Fase 3)
| kolom | tipe | catatan |
|---|---|---|
| kode | string unique | `LND-YYMMDD-XXXX` |
| pelanggan_id | FK | |
| user_id | FK | kasir pembuat |
| parfum_id | FK null | |
| tanggal_masuk | datetime | |
| estimasi_selesai | datetime | |
| tanggal_diambil | datetime null | |
| status | enum | `antrian`,`dicuci`,`disetrika`,`siap_diambil`,`diambil`,`batal` |
| subtotal | unsignedBigInteger | Σ item |
| diskon | unsignedBigInteger | default 0 |
| biaya_antar | unsignedBigInteger | default 0 |
| total | unsignedBigInteger | subtotal − diskon + biaya_antar |
| dibayar | unsignedBigInteger | Σ pembayaran (cache) |
| status_bayar | enum(`belum`,`dp`,`lunas`) | diturunkan dari dibayar vs total |
| catatan | text null | |
| alasan_batal | string null | |

Index: `status`, `status_bayar`, `tanggal_masuk`.

## order_items (Fase 3)
order_id FK cascade, layanan_id FK, nama_layanan (snapshot), harga (snapshot), qty decimal(8,2), subtotal

> Snapshot nama & harga supaya order lama tidak berubah saat harga layanan diubah.

## order_status_logs (Fase 3)
order_id FK cascade, user_id FK, status, catatan null

## pembayaran (Fase 3)
order_id FK, metode_pembayaran_id FK, user_id FK, jumlah, tanggal, catatan null

## kategori_pengeluaran & pengeluaran (Fase 6)
- kategori_pengeluaran: nama
- pengeluaran: kategori_pengeluaran_id FK, user_id FK, tanggal, jumlah, keterangan, foto_nota null

## Relasi
```
pelanggan 1─* orders 1─* order_items *─1 layanan
                    1─* pembayaran *─1 metode_pembayaran
                    1─* order_status_logs
users 1─* orders / pembayaran / pengeluaran
```
