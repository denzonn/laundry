<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = ['layanan_id', 'nama_layanan', 'satuan', 'harga', 'qty', 'subtotal'];

    protected $casts = ['harga' => 'integer', 'qty' => 'float', 'subtotal' => 'integer'];
}
