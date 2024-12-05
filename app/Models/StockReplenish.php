<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockReplenish extends Model
{
    use HasFactory;
    
    protected $table = 'stock_replenish';  
     protected $primaryKey = null;
     public $incrementing = false;
    public $timestamps = false;
    // protected $id = 'kelurahan_id';

    protected $fillable = [
        'kode_item',
        'quantity'
    ];
    
   
}
