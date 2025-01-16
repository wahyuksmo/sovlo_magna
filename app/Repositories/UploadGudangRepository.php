<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;
use App\Models\Apilog;
use App\Libraries\Helper;
use App\Models\StockGudang;
use Carbon\Carbon;
use Exception;
use Illuminate\Validation\ValidationException;

class UploadGudangRepository
{

    public static function upload($request) {

        $savedGudangs = [];
    
        DB::beginTransaction();
    
        try {
            foreach ($request as $data) {
                if ($data['status_validation'] === 'Success') {
                    // Gunakan updateOrCreate untuk cek data berdasarkan kode_gudang dan kode_item
                    $gudang = StockGudang::updateOrCreate(
                        // Kondisi pencarian
                        [
                            'kode_gudang' => $data['kode_gudang'],
                            'kode_item' => $data['kode_item']
                        ],
                        // Data yang akan diperbarui atau dibuat
                        [
                            'nama_gudang' => $data['nama_gudang'],
                            'quantity' => $data['quantity'],
                            'standard_stock' => $data['standard_stock'],
                            'death_stock' => $data['death_stock']
                        ]
                    );
    
                    $savedGudangs[] = $gudang;
                }
            }
    
            DB::commit();
            return $savedGudangs;
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