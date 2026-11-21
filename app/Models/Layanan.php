<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Layanan extends Model
{
    protected $table = 'layanan';

    protected $fillable = ['nama', 'kategori', 'satuan', 'harga', 'durasi_jam', 'aktif'];

    protected $casts = ['harga' => 'integer', 'durasi_jam' => 'integer', 'aktif' => 'boolean'];
}
