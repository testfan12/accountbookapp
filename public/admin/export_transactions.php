<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../config/database.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Models\Transaction;
use App\Models\Customer;

// Get filters
$customerId = $_GET['customer_id'] ?? '';
$from = $_GET['from'] ?? '';
$to = $_GET['to'] ?? '';

// Fetch selected customer
$customerInfo = 'All Customers';
$customerNameForFile = 'All_Customers';
$address = 'N/A';
if (!empty($customerId)) {
    $customer = Customer::find($customerId);
    if ($customer) {
        $customerInfo = $customer->name . ' (' . $customer->phone . ')';
        $customerNameForFile = preg_replace('/[^a-zA-Z0-9]/', '_', $customer->name);
        $address = $customer->address ?? 'N/A';
    }
}

// Fetch transactions
$query = Transaction::with('customer');
if (!empty($customerId)) {
    $query->where('customer_id', $customerId);
}
if (!empty($from)) {
    $query->whereDate('created_at', '>=', $from);
}
if (!empty($to)) {
    $query->whereDate('created_at', '<=', $to);
}
$transactions = $query->orderBy('customer_id')->orderBy('created_at')->get();

// Summary calculations
$total_opening = 0;
$total_paid = 0;
foreach ($transactions as $tx) {
    $total_opening += $tx->rate * $tx->kg;
    $total_paid += $tx->paid_amount;
}
$net = $total_paid - $total_opening;
$final_due = $net < 0 ? abs($net) : 0;
$final_advance = $net > 0 ? $net : 0;

// Create Excel file
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Add Summary Info
$sheet->setCellValue('A1', 'Transaction Report Summary');
$sheet->setCellValue('A2', 'Customer:');
$sheet->setCellValue('B2', $customerInfo);
$sheet->setCellValue('A3', 'Address:');
$sheet->setCellValue('B3', $address);
$sheet->setCellValue('A4', 'From Date:');
$sheet->setCellValue('B4', $from ?: 'All');
$sheet->setCellValue('A5', 'To Date:');
$sheet->setCellValue('B5', $to ?: 'All');

$sheet->setCellValue('A7', 'Total Opening (₹)');
$sheet->setCellValue('B7', number_format($total_opening, 2));
$sheet->setCellValue('A8', 'Total Paid (₹)');
$sheet->setCellValue('B8', number_format($total_paid, 2));
$sheet->setCellValue('A9', 'Final Due (₹)');
$sheet->setCellValue('B9', number_format($final_due, 2));
$sheet->setCellValue('A10', 'Final Advance (₹)');
$sheet->setCellValue('B10', number_format($final_advance, 2));

// Add some spacing
$startRow = 12;

// Add Table Header
$headers = [
    'ID', 'Customer Name', 'Phone', 'Rate', 'KG', 
    'Opening', 'Paid Amount', 'Due Amount', 'Advance Amount', 'Date'
];

$col = 'A';
foreach ($headers as $header) {
    $sheet->setCellValue($col . $startRow, $header);
    $col++;
}

// Add Transactions
$row = $startRow + 1;
foreach ($transactions as $tx) {
    $opening = $tx->rate * $tx->kg;
    $cust = $tx->customer;

    $sheet->setCellValue("A$row", $tx->id);
    $sheet->setCellValue("B$row", $cust->name ?? '');
    $sheet->setCellValue("C$row", $cust->phone ?? '');
    $sheet->setCellValue("D$row", $tx->rate);
    $sheet->setCellValue("E$row", $tx->kg);
    $sheet->setCellValue("F$row", number_format($opening, 2));
    $sheet->setCellValue("G$row", number_format($tx->paid_amount, 2));
    $sheet->setCellValue("H$row", number_format($tx->due_amount, 2));
    $sheet->setCellValue("I$row", number_format($tx->advance_amount, 2));
    $sheet->setCellValue("J$row", date('d-m-Y H:i:s', strtotime($tx->created_at)));
    $row++;
}

// Auto-size columns
foreach (range('A', 'J') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Set Excel filename
$filename = "Transactions_" . $customerNameForFile . "_" . date('Ymd_His') . ".xlsx";
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment; filename=\"$filename\"");
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
