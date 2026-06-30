<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use App\Models\RestaurantTable;
use Illuminate\Http\Request;
use Inertia\Inertia;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class ReportController extends Controller
{
    protected $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    /**
     * Display the reports page
     */
    public function index(Request $request)
    {
        $filters = $request->only(['start_date', 'end_date', 'payment_method', 'table_id']);

        $transactions = $this->reportService->getSuccessfulTransactions($filters);
        $totalRevenue = $this->reportService->getTotalRevenue($filters);
        $tables = RestaurantTable::all();

        return Inertia::render('Admin/Reports', [
            'transactions' => $transactions,
            'totalRevenue' => $totalRevenue,
            'tables' => $tables,
            'filters' => $filters
        ]);
    }

    /**
     * Export report to Excel
     */
    public function export(Request $request)
    {
        $filters = $request->only(['start_date', 'end_date', 'payment_method', 'table_id']);

        $reportData = $this->reportService->getReportData($filters);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set title
        $sheet->setCellValue('A1', 'Laporan Transaksi');
        $sheet->mergeCells('A1:J1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Add filter information
        $row = 2;
        if (!empty($filters['start_date']) || !empty($filters['end_date'])) {
            $dateRange = 'Periode: ';
            if (!empty($filters['start_date'])) {
                $dateRange .= date('d/m/Y', strtotime($filters['start_date']));
            }
            if (!empty($filters['end_date'])) {
                $dateRange .= ' - ' . date('d/m/Y', strtotime($filters['end_date']));
            }
            $sheet->setCellValue('A' . $row, $dateRange);
            $sheet->mergeCells('A' . $row . ':J' . $row);
            $row++;
        }

        $row++; // Empty row

        // Set headers
        $headers = [
            'No',
            'No. Order',
            'Meja',
            'Tanggal',
            'Item Pesanan',
            'Subtotal',
            'Voucher',
            'Diskon',
            'Harga Final',
            'Metode Pembayaran'
        ];

        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . $row, $header);
            $sheet->getStyle($col . $row)->getFont()->setBold(true);
            $sheet->getStyle($col . $row)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('4F46E5');
            $sheet->getStyle($col . $row)->getFont()->getColor()->setRGB('FFFFFF');
            $col++;
        }

        // Add borders to header
        $sheet->getStyle('A' . $row . ':J' . $row)->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);

        $row++;
        $startDataRow = $row;

        // Add data
        $no = 1;
        foreach ($reportData['data'] as $data) {
            $sheet->setCellValue('A' . $row, $no++);
            $sheet->setCellValue('B' . $row, $data['order_number']);
            $sheet->setCellValue('C' . $row, $data['table']);
            $sheet->setCellValue('D' . $row, $data['date']);
            $sheet->setCellValue('E' . $row, $data['items']);
            $sheet->setCellValue('F' . $row, $data['subtotal']);
            $sheet->setCellValue('G' . $row, $data['voucher']);
            $sheet->setCellValue('H' . $row, $data['discount']);
            $sheet->setCellValue('I' . $row, $data['final_price']);
            $sheet->setCellValue('J' . $row, ucfirst($data['payment_method']));

            // Add borders to data rows
            $sheet->getStyle('A' . $row . ':J' . $row)->getBorders()->getAllBorders()
                ->setBorderStyle(Border::BORDER_THIN);

            $row++;
        }

        // Add total revenue row
        $sheet->setCellValue('A' . $row, 'TOTAL PENDAPATAN');
        $sheet->mergeCells('A' . $row . ':H' . $row);
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->setCellValue('I' . $row, $reportData['total_revenue']);
        $sheet->getStyle('I' . $row)->getFont()->setBold(true);

        // Style total row
        $sheet->getStyle('A' . $row . ':J' . $row)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('E0E7FF');
        $sheet->getStyle('A' . $row . ':J' . $row)->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);

        // Format currency columns
        $currencyFormat = '#,##0';
        $sheet->getStyle('F' . $startDataRow . ':F' . ($row - 1))->getNumberFormat()
            ->setFormatCode($currencyFormat);
        $sheet->getStyle('H' . $startDataRow . ':H' . ($row - 1))->getNumberFormat()
            ->setFormatCode($currencyFormat);
        $sheet->getStyle('I' . $startDataRow . ':I' . $row)->getNumberFormat()
            ->setFormatCode($currencyFormat);

        // Auto-size columns
        foreach (range('A', 'J') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Set file name
        $fileName = 'Laporan_Transaksi_' . date('Y-m-d_His') . '.xlsx';

        // Create file and send as download
        $writer = new Xlsx($spreadsheet);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }
}
