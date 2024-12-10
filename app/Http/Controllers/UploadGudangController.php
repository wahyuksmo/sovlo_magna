<?php

namespace App\Http\Controllers;

use App\Repositories\UploadGudangRepository;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


class UploadGudangController extends Controller
{
    //
    public function index(Request $request) {
        if ($request->ajax()) {
            $search = $request->input('search.value'); // Nilai pencarian dari DataTables
            $query = "SELECT * FROM stock_gudang";
            $bindings = [];
    
            // Cek apakah ada pencarian
            if (!empty($search)) {
                $query .= " WHERE kode_gudang ILIKE :search 
                            OR nama_gudang ILIKE :search";
                $bindings['search'] = '%' . $search . '%';
            }
    
            // Tetap batasi hasil query hingga 5000
            $query .= " LIMIT 5000";
    
            // Jalankan query
            $data = DB::select($query, $bindings);
    
            return DataTables::of($data)
                ->make(true);
        }
    
        return view('uploadgudang.index');
    }
    
    


    // public function validationUpload(Request $request) {

    //     $request->validate([
    //         'file' => 'mimes:xlsx,xls,csv',
    //     ]);


    //     $uploadedFile = $request->file('file');
    //     $spreadsheet = IOFactory::load($uploadedFile);
    //     $worksheet = $spreadsheet->getActiveSheet();


    //     $data = [];

    //     $rules = [
    //         'kode_gudang'   => 'required',
    //         'nama_gudang'   => 'required',
    //         'kode_item'     => 'required',
    //         'quantity'      => 'int',
    //         'standard_stock'=> 'int',
    //         'death_stock'   => 'int'
    //     ];

    //     $headerMapping = [
    //         'Kode Gudang'   => 'kode_gudang',
    //         'Nama Gudang'   => 'nama_gudang',
    //         'Kode Item'     => 'kode_item',
    //         'Quantity'      => 'quantity',
    //         'Standard Stock'=> 'standard_stock',
    //         'Death Stock'   => 'death_stock'
    //     ];

    //     $rows = $worksheet->toArray();

    //     if (!empty($rows)) {
    //         $headerRow = array_shift($rows);
    
    //         foreach ($headerRow as $header) {
    //             if (array_key_exists($header, $headerMapping)) {
    //                 $key = $headerMapping[$header]; 
    //                 $keys[] = $key;
    //             }
    //         }
    //     }


    //     foreach ($rows as $index => $row) {
    //         $rowData = array_combine($keys, $row);
    
    //         $isValid = true;
    //         $validationMessage = '';
    
    //         foreach ($rules as $column => $rule) {
    //             $cellValue = $rowData[$column] ?? null;
    //             $validator = Validator::make([$column => $cellValue], [$column => $rule]);
    
    //             if ($validator->fails()) {
    //                 $isValid = false;
    //                 $validationMessage = $validator->errors()->first();
    //             }
    //         }
    //         $rowData['status_validation'] = $isValid ? 'Success' : 'Error';
    //         $rowData['message_validation'] = $isValid ? 'Row Data Is Valid' : $validationMessage;
    
    
    //         $data[] = $rowData;
    //     }

        
    //     return response()->json([
    //         'success' => true,
    //         'data' => $data,
    //         'message' => 'File processed successfully.'
    //     ]);

    // }

    public function validationUploadOld(Request $request) {

        $request->validate([
            'file' => 'mimes:xlsx,xls,csv',
        ]);


        $uploadedFile = $request->file('file');
        $spreadsheet = IOFactory::load($uploadedFile);


        $sheet = $spreadsheet->getActiveSheet();

        $dataToInsert = [];
        foreach ($sheet->getRowIterator() as $rowIndex => $row) {
            // Lewati header jika ada (misalnya baris pertama)
            if ($rowIndex === 1) {
                continue;
            }

            // Ambil data dari kolom tertentu (A, B, C, ...)
            $column1 = $sheet->getCell('A' . $rowIndex)->getValue(); // Kolom A
            $column2 = $sheet->getCell('B' . $rowIndex)->getValue(); // Kolom B
            $column3 = $sheet->getCell('C' . $rowIndex)->getValue(); // Kolom C
            $column4 = $sheet->getCell('D' . $rowIndex)->getValue(); // Kolom C
            $column5 = $sheet->getCell('E' . $rowIndex)->getValue(); // Kolom C
            $column6 = $sheet->getCell('F' . $rowIndex)->getValue(); // Kolom C
          

            if(empty($column1) || empty($column2) || empty($column3)) {
                continue;
            }


            // Menambahkan data ke dalam array
            $dataToInsert[] = [
                'kode_gudang' => $column1,
                'nama_gudang' => $column2,
                'kode_item' => $column3,
                'quantity' => $column4,
                'standard_stock' => $column5,
                'death_stock' => $column6,
            ];
            

            // if (count($dataToInsert) >= 50) {
            //     DB::table('penjualan_temp')->insert($dataToInsert);
            //     $dataToInsert = []; // Reset array data setelah insert
            // }
        }

        if (!empty($dataToInsert)) {
            DB::table('stock_gudang')->insert($dataToInsert);
        }

        
        return response()->json([
            'success' => true,
            'data' => count($dataToInsert),
            'message' => 'File processed successfully. ' . count($dataToInsert) . ' rows inserted.'
        ]);

    }


    public function validationUpload(Request $request) {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048',
        ]);

        $file = $request->file('file');


        $spreadsheet = IOFactory::load($file->getRealPath());
        $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

        $mapping = [
            'A' => 'kode_gudang',
            'B' => 'nama_gudang',
            'C' => 'kode_item',
            'D' => 'quantity',
            'E' => 'standard_stock',
            'F' => 'death_stock',
        ];


        $data = array_map(function ($row) use ($mapping) {
            return array_combine(
                array_values($mapping),
                array_intersect_key($row, array_flip(array_keys($mapping)))
            );
        }, array_slice($sheetData, 1));


        $batchSize = 1000; // Set batch size
        $chunks = array_chunk($data, $batchSize); // Split data into chunks of 1000

        DB::beginTransaction();

        try {

            foreach($chunks as $chunk) {
                DB::table('stock_gudang')->insert($chunk);
            }
            

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => count($data),
                'message' => 'File processed successfully. ' . count($data) . ' rows inserted.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }

    }


    public function uploadAction(Request $request)
    {
        try{

            $data = UploadGudangRepository::upload($request->all());

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
