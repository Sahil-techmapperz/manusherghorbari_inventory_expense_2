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

$from_Date = $_POST['from_date'];
$to_Date = $_POST['to_date'];

$dateTime = new DateTime($from_Date, new DateTimeZone('UTC'));
$dateTime->modify('+23 hours +51 minutes +28 seconds');
$dateTime->setTimezone(new DateTimeZone('America/New_York'));
$fromDate = $dateTime->format('Y-m-d H:i:s');

$dateTime = new DateTime($to_Date, new DateTimeZone('UTC'));
$dateTime->modify('+23 hours +51 minutes +28 seconds');
$dateTime->setTimezone(new DateTimeZone('America/New_York'));
$toDate = $dateTime->format('Y-m-d H:i:s');


$sql = "SELECT 
            product_balances.Product_name as name, 
            product_expenses.Expenses_quantity, 
            products_category.category_value, 
            product_expenses.Expenses_by, 
            product_expenses.created_at
        FROM 
        product_expenses
        INNER JOIN 
        product_balances ON product_balances.Product_Balances_id = product_expenses.Product_id  
        INNER JOIN 
        products_category ON products_category.id = product_balances.Product_catogory_id
        WHERE 
            product_expenses.Created_at BETWEEN ? AND ?
        ORDER BY 
            product_expenses.Product_Expenses_id DESC";

// Prepare and execute the query
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $fromDate, $toDate);
$stmt->execute();
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
            'name' => '',
            'Expenses_quantity' => '',
            'category_value' => '',
            'Expenses_by' => '',
            'created_at' => ''
        ]
    ];
}

changeArrayKey($data, 'name', 'Product Name');
changeArrayKey($data, 'Expenses_quantity', 'Quantity');
changeArrayKey($data, 'category_value', 'Catogory');
changeArrayKey($data, 'Expenses_by', 'Expense By');
changeArrayKey($data, 'created_at', 'Expenses Date');

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
    if ($item['Quantity'] === 0) {
        $item['Quantity'] = '0';
    }

    $item['Expenses Date'] = convertToIndianDate($item['Expenses Date']);
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
