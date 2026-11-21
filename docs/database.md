# Rancangan Database

Uang = `unsignedBigInteger` (Rupiah). Berat/qty = `decimal(8,2)`. Semua tabel punya `timestamps`.
Tabel bawaan Laravel (`sessions`, `cache`, `jobs`, `password_reset_tokens`) tidak ditulis.

## users (Fase 1, ubah tabel bawaan)
| kolom | tipe | catatan |
|---|---|---|
| name, email, password | bawaan | |
| role | enum(`owner`,`kasir`,`kurir`) | default `kasir` (`kurir` sejak Fase 7b) |
| no_hp | string null | |
| foto | string null | path storage |
| aktif | boolean | default true |

## pengaturan (Fase 1) — 1 baris saja
nama_toko, alamat, no_hp, logo, footer_struk, ukuran_struk (`58`/`80`), template_wa_masuk, template_wa_selesai,
poin_aktif boolean (default false), rupiah_per_poin unsignedInteger (default 10000), nilai_poin unsignedInteger (default 100) — Fase 7b

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
nama, no_hp (unique, dinormalisasi `62xxx` oleh mutator `Pelanggan::setNoHpAttribute`), alamat null, catatan null, poin unsignedInteger default 0 (Fase 7b, hanya diubah lewat `Pelanggan::mutasiPoin()`)

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
| poin_ditukar | unsignedInteger | default 0 (Fase 7b) |
| potongan_poin | unsignedBigInteger | default 0, = poin_ditukar × nilai_poin saat order dibuat |
| total | unsignedBigInteger | subtotal − diskon − potongan_poin + biaya_antar |
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
- kategori_pengeluaran: nama unique(50)
- pengeluaran: kategori_pengeluaran_id FK restrict, user_id FK restrict, tanggal date (index), jumlah, keterangan null, foto_nota null (path disk `public`, file dihapus saat diganti/dihapus)

## bahan & mutasi_bahan (Fase 7)
- bahan: nama unique(50), satuan string(20) (liter/kg/pcs/pak), stok decimal(10,2), stok_minimum decimal(10,2). Menipis = `stok <= stok_minimum` (scope `Bahan::menipis()`)
- mutasi_bahan: bahan_id FK cascade, user_id FK restrict, jenis enum(`masuk`,`keluar`,`penyesuaian`), jumlah decimal(10,2) (positif; untuk `penyesuaian` = stok baru hasil hitung fisik), stok_setelah decimal(10,2), keterangan null

> Stok hanya diubah lewat `Bahan::mutasi()` (transaksi + `lockForUpdate`, keluar tidak boleh membuat stok minus), supaya selalu ada jejak di mutasi_bahan.

## riwayat_poin & pengiriman (Fase 7b)
- riwayat_poin: pelanggan_id FK cascade, order_id FK null (nullOnDelete), jenis enum(`dapat`,`tukar`,`batal`), poin int (bertanda: tukar negatif), saldo_setelah unsignedInteger, keterangan null
- pengiriman: order_id FK cascade, jenis enum(`jemput`,`antar`), kurir_id FK users null (restrictOnDelete), alamat string, jadwal datetime (index), status enum(`dijadwalkan`,`dalam_perjalanan`,`selesai`,`batal`) default `dijadwalkan`, biaya unsignedBigInteger default 0 (info, yang ditagih `orders.biaya_antar`), catatan null, selesai_at null

## Relasi
```
pelanggan 1─* orders 1─* order_items *─1 layanan
                    1─* pembayaran *─1 metode_pembayaran
                    1─* order_status_logs
                    1─* pengiriman *─1 users (kurir)
pelanggan 1─* riwayat_poin *─1 orders (null)
users 1─* orders / pembayaran / pengeluaran / mutasi_bahan
bahan 1─* mutasi_bahan
```
