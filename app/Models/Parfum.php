<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Parfum extends Model
{
    protected $table = 'parfum';

    protected $fillable = ['nama', 'aktif'];

    protected $casts = ['aktif' => 'boolean'];
}
