<?php
include('db_connection.php');
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $id = $_GET['id'];

    $sql = "SELECT * FROM products WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
    } else {
        echo "Product not found";
        exit();
    }

    $stmt->close();
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize user inputs
    // $id = $_POST['id'];
    // $name = htmlspecialchars($_POST['name']);
    // $price = floatval($_POST['price']); // Assuming price is a float, adjust as needed
    // $quantity = intval($_POST['quantity']); // Assuming quantity is an integer, adjust as needed
    // $category = intval($_POST['category']);
    // $date_added = htmlspecialchars($_POST['date_added']); // Assuming date_added is a string, adjust as needed
    // $Edit_by = htmlspecialchars($_SESSION['username']);
    // $Lastedit_date = date('Y-m-d H:i:s');
    // // Use prepared statement to prevent SQL injection
    // $sql = "UPDATE products SET name=?, price=?, quantity=?, category_id=?, date_added=?, edit_by=?, lastedit_date=?  WHERE id = ?";
    // $stmt = $conn->prepare($sql);
    // $stmt->bind_param("sdiisssi", $name, $price, $quantity, $category, $date_added, $Edit_by, $Lastedit_date, $id);


    $id = $_POST['id'];
    $name = htmlspecialchars($_POST['name']);
    $price = floatval($_POST['price']); // Assuming price is a float, adjust as needed
    $quantity = intval($_POST['quantity']); // Assuming quantity is an integer, adjust as needed
    $category = intval($_POST['category']);
    $date_added = htmlspecialchars($_POST['date_added']); // Assuming date_added is a string, adjust as needed
    $Edit_by = htmlspecialchars($_SESSION['username']);
    $Lastedit_date = date('Y-m-d H:i:s');
    // print_r($Lastedit_date);
    // die();
    // Use prepared statement to prevent SQL injection
    $sql = "UPDATE products SET name=?, price=?, quantity=?, category_id=?, date_added=?, edit_by=?, lastedit_date=?  WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sdiissss", $name, $price, $quantity, $category, $date_added, $Edit_by, $Lastedit_date, $id);

    // Adjust types accordingly ('s' for string, 'd' for double, 'i' for integer)
    // print_r($stmt);
    // die();
    // Execute the statement
    if ($stmt->execute()) {
        header("Location: index.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    // Close the statement
    $stmt->close();
}

$conn->close();
