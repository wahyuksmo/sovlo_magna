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
            $data = DB::select("SELECT * FROM penjualan");
            return DataTables::of($data)
                ->make(true);
        }

        return view('uploadpenjualan.index');
    }


    public function validationUpload(Request $request) {

        $request->validate([
            'file' => 'mimes:xlsx,xls,csv',
        ]);


        $uploadedFile = $request->file('file');
        $spreadsheet = IOFactory::load($uploadedFile);



        // DB::table('penjualan_temp')->truncate();

        $data = [];

        $rules = [
            'no_invoice'     => 'required',
            'kode_customer'  => 'required',
            'nama_customer'  => 'required',
            'tgl_invoice'    => 'required',
            'kode_item'      => 'required',
            'nama_item'      => 'required',
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
            'Warehouse'         => 'warehouse',
            'QTY'               => 'qty',
            'Price'             => 'price',
            'Total'             => 'total' 
        ];

        $sheet = $spreadsheet->getActiveSheet();


        $dataToInsert = [];

        $lastId = DB::table('penjualan')->max('id') ?? 0;

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
            $column7 = $sheet->getCell('G' . $rowIndex)->getValue(); // Kolom C
            $column8 = $sheet->getCell('H' . $rowIndex)->getValue(); // Kolom C
            $column9 = $sheet->getCell('I' . $rowIndex)->getValue(); // Kolom C
            $column10 = $sheet->getCell('J' . $rowIndex)->getValue(); // Kolom C

            if(empty($column1) || empty($column2) || empty($column3) || empty($column4) || empty($column5) || empty($column6) || empty($column7) || empty($column8) || empty($column9) || empty($column10)) {
                continue;
            }

            $tanggal = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($column4);
            if (!$tanggal instanceof \DateTime) {
                continue;  // Jika bukan tanggal yang valid, lewati baris ini
            }

            $lastId++;

            // Menambahkan data ke dalam array
            $dataToInsert[] = [
                'id' => $lastId,
                'no_invoice' => $column1,
                'kode_customer' => $column2,
                'nama_customer' => $column3,
                'tgl_invoice' => $tanggal, // Pastikan menambahkan timestamp
                'kode_item' => $column5, // Jika Anda menggunakan timestamp Eloquent
                'nama_item' => $column6, // Jika Anda menggunakan timestamp Eloquent
                'warehouse' => $column7, // Jika Anda menggunakan timestamp Eloquent
                'qty' => $column8, // Jika Anda menggunakan timestamp Eloquent
                'price' =>  number_format((float)str_replace(',', '.',  $column9), 2, '.', ''), // Jika Anda menggunakan timestamp Eloquent
                'total' =>  number_format((float)str_replace(',', '.',  $column10), 2, '.', ''), // Jika Anda menggunakan timestamp Eloquent
            ];

            // if (count($dataToInsert) >= 50) {
            //     DB::table('penjualan_temp')->insert($dataToInsert);
            //     $dataToInsert = []; // Reset array data setelah insert
            // }
        }

        // if (!empty($dataToInsert)) {
        //     DB::table('penjualan_temp')->insert($dataToInsert);
        // }

        if (!empty($dataToInsert)) {
            DB::table('penjualan')->insert($dataToInsert);
        }

        // $rows = $worksheet->toArray();

        // dd($rows);

        // if (!empty($rows)) {
        //     $headerRow = array_shift($rows);
    
        //     foreach ($headerRow as $header) {
        //         if (array_key_exists($header, $headerMapping)) {
        //             $key = $headerMapping[$header]; 
        //             $keys[] = $key;
        //         }
        //     }
        // }





    
        // foreach ($rows as $index => $row) {
        //     $rowData = array_combine($keys, $row);
            

        //     // if($index == 0) continue; 


        //     // dd(number_format((float)$row[9], 2, ',', '.'));
        //     // $price = str_replace(',', '.', $row[8]); // Ganti koma dengan titik
        //     // $total = str_replace(',', '.', $row[9]); // Ganti koma dengan titik
        //     // $header[] =  array (

        //     //     'no_invoice' => $row[0],
        //     //     'kode_customer' => $row[1],
        //     //     'nama_customer' => $row[2],
        //     //     'tgl_invoice' => $row[3],
        //     //     'kode_item' => $row[4],
        //     //     'nama_item' => $row[5],
        //     //     'warehouse' => $row[6],
        //     //     'qty' => $row[7],
        //     //     'price' =>  number_format((float)str_replace(',', '.', $row[8]), 2, '.', ''), // Format numeric 18,2
        //     //     'total' => number_format((float)str_replace(',', '.', $row[9]), 2, '.', ''), 

        //     // );
                



        //     // dd($header);
    
        //     // dd($rowData);
        //     // $isValid = true;
        //     // $validationMessage = '';
    
        //     // foreach ($rules as $column => $rule) {
        //     //     $cellValue = $rowData[$column] ?? null;
        //     //     $validator = Validator::make([$column => $cellValue], [$column => $rule]);
    
        //     //     if ($validator->fails()) {
        //     //         $isValid = false;
        //     //         $validationMessage = $validator->errors()->first();
        //     //     }
        //     // }
        //     // $rowData['status_validation'] = $isValid ? 'Success' : 'Error';
        //     // $rowData['message_validation'] = $isValid ? 'Row Data Is Valid' : $validationMessage;
    
    
        //     $rowData["price"] = number_format((float)str_replace(',', '.', $rowData["price"]), 2, '.', '');
        //     $rowData["total"] = number_format((float)str_replace(',', '.', $rowData["total"]), 2, '.', '');
        //     $data[] = $rowData;
        // }
        // DB::table('penjualan_temp')->insert($data);



        
        return response()->json([
            'success' => true,
            'data' => count($dataToInsert),
            'message' => 'File processed successfully. ' . count($dataToInsert) . ' rows inserted.'
        ]);
        

    }


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
