<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;
use App\Models\Apilog;
use App\Libraries\Helper;
 use App\Models\ReplenishEdit;
use App\Models\StockGudang;
use App\Models\StockReplenish;
use Carbon\Carbon;
use Exception;
use Illuminate\Validation\ValidationException;

class UploadReplenishEditRepository
{



    public static function update($request) {

        
        DB::beginTransaction();

        try {

            $replenishEdit = new ReplenishEdit();
            $lastId = ReplenishEdit::orderBy('docentry', 'desc')->value('docentry');

            // Jika tabel kosong, atur lastId menjadi 0 untuk penyesuaian
            $newId = $lastId ? $lastId + 1 : 1;

            $replenishEdit->docentry = $newId;
            $replenishEdit->tanggalreplenish = Carbon::now();
            $replenishEdit->toko = $request['toko'];
            $replenishEdit->item = $request['item'];
            $replenishEdit->edit_replenish = $request['edit_replenish'];
            $replenishEdit->save();

            DB::commit();

            return $replenishEdit;
        }catch(Exception $e) {
            DB::rollBack();
            throw $e;
        }

    }


    public static function upload($request) {

        $savedReplenishEdits = [];

        DB::beginTransaction();

        try {

            foreach($request as $data) {
                
                if ($data['status_validation'] === 'Success') {

                    $replenishEdit = new ReplenishEdit();

                    // Mendapatkan ID terakhir dari tabel penjualan
                    $lastId = ReplenishEdit::orderBy('docentry', 'desc')->value('docentry');

                    // Jika tabel kosong, atur lastId menjadi 0 untuk penyesuaian
                    $newId = $lastId ? $lastId + 1 : 1;

                    $replenishEdit->docentry = $newId;
                    $replenishEdit->tanggalreplenish = Carbon::now();
                    $replenishEdit->toko = $data['toko'];
                    $replenishEdit->item = $data['item'];
                    $replenishEdit->edit_replenish = $data['edit_replenish'];
                    $replenishEdit->save();

                    $savedReplenishEdits[] = $replenishEdit;
                }

            }

            DB::commit();
            return $savedReplenishEdits;
        }catch(ValidationException $e) {
            DB::rollback();
            throw $e;
        } catch(Exception $e) {
            DB::rollback();
            throw $e;
        }

    }

}

?>