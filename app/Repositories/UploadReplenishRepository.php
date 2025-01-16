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
                    // Menggunakan updateOrCreate berdasarkan kode_item
                    $penish = StockReplenish::updateOrCreate(
                        ['kode_item' => $data['kode_item']], // Kondisi pencocokan
                        ['quantity' => $data['quantity']]   // Data yang akan diupdate atau dibuat
                    );
    
                    $savedPenishs[] = $penish;
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