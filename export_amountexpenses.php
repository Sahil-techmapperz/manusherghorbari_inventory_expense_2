<?php
// Include database connection
include('db_connection.php');
include('common.php');
include('key_change.php');

// Start session
session_start();

// Include PhpSpreadsheet
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$category = $_POST['category'];
$start_date = $_POST['start_Date'];
$end_date = $_POST['end_Date'];

if ($category == 0) {
        $sql = "SELECT 
        Catogory,
        Product_name,
        Amount, 
        description, 
        expense_Date 
    FROM money_expenses 
    ORDER BY money_expenses.expenses_id DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
} else {
    $sql = "SELECT 
            Catogory,
            Product_name,
            Amount, 
            description, 
            expense_Date  
    FROM money_expenses 
    WHERE money_expenses.expense_Date BETWEEN ? AND ? 
    AND money_expenses.Catogory = ?
    ORDER BY money_expenses.expenses_id DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $start_date, $end_date, $category);
    $stmt->execute();
}
$result = $stmt->get_result();
$stmt->close();
$data = [];

// Fetch data
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
} else {
    $data = [
        [
            'Catogory'=> '',
            'Product_name'=> '',
            'Amount'=> '', 
            'description'=> '', 
            'expense_Date'=> ''
        ]
    ];
}
// echo '<pre>';
// print_r($data);
// die();

changeArrayKey($data, 'Catogory', 'Category');
changeArrayKey($data, 'Product_name', 'Product Name');
changeArrayKey($data, 'Amount', 'Expense Amount');
changeArrayKey($data, 'description', 'Description');
changeArrayKey($data, 'expense_Date', 'Expense Date');

// echo '<pre>';
// print_r($data);
// die();
$spreadsheet = new Spreadsheet();

// Set the worksheet title
$spreadsheet->getActiveSheet()->setTitle('Products');

// Set headers
$headers = array_keys($data[0]);
$spreadsheet->getActiveSheet()->fromArray($headers, NULL, 'A1');

// Make all headers bold
$highestColumn = $spreadsheet->getActiveSheet()->getHighestColumn();
$headerRange = 'A1:' . $highestColumn . '1';
$spreadsheet->getActiveSheet()->getStyle($headerRange)->applyFromArray([
    'font' => [
        'bold' => true,
    ],
]);

// Set data
$row = 2;
foreach ($data as $item) {

    $item['Expense Date'] = convertToIndianDate($item['Expense Date']);
    $spreadsheet->getActiveSheet()->fromArray($item, NULL, 'A' . $row);
    $row++;
}

// Create Excel file
$writer = new Xlsx($spreadsheet);
$filename = 'products.xlsx';
$writer->save($filename);

// Download the file
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');
$writer->save('php://output');
?>