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

## Pembayaran

- `status_bayar` dihitung: `dibayar = 0` → `belum`, `0 < dibayar < total` → `dp`, `dibayar >= total` → `lunas`.
- Pembayaran tidak boleh melebihi sisa tagihan (kembalian dihitung di UI untuk tunai, yang disimpan = sisa tagihan).
- Total & `dibayar` selalu dihitung ulang di server.

## Perhitungan

```
subtotal item   = harga × qty            (qty kg boleh desimal, mis. 3.5)
subtotal order  = Σ subtotal item
total           = subtotal − diskon + biaya_antar   (diskon ≤ subtotal)
estimasi        = tanggal_masuk + max(durasi_jam layanan)
```

Opsional (tanyakan ke user sebelum dibuat): minimal berat kiloan (mis. min 2 kg), pembulatan berat.

## Hak Akses

| Aksi | Owner | Kasir |
|---|---|---|
| Buat order, update status, terima pembayaran | ✓ | ✓ |
| Kelola pelanggan | ✓ | ✓ |
| Batalkan order, ubah harga item order | ✓ | ✗ |
| Master layanan/parfum/metode bayar | ✓ | ✗ |
| Laporan, pengeluaran, pengaturan, user | ✓ | ✗ |
