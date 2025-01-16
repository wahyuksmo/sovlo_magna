<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;
use App\Models\Apilog;
use App\Libraries\Helper;
use App\Models\StockGudang;
use App\Models\StockReplenish;
use Carbon\Carbon;
use Exception;
use Illuminate\Validation\ValidationException;

class UploadReplenishRepository
{

    public static function upload($request) {

        $savedPenishs = [];
        
        DB::beginTransaction();
        
        try {
            foreach ($request as $data) {
                if ($data['status_validation'] === 'Success') {
                    // Cek apakah data sudah ada berdasarkan kode_item
                    $penish = StockReplenish::where('kode_item', $data['kode_item'])->first();
    
                    if ($penish) {
                        // Jika data ditemukan, perbarui quantity hanya jika ada perubahan
                        if ($penish->quantity != $data['quantity']) {
                            // Gunakan update() untuk memperbarui tanpa menggunakan save()
                            StockReplenish::where('kode_item', $data['kode_item'])
                                ->update(['quantity' => $data['quantity']]);
                        }
                    } else {
                        // Jika data tidak ditemukan, buat data baru tanpa save()
                        StockReplenish::create([
                            'kode_item' => $data['kode_item'],
                            'quantity' => $data['quantity']
                        ]);
                    }
    
                    // Ambil data yang telah disimpan
                    $savedPenishs[] = $penish ?: StockReplenish::where('kode_item', $data['kode_item'])->first();
                }
            }
        
            DB::commit();
            return $savedPenishs;
        } catch (ValidationException $e) {
            DB::rollback();
            throw $e;
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }
    
    

}

?>