<?php
include('db_connection.php');
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize user inputs
    $name = htmlspecialchars($_POST['name']);
    $price = floatval($_POST['price']); // Assuming price is a float, adjust as needed
    $quantity = intval($_POST['quantity']); // Assuming quantity is an integer, adjust as needed
    $category = htmlspecialchars($_POST['category']);
    $date_added = htmlspecialchars($_POST['date_added']); // date
    $Add_by = $_SESSION['username'];
    $amount = intval($quantity) * intval($price);

    // Use prepared statement to prevent SQL injection

    $sql = "INSERT INTO products (name, price, quantity, category_id, date_added, Add_by) VALUES (?, ?, ?, ?, ?,?)";

    $stmt = $conn->prepare($sql);

    // Bind parameters
    $stmt->bind_param("sdiiss", $name, $price, $quantity, $category, $date_added, $Add_by);
    $stmt->execute();


    $sql = "SELECT category_value FROM products_category WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $category);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $productCategory_name = $row['category_value'];

    $sql = "INSERT INTO money_expenses (Catogory,Product_name, Amount, expense_Date) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    // Bind parameters
    $stmt->bind_param("ssis", $productCategory_name, $name, $amount, $date_added);


    if ($stmt->execute()) {
        // Adjust types accordingly ('s' for string, 'd' for double, 'i' for integer)
        $sql = "SELECT * FROM product_balances WHERE Product_name = ? AND Product_catogory_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $name, $category);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();
            $category = $row['Product_catogory_id'];
            $name = $row['Product_name'];
            $new_quantity = $row['Balance_Quantity'] + $quantity;
            $sql = "UPDATE product_balances SET Balance_Quantity = ? WHERE Product_name = ? AND Product_catogory_id = ?";
            $stmt = $conn->prepare($sql);
            // Bind parameters
            $stmt->bind_param("isi", $new_quantity, $name, $category);
            $stmt->execute();
            header("Location: index.php");
            exit();
        } else {
            $sql = "INSERT INTO product_balances (Product_name, Product_catogory_id, Balance_Quantity) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);

            // Bind parameters
            $stmt->bind_param("sii", $name, $category, $quantity);
            // Execute the statement
            $stmt->execute();
            header("Location: index.php");
            exit();
        }
    } else {
        echo "Error: " . $stmt->error;
    }

    // Close the statement
    $stmt->close();
}

$conn->close();
