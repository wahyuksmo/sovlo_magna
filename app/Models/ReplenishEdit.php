<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReplenishEdit extends Model
{
    use HasFactory;
    
    protected $table = 'replenish_edit';  

    protected $primaryKey = 'docentry';

    public $incrementing = true;

    public $timestamps = false;

    protected $fillable = [
        'tanggalreplenish',
        'toko',
        'item',
        'edit_replenish'
    ];
}
