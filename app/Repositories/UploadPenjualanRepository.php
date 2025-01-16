<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;
use App\Models\Apilog;
use App\Libraries\Helper;
use App\Models\Penjualan;
use App\Models\StockGudang;
use App\Models\StockReplenish;
use Carbon\Carbon;
use Exception;
use Illuminate\Validation\ValidationException;

class UploadPenjualanRepository
{

    public static function upload($request) {

        $savedPenjualans = [];
    
        DB::beginTransaction();
    
        try {
            foreach ($request as $data) {
                if ($data['status_validation'] === 'Success') {
                    // Cari data berdasarkan kombinasi unik
                    $existingPenjualan = Penjualan::where('no_invoice', $data['no_invoice'])
                        ->where('kode_customer', $data['kode_customer'])
                        ->where('kode_item', $data['kode_item'])
                        ->where('warehouse', $data['warehouse_code'])
                        ->first();
    
                    if ($existingPenjualan) {
                        // Jika data ditemukan, perbarui data yang ada
                        $existingPenjualan->nama_customer = $data['nama_customer'];
                        $existingPenjualan->tgl_invoice = $data['tgl_invoice'];
                        $existingPenjualan->nama_item = $data['nama_item'];
                        $existingPenjualan->warehouse = $data['warehouse_code'];
                        $existingPenjualan->qty = $data['qty'];
                        $existingPenjualan->price = $data['price'];
                        $existingPenjualan->total = $data['total'];
    
                        $existingPenjualan->save();
                        $savedPenjualans[] = $existingPenjualan;
                    } else {
                        // Jika data tidak ditemukan, buat data baru dengan ID manual
                        $lastId = Penjualan::orderBy('id', 'desc')->value('id');
                        $newId = $lastId ? $lastId + 1 : 1;
    
                        $penjualan = new Penjualan();
                        $penjualan->id = $newId;
                        $penjualan->no_invoice = $data['no_invoice'];
                        $penjualan->kode_customer = $data['kode_customer'];
                        $penjualan->nama_customer = $data['nama_customer'];
                        $penjualan->tgl_invoice = $data['tgl_invoice'];
                        $penjualan->kode_item = $data['kode_item'];
                        $penjualan->nama_item = $data['nama_item'];
                        $penjualan->warehouse = $data['warehouse_code'];
                        $penjualan->qty = $data['qty'];
                        $penjualan->price = $data['price'];
                        $penjualan->total = $data['total'];
    
                        $penjualan->save();
                        $savedPenjualans[] = $penjualan;
                    }
                }
            }
    
            DB::commit();
            return $savedPenjualans;
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