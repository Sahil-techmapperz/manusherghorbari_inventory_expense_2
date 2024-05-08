<?php
include('db_connection.php');
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize user inputs
    $productCategory_name = $_POST['expenses_catogory'];
    $name = $_POST['Product_name'];
    $amount = intval($_POST['expenses_amount']);
    $expense_Date = $_POST['expense_Date'];
    $description = $_POST['description'];


    // print_r($productCategory_name);
    // print_r($expense_Date);
    // die();
    $sql = "INSERT INTO money_expenses (Catogory,Product_name, Amount, expense_Date,description) VALUES (?,?,?, ?, ?)";
    $stmt = $conn->prepare($sql);

    $stmt->bind_param("ssiss", $productCategory_name, $name, $amount, $expense_Date,$description);
    // Execute the statement
    if ($stmt->execute()) {
        header("Location: expenses.php");
        exit();
    } else {
        echo "Error updating product: " . $stmt->error;
    }

    $stmt->close(); // Close the statement
}

$conn->close();
