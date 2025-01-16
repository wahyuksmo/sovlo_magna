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
                    // Cek apakah data sudah ada berdasarkan kode_gudang dan kode_item
                    $existing = StockGudang::where('kode_gudang', $data['kode_gudang'])
                                          ->where('kode_item', $data['kode_item'])
                                          ->first();
        
                    // Jika data ditemukan, update data
                    if ($existing) {
                        // Hanya update quantity jika ada perubahan
                        if ($existing->quantity != $data['quantity']) {
                            StockGudang::where('kode_gudang', $data['kode_gudang'])
                                       ->where('kode_item', $data['kode_item'])
                                       ->update(['quantity' => $data['quantity']]);
                        }
                        
                        // Update kolom lainnya hanya jika ada perubahan
                        if ($existing->nama_gudang != $data['nama_gudang']) {
                            StockGudang::where('kode_gudang', $data['kode_gudang'])
                                       ->where('kode_item', $data['kode_item'])
                                       ->update(['nama_gudang' => $data['nama_gudang']]);
                        }
    
                        if ($existing->standard_stock != $data['standard_stock']) {
                            StockGudang::where('kode_gudang', $data['kode_gudang'])
                                       ->where('kode_item', $data['kode_item'])
                                       ->update(['standard_stock' => $data['standard_stock']]);
                        }
    
                        if ($existing->death_stock != $data['death_stock']) {
                            StockGudang::where('kode_gudang', $data['kode_gudang'])
                                       ->where('kode_item', $data['kode_item'])
                                       ->update(['death_stock' => $data['death_stock']]);
                        }
    
                        // Menambahkan data yang diupdate ke array
                        $savedGudangs[] = $existing;
    
                    } else {
                        // Jika data tidak ditemukan, buat data baru
                        $gudang = new StockGudang();
                        $gudang->kode_gudang = $data['kode_gudang'];
                        $gudang->kode_item = $data['kode_item'];
                        $gudang->nama_gudang = $data['nama_gudang'];
                        $gudang->quantity = $data['quantity'];
                        $gudang->standard_stock = $data['standard_stock'];
                        $gudang->death_stock = $data['death_stock'];
                        $gudang->save();  // Menyimpan data baru
                        
                        $savedGudangs[] = $gudang;  // Menambahkan data baru
                    }
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