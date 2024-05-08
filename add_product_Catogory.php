<?php
include('db_connection.php');
session_start();


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize user inputs
    $category = htmlspecialchars($_POST['category']);

    // Use prepared statement to prevent SQL injection
    $sql = "INSERT INTO products_category (category_value, isActive) VALUES (?, ?)";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        echo "Error preparing statement: " . $conn->error;
        die();
    }

    // Bind parameters
    $isActive = 1; // Assuming isActive is always 1
    $stmt->bind_param("si", $category, $isActive);

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
