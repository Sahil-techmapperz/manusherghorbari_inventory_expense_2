<?php
include('db_connection.php');
session_start();

// Assuming $conn is your database connection

// Retrieve the raw POST data from the input stream
$content = file_get_contents("php://input");
// Attempt to decode the raw POST data from JSON
$decoded = json_decode($content, true);

// Check if the category ID is provided in the decoded data
if (isset($decoded['id'])) {
    // Get the ID from the decoded data and ensure it's an integer
    $id = intval($decoded['id']);

    ini_set('display_errors', 1);
    error_reporting(E_ALL);

    // Prepare the SQL statement to prevent SQL injection
    // $orderBy = "products.id DESC";
    // $sql = "SELECT quantity FROM products WHERE id = ? ORDER BY " . $orderBy;

    $orderBy = "product_balances.Product_Balances_id DESC";
    $sql = "SELECT Balance_Quantity FROM product_balances WHERE Product_Balances_id = ? ORDER BY " . $orderBy;

    // Prepare the statement
    $stmt = $conn->prepare($sql);

    // Bind parameters
    $stmt->bind_param("i", $id);

    // Execute the statement
    $stmt->execute();

    // Get the result
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Fetch all data
        $data = $result->fetch_all(MYSQLI_ASSOC);

        // Return the data as JSON
        header('Content-Type: application/json');
        echo json_encode($data);
    } else {
        // If the ID does not exist in the database, return an error message
        header("HTTP/1.1 404 Not Found");
        echo json_encode(['error' => 'ID not found']);
    }

    // Close the statement
    $stmt->close();
} else {
    // If no ID parameter is provided in the POST request, return an error message
    header("HTTP/1.1 400 Bad Request");
    echo json_encode(['error' => 'ID parameter is missing']);
}
