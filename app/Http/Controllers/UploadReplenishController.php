<?php

namespace App\Http\Controllers;

use App\Repositories\UploadGudangRepository;
use App\Repositories\UploadReplenishRepository;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


class UploadReplenishController extends Controller
{
    //
    public function index(Request $request) {


        if ($request->ajax()) {
            $data = DB::select("SELECT * FROM stock_replenish");
            return DataTables::of($data)
                ->make(true);
        }

        return view('uploadreplenish.index');
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
    //         'kode_item'     => 'required',
    //         'quantity'      => 'int'
    //     ];

    //     $headerMapping = [
    //         'Kode Item'     => 'kode_item',
    //         'Quantity'      => 'quantity'
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


            if(empty($column1)) {
                continue;
            }


            // Menambahkan data ke dalam array
            $dataToInsert[] = [
                'kode_item' => $column1,
                'quantity' => $column2,
            ];
            

            // if (count($dataToInsert) >= 50) {
            //     DB::table('penjualan_temp')->insert($dataToInsert);
            //     $dataToInsert = []; // Reset array data setelah insert
            // }
        }

        if (!empty($dataToInsert)) {
            DB::table('stock_replenish')->insert($dataToInsert);
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
            'A' => 'kode_item',
            'B' => 'quantity',
        ];


        $data = array_map(function ($row) use ($mapping) {
            return array_combine(
                array_values($mapping),
                array_intersect_key($row, array_flip(array_keys($mapping)))
            );
        }, array_slice($sheetData, 1));


        DB::beginTransaction();

        try {

            DB::table('stock_replenish')->insert($data);

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

            $data = UploadReplenishRepository::upload($request->all());

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
