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

                    if (!DB::table('stock_gudang')->where('kode_gudang', $data['warehouse_code'])->exists()) {
                        continue;
                    }
    
                    if ($existingPenjualan) {
                        // Jika data ditemukan, perbarui data yang ada
                        $updateData = [];
    
                        // Periksa dan perbarui hanya jika ada perubahan
                        if ($existingPenjualan->nama_customer != $data['nama_customer']) {
                            $updateData['nama_customer'] = $data['nama_customer'];
                        }
                        if ($existingPenjualan->tgl_invoice != $data['tgl_invoice']) {
                            $updateData['tgl_invoice'] = $data['tgl_invoice'];
                        }
                        if ($existingPenjualan->nama_item != $data['nama_item']) {
                            $updateData['nama_item'] = $data['nama_item'];
                        }
                        if ($existingPenjualan->warehouse != $data['warehouse_code']) {
                            $updateData['warehouse'] = $data['warehouse_code'];
                        }
                        if ($existingPenjualan->qty != $data['qty']) {
                            $updateData['qty'] = $data['qty'];
                        }
                        if ($existingPenjualan->price != $data['price']) {
                            $updateData['price'] = $data['price'];
                        }
                        if ($existingPenjualan->total != $data['total']) {
                            $updateData['total'] = $data['total'];
                        }
    
                        // Jika ada data yang perlu diperbarui
                        if (!empty($updateData)) {
                            Penjualan::where('no_invoice', $data['no_invoice'])
                                ->where('kode_customer', $data['kode_customer'])
                                ->where('kode_item', $data['kode_item'])
                                ->where('warehouse', $data['warehouse_code'])
                                ->update($updateData);
                        }
    
                        // Menyimpan data yang diupdate ke array
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