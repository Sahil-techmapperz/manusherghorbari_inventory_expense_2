<?php
include('db_connection.php');
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize user inputs
    $Product_id = intval($_POST['Product_id']);
    $Expenses_quantity = intval($_POST['Expenses_quantity']);
    $Expenses_by = $_POST['Expenses_by'];
    $productCategory_name = $_POST['productCategory_name'];
    $productName_Display = $_POST['productName_Display'];

    $sql = "SELECT * FROM products WHERE name = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $productName_Display);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $selected_product_id = intval($row['id']);
    // print_r($selected_product_id);
    // die();
    // Use prepared statement to prevent SQL injection
    $sql = "INSERT INTO product_expenses (Product_id, Expenses_quantity, Expenses_by) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);

    // Bind parameters
    $stmt->bind_param("iis", $Product_id, $Expenses_quantity, $Expenses_by);

    // Execute the statement
    if ($stmt->execute()) {
        $stmt->close(); // Close the statement

        // Update product details
        $sql = "SELECT * FROM product_balances WHERE Product_Balances_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $Product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close(); // Close previous statement

        $name = htmlspecialchars($row['Product_name']);
        $quantity = intval($row['Balance_Quantity']) - intval($Expenses_quantity);
        $category = intval($row['Product_catogory_id']); // corrected category_id field

        $sql = "UPDATE product_balances SET Product_name=?, Balance_Quantity=?, Product_catogory_id=? WHERE Product_Balances_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("siii", $name, $quantity, $category, $Product_id);
        // $stmt->execute();

        // $sql = "SELECT * FROM products WHERE name = ?";
        // $stmt = $conn->prepare($sql);
        // $stmt->bind_param("s", $name);
        // $stmt->execute();
        // $result = $stmt->get_result();
        // $row = $result->fetch_assoc();

        // $amount = intval($Expenses_quantity) * intval($row['price']);
        // $current_date = date("Y-m-d");

        // $sql = "INSERT INTO money_expenses (Catogory,Product_name, Amount, expense_Date) VALUES (?, ?, ?, ?)";
        // $stmt = $conn->prepare($sql);

        // // Bind parameters
        // $stmt->bind_param("ssis", $productCategory_name, $name, $amount, $current_date);
        if ($stmt->execute()) {
            header("Location: index.php");
            exit();
        } else {
            echo "Error updating product: " . $stmt->error;
        }
    } else {
        echo "Error inserting expenses: " . $stmt->error;
    }

    $stmt->close(); // Close the statement
}

$conn->close();
