<!DOCTYPE html>
<html lang="id">

@php
    $rp = fn($v) => number_format($v, 0, ',', '.');
    $mm = $toko->ukuran_struk == '80' ? 80 : 58;
@endphp

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Struk {{ $order->kode }}</title>
    <style>
        @page { size: {{ $mm }}mm auto; margin: 0; }
        * { box-sizing: border-box; }
        body { margin: 0; background: #334155; font: {{ $mm == 80 ? 12 : 11 }}px/1.45 ui-monospace, Menlo, Consolas, monospace; color: #000; }
        .struk { width: {{ $mm }}mm; margin: 16px auto; padding: 4mm 3mm 6mm; background: #fff; }
        .c { text-align: center; } .b { font-weight: 700; } .up { text-transform: uppercase; }
        .row { display: flex; justify-content: space-between; gap: 6px; } .row span:last-child { text-align: right; white-space: nowrap; }
        .hr { border-top: 1px dashed #000; margin: 6px 0; }
        .big { font-size: 1.25em; }
        .logo { max-width: 60%; max-height: 18mm; display: block; margin: 0 auto 4px; filter: grayscale(1); }
        p { margin: 0; } .muted { font-size: .92em; }
        .aksi { position: sticky; bottom: 0; display: flex; gap: 8px; max-width: 420px; margin: 0 auto; padding: 12px 16px; }
        .aksi a, .aksi button { flex: 1; height: 48px; display: flex; align-items: center; justify-content: center; border-radius: 14px; border: 0;
            font: 600 15px system-ui, sans-serif; text-decoration: none; cursor: pointer; }
        .print { background: #0ea5e9; color: #fff; } .back { background: #fff; color: #334155; }
        @media print {
            body { background: #fff; }
            .struk { margin: 0; width: 100%; }
            .aksi { display: none; }
        }
    </style>
</head>

<body>
    <div class="struk">
        <div class="c">
            @if ($toko->logo)
                <img src="{{ asset('storage/' . $toko->logo) }}" alt="" class="logo">
            @endif
            <p class="b up big">{{ $toko->nama_toko }}</p>
            @if ($toko->alamat) <p>{{ $toko->alamat }}</p> @endif
            @if ($toko->no_hp) <p>{{ $toko->no_hp }}</p> @endif
        </div>

        <div class="hr"></div>
        <p class="b">{{ $order->kode }}</p>
        <div class="row"><span>Masuk</span><span>{{ $order->tanggal_masuk->format('d/m/Y H:i') }}</span></div>
        <div class="row"><span>Selesai</span><span>{{ $order->estimasi_selesai->format('d/m/Y H:i') }}</span></div>
        <div class="row"><span>Kasir</span><span>{{ $order->user->name }}</span></div>
        <div class="row"><span>Pelanggan</span><span>{{ $order->pelanggan->nama }}</span></div>

        <div class="hr"></div>
        @foreach ($order->items as $item)
            <p>{{ $item->nama_layanan }}</p>
            <div class="row muted">
                <span>{{ rtrim(rtrim(number_format($item->qty, 2, ',', '.'), '0'), ',') }} {{ $item->satuan }} x {{ $rp($item->harga) }}</span>
                <span>{{ $rp($item->subtotal) }}</span>
            </div>
        @endforeach

        <div class="hr"></div>
        <div class="row"><span>Subtotal</span><span>{{ $rp($order->subtotal) }}</span></div>
        @if ($order->diskon) <div class="row"><span>Diskon</span><span>-{{ $rp($order->diskon) }}</span></div> @endif
        @if ($order->potongan_poin) <div class="row"><span>Tukar {{ $order->poin_ditukar }} poin</span><span>-{{ $rp($order->potongan_poin) }}</span></div> @endif
        @if ($order->biaya_antar) <div class="row"><span>Antar/Jemput</span><span>{{ $rp($order->biaya_antar) }}</span></div> @endif
        <div class="row b big"><span>TOTAL</span><span>{{ $rp($order->total) }}</span></div>
        <div class="row"><span>Dibayar</span><span>{{ $rp($order->dibayar) }}</span></div>
        <div class="row b"><span>{{ $order->sisa ? 'SISA' : 'STATUS' }}</span><span>{{ $order->sisa ? $rp($order->sisa) : '** LUNAS **' }}</span></div>

        @if ($order->parfum || $order->catatan)
            <div class="hr"></div>
            @if ($order->parfum) <p>Parfum: {{ $order->parfum->nama }}</p> @endif
            @if ($order->catatan) <p>Catatan: {{ $order->catatan }}</p> @endif
        @endif

        <div class="hr"></div>
        <p class="c muted" style="word-break:break-all">Cek status cucian:<br>{{ route('lacak.show', $order->kode) }}</p>
        @if ($toko->footer_struk)
            <p class="c" style="margin-top:6px">{{ $toko->footer_struk }}</p>
        @endif
    </div>

    <div class="aksi">
        <a href="{{ route('order.show', $order) }}" class="back">&larr; Kembali</a>
        <button type="button" class="print" onclick="window.print()">Print</button>
    </div>

    @if (request('print'))
        <script>window.addEventListener('load', () => window.print());</script>
    @endif
</body>

</html>
