<?php
include('db_connection.php');
include('common.php');
include('key_change.php');


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

$whereClause = "WHERE 1";
$orderBy = "ORDER BY products.id DESC"; // Replace 'column_name' with the actual column name you want to sort by
$sql = "SELECT products.name, products.price, products.quantity, products_category.category_value, products.date_added, products.Add_by FROM products_category 
        INNER JOIN products ON products_category.id = products.category_id 
        WHERE products.date_added BETWEEN ? AND ?
        $orderBy";
// Prepare and execute the query
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $fromDate, $toDate);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
$data = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $row['Total_Amount'] = $row['price'] * $row['quantity'];
        $row = array_merge(array_slice($row, 0, 3), array('Total_Amount' => $row['Total_Amount']), array_slice($row, 3));
        $data[] = $row;
    }
} else {
    $data[] = [
        'Product Name' => '',
        'Price' => '',
        'Quantity' => '',
        'Total Amount' => '',
        'Catogory' => '',
        'Added Date' => '',
        'Add By' => ''
    ];
}

changeArrayKey($data, 'name', 'Product Name');
changeArrayKey($data, 'price', 'Price');
changeArrayKey($data, 'quantity', 'Quantity');
changeArrayKey($data, 'Total_Amount', 'Total Amount');
changeArrayKey($data, 'category_value', 'Catogory');
changeArrayKey($data, 'date_added', 'Added Date');
changeArrayKey($data, 'Add_by', 'Add By');

// Create a new Spreadsheet object
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

    $item['Added Date'] = convertToIndianDate($item['Added Date']);
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
