<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ReportController extends Controller
{
    //
    public function reportReplenish(Request $request)
    {
        if ($request->ajax()) {
            $data = DB::select("SELECT a.*, 
                                    re.edit_replenish,
                                    re.tanggalreplenish
                                FROM fn_replenishment() a
                                LEFT JOIN (
                                    SELECT re.*, 
                                        ROW_NUMBER() OVER (PARTITION BY re.item, re.toko ORDER BY re.tanggalreplenish DESC) AS rn
                                    FROM replenish_edit re
                                ) re 
                                    ON a.kode_item = re.item 
                                    AND a.toko = re.toko
                                    AND re.rn = 1");
            return DataTables::of($data)
                ->make(true);
        }

        return view('reports.report_replenish');
    }

    
    public function exportExcelReportReplenish(Request $request) {

        $kodeItem = $request->input('kode_item'); // Ambil parameter kode_item
        $namaToko = $request->input('nama_toko');

        $query = "SELECT a.*, 
                            re.edit_replenish,
                            re.tanggalreplenish
                        FROM fn_replenishment() a
                        LEFT JOIN (
                            SELECT re.*, 
                                ROW_NUMBER() OVER (PARTITION BY re.item, re.toko ORDER BY re.tanggalreplenish DESC) AS rn
                            FROM replenish_edit re
                        ) re 
                            ON a.kode_item = re.item 
                            AND a.toko = re.toko
                            AND re.rn = 1";
        $bindings = [];
    
        if ($kodeItem) {
            $query .= " WHERE kode_item = :kode_item";
            $bindings['kode_item'] = $kodeItem;
        }

        if($namaToko) {
            $query .= " WHERE toko = :nama_toko";
            $bindings['nama_toko'] = $namaToko;
        }
    
        $data = DB::select($query, $bindings);



        // Buat spreadsheet baru
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Menambahkan judul kolom
        $sheet->setCellValue('A1', 'Kode Item');
        $sheet->setCellValue('B1', 'Nama Item');
        $sheet->setCellValue('C1', 'Toko');
        $sheet->setCellValue('D1', 'P3M');
        $sheet->setCellValue('E1', 'RPP3M');
        $sheet->setCellValue('F1', 'Omset Toko');
        $sheet->setCellValue('G1', 'Kontribusi');
        $sheet->setCellValue('H1', 'Skala Prioritas');
        $sheet->setCellValue('I1', 'Stock Gudang');
        $sheet->setCellValue('J1', 'Kebutuhan Replenishment');
        $sheet->setCellValue('K1', 'Replenished');
        $sheet->setCellValue('L1', 'Edit Replenished');

        // Menambahkan data ke dalam spreadsheet
        $row = 2; // Mulai dari baris kedua setelah header
        foreach ($data as $item) {
            $sheet->setCellValue('A' . $row, $item->kode_item);
            $sheet->setCellValue('B' . $row, $item->name_item);
            $sheet->setCellValue('C' . $row, $item->toko);
            $sheet->setCellValue('D' . $row, $item->p3m);
            $sheet->setCellValue('E' . $row, $item->rpp3m);
            $sheet->setCellValue('F' . $row, $item->omset_toko);
            $sheet->setCellValue('G' . $row, $item->kontribusi);
            $sheet->setCellValue('H' . $row, $item->skala_prioritas);
            $sheet->setCellValue('I' . $row, $item->stock_gudang);
            $sheet->setCellValue('J' . $row, $item->kebutuhan_replenishment);
            $sheet->setCellValue('K' . $row, $item->replenished);
            $sheet->setCellValue('L' . $row, $item->edit_replenish);
            $row++;
        }

        // Membuat file Excel dan mengunduhnya
        $writer = new Xlsx($spreadsheet);
        // Mengirimkan file Excel ke browser untuk diunduh
        return response()->stream(
            function() use ($writer) {
                $writer->save('php://output');
            },
            200,
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment;filename="Stock_Replenish_Report.xlsx"',
                'Cache-Control' => 'max-age=0',
                'Cache-Control' => 'max-age=1',
                'Pragma' => 'public',
                'Expires' => '0',
            ]
        );
    }

}
