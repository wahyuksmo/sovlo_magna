<?php

namespace App\Http\Controllers;

use App\Repositories\UploadPenjualanRepository;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


class UploadPenjualanController extends Controller
{
    //
    public function index(Request $request) {
        if ($request->ajax()) {
            $search = $request->input('search.value'); // Nilai pencarian dari DataTables
            $baseQuery = "SELECT * FROM penjualan";
            $bindings = [];
    
            if (!empty($search)) {
                $baseQuery .= " WHERE no_invoice ILIKE :search
                                OR kode_customer ILIKE :search
                                OR nama_customer ILIKE :search
                                OR tgl_invoice::TEXT ILIKE :search
                                OR kode_item ILIKE :search
                                OR nama_item ILIKE :search
                                OR warehouse ILIKE :search";
                $bindings['search'] = '%' . $search . '%';
            }
    
            $baseQuery .= " LIMIT 5000";
    
            $data = DB::select($baseQuery, $bindings);
    
            return DataTables::of($data)->make(true);
        }
    
        return view('uploadpenjualan.index');
    }
    
    


    public function validationUpload(Request $request) {

        $request->validate([
            'file' => 'mimes:xlsx,xls,csv',
        ]);


        $uploadedFile = $request->file('file');
        $spreadsheet = IOFactory::load($uploadedFile);
        $worksheet = $spreadsheet->getActiveSheet();

        $data = [];

        $rules = [
            'no_invoice'     => 'required',
            'kode_customer'  => 'required',
            'nama_customer'  => 'required',
            'tgl_invoice'    => 'required',
            'kode_item'      => 'required',
            'nama_item'      => 'required',
            'warehouse_code' => 'required',
            'warehouse'      => 'required',
            'qty'            => 'required|int',
            'price'          => 'required',
            'total'          => 'required'
        ];

        $headerMapping = [
            'Nomor Invoice'     => 'no_invoice',
            'Kode Customer'     => 'kode_customer',
            'Nama Customer'     => 'nama_customer',
            'Tanggal Invoice'   => 'tgl_invoice',
            'Kode Item'         => 'kode_item',
            'Nama Item'         => 'nama_item',
            'Kode Warehouse'    => 'warehouse_code',
            'Warehouse'         => 'warehouse',
            'QTY'               => 'qty',
            'Price'             => 'price',
            'Total'             => 'total' 
        ];



        $rows = $worksheet->toArray();


        if (!empty($rows)) {
            $headerRow = array_shift($rows);
    
            foreach ($headerRow as $header) {
                if (array_key_exists($header, $headerMapping)) {
                    $key = $headerMapping[$header]; 
                    $keys[] = $key;
                }
            }
        }



        foreach ($rows as $index => $row) {

            $row = array_pad(array_slice($row, 0, 11), 11, null);

            $rowData = array_combine($keys, $row);

            if (empty(array_filter($row))) {
                continue;
            }

            // $price = str_replace(',', '.', $row[8]); // Ganti koma dengan titik
            // $total = str_replace(',', '.', $row[9]); // Ganti koma dengan titik
           
            $isValid = true;
            $validationMessage = '';
    
            foreach ($rules as $column => $rule) {
                $cellValue = $rowData[$column] ?? null;

                // if ($column === 'warehouse_code') {
                //     $isWarehouseExists = DB::table('stock_gudang')
                //         ->where('kode_gudang', $cellValue)
                //         ->exists();
    
                //     if (!$isWarehouseExists) {
                //         $isValid = false;
                //         $validationMessage = "Kode Gudang ($cellValue) tidak ditemukan di tabel Stock Gudang.";
                //         break;
                //     }
                // }

                $validator = Validator::make([$column => $cellValue], [$column => $rule]);
    
                if ($validator->fails()) {
                    $isValid = false;
                    $validationMessage = $validator->errors()->first();
                }
            }
            $rowData['status_validation'] = $isValid ? 'Success' : 'Error';
            $rowData['message_validation'] = $isValid ? 'Row Data Is Valid' : $validationMessage;
    
    
            $rowData["price"] = number_format((float)str_replace(',', '.', $rowData["price"]), 2, '.', '');
            $rowData["total"] = number_format((float)str_replace(',', '.', $rowData["total"]), 2, '.', '');
            $data[] = $rowData;
        }


    
        return response()->json([
            'success' => true,
            'data' => $data,
            'message' => 'File processed successfully..'
        ]);
        

    }


    // public function validationUpload(Request $request)
    // {
    //     $request->validate([
    //         'file' => 'required|mimes:xlsx,xls,csv',
    //     ]);

    //     $file = $request->file('file');

    //     $spreadsheet = IOFactory::load($file->getRealPath());
    //     $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

    //     $mapping = [
    //         'A' => 'no_invoice',
    //         'B' => 'kode_customer',
    //         'C' => 'nama_customer',
    //         'D' => 'tgl_invoice',
    //         'E' => 'kode_item',
    //         'F' => 'nama_item',
    //         'G' => 'warehouse',
    //         'I' => 'qty',
    //         'J' => 'price',
    //         'K' => 'total'
    //     ];

    //     $data = array_map(function ($row) use ($mapping) {
    //         $row['J'] = number_format((float)str_replace(',', '.', $row['I']), 2, '.', '');
    //         $row['K'] = number_format((float)str_replace(',', '.', $row['J']), 2, '.', '');
    //         return array_combine(
    //             array_values($mapping),
    //             array_intersect_key($row, array_flip(array_keys($mapping)))
    //         );
    //     }, array_slice($sheetData, 1));

    //     // Ambil semua nilai unik dari kolom warehouse
    //     $warehouseList = array_unique(array_column($data, 'warehouse'));

    //     // Ambil daftar kode_gudang yang valid dari tabel stock_gudang
    //     $validWarehouses = DB::table('stock_gudang')
    //         ->whereIn('kode_gudang', $warehouseList)  // Sesuaikan dengan kolom kode_gudang
    //         ->pluck('kode_gudang')
    //         ->toArray();

    //     // Filter data yang memiliki warehouse valid
    //     $validData = array_filter($data, function ($row) use ($validWarehouses) {
    //         return in_array($row['warehouse'], $validWarehouses);
    //     });

    //     $batchSize = 1000; // Set batch size
    //     $chunks = array_chunk($validData, $batchSize); // Split data into chunks of 1000

    //     DB::beginTransaction();

    //     try {
    //         foreach ($chunks as $chunk) {
    //             // Insert data valid saja
    //             DB::table('penjualan')->insert($chunk);
    //         }

    //         DB::commit();

    //         return response()->json([
    //             'success' => true,
    //             'data' => count($validData),
    //             'message' => 'File processed successfully. ' . count($validData) . ' rows inserted.'
    //         ]);
    //     } catch (\Exception $e) {
    //         DB::rollBack();

    //         return response()->json([
    //             'success' => false,
    //             'message' => 'An error occurred: ' . $e->getMessage()
    //         ], 500);
    //     }
    // }




    public function uploadAction(Request $request)
    {
        try{

            $data = UploadPenjualanRepository::upload($request->all());

            return response()->json([
                'message' => 'Data Berhasil Disimpan.',
                'data'    => $data
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

}
