# Alur Bisnis

## Alur Order

```
[Pelanggan datang] → Kasir buat Order Baru → cetak struk / kirim WA
        ↓
   antrian → dicuci → disetrika → siap_diambil → diambil
        ↘──────────── batal (owner, wajib alasan) ─────╯ (sebelum diambil)
```

Aturan transisi status:
- Hanya maju 1 langkah, atau lompat ke `siap_diambil` (layanan tanpa setrika, mis. "Cuci Kering").
- `diambil` hanya boleh jika `status_bayar = lunas` (kasir bisa terima pelunasan di layar yang sama).
- `batal` hanya owner, hanya sebelum `diambil`. Pembayaran yang sudah masuk dicatat sebagai refund manual (catatan).
- Setiap perubahan → 1 baris `order_status_logs`.
- Saat jadi `siap_diambil` → tampilkan tombol "Kirim WA: laundry selesai".

## Member & Poin (opsional, default mati)

Diatur di Pengaturan Toko: `poin_aktif`, `rupiah_per_poin` (default 10.000), `nilai_poin` (default 100).

- **Dapat**: saat order jadi `diambil` (pasti lunas) → `floor(total / rupiah_per_poin)` poin, sekali per order (`Order::beriPoin`, dipanggil di `ubahStatus`).
- **Tukar**: di Order Baru, maks `min(saldo, floor((subtotal − diskon) / nilai_poin))`. Server menghitung `potongan_poin = poin_ditukar × nilai_poin` dan mengurangi saldo dalam transaksi yang sama.
- **Batal**: order batal → poin yang ditukar dikembalikan (riwayat `batal`). Poin yang sudah didapat tidak berubah (order `diambil` tidak bisa dibatalkan).
- Saldo hanya diubah lewat `Pelanggan::mutasiPoin()` (lock + baris `riwayat_poin`).

## Antar / Jemput

```
Detail Order → Jadwalkan (jemput/antar, alamat, jadwal, kurir)
   dijadwalkan → dalam_perjalanan → selesai
        ↘────────── batal (owner/kasir) ─╯ (sebelum selesai)
```

- Kurir melihat tugasnya di **Tugas Saya** (hari ini / besok / nanti), tombol Maps, telepon/WA pelanggan, sisa tagihan.
- Kurir hanya boleh mengubah status tugas miliknya (selain itu 403) dan tidak bisa membatalkan.
- `pengiriman.biaya` hanya info ongkos; yang ditagih tetap `orders.biaya_antar`.
- Kurir tidak mencatat pembayaran: uang tunai diserahkan ke kasir, kasir yang mencatat di Detail Order.

## Pembayaran

- `status_bayar` dihitung: `dibayar = 0` → `belum`, `0 < dibayar < total` → `dp`, `dibayar >= total` → `lunas`.
- Pembayaran tidak boleh melebihi sisa tagihan (kembalian dihitung di UI untuk tunai, yang disimpan = sisa tagihan).
- Total & `dibayar` selalu dihitung ulang di server.

## Perhitungan

```
subtotal item   = harga × qty            (qty kg boleh desimal, mis. 3.5)
subtotal order  = Σ subtotal item
potongan_poin   = poin_ditukar × nilai_poin         (0 bila tidak tukar poin)
total           = subtotal − diskon − potongan_poin + biaya_antar
                  (diskon ≤ subtotal, potongan_poin ≤ subtotal − diskon → total ≥ biaya_antar)
estimasi        = tanggal_masuk + max(durasi_jam layanan)
```

Opsional (tanyakan ke user sebelum dibuat): minimal berat kiloan (mis. min 2 kg), pembulatan berat.

## Hak Akses

| Aksi | Owner | Kasir | Kurir |
|---|---|---|---|
| Buat order, update status, terima pembayaran, tukar poin | ✓ | ✓ | ✗ |
| Kelola pelanggan | ✓ | ✓ | ✗ |
| Jadwalkan / tugaskan / batalkan antar-jemput | ✓ | ✓ | ✗ |
| Lihat & ubah status tugas antar-jemput miliknya (Tugas Saya) | ✗ | ✗ | ✓ |
| Batalkan order, ubah harga item order | ✓ | ✗ | ✗ |
| Master layanan/parfum/metode bayar | ✓ | ✗ | ✗ |
| Laporan, pengeluaran, pengaturan, user | ✓ | ✗ | ✗ |
| Profil sendiri | ✓ | ✓ | ✓ |
