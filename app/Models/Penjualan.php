<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penjualan extends Model
{
    use HasFactory;
    
    protected $table = 'penjualan';  

    protected $primaryKey = 'id';

    public $incrementing = true;

    public $timestamps = false;

    protected $fillable = [
        'no_invoice',
        'kode_customer',
        'nama_customer',
        'tgl_invoice',
        'kode_item',
        'nama_item',
        'warehouse',
        'qty',
        'price',
        'total'
    ];
}
