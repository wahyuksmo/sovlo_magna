<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockGudang extends Model
{
    use HasFactory;
    
    protected $table = 'stock_gudang';  
     protected $primaryKey = null;
     public $incrementing = false;
    public $timestamps = false;
    // protected $id = 'kelurahan_id';

    protected $fillable = [
        'kode_gudang',
        'nama_gudang',
        'kode_item',
        'quantity',
        'standard_stock',
        'death_stock',
    ];
    
   
}
