<?php
include('db_connection.php');
include('common.php');
// Check if the request method is POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the POST data
    $postData = json_decode(file_get_contents("php://input"), true);

    // Check if required data is provided
    if (!isset($postData['selectedOptions']) || !isset($postData['startdateselected']) || !isset($postData['enddateselected'])) {
        // Return a 400 Bad Request error if required data is missing
        http_response_code(400); // Bad Request
        echo json_encode(array('error' => 'Missing required data'));
        exit;
    }

    // Extract data from the POST request
    $selectedOptions = $postData['selectedOptions'];
    $startDateSelected = $postData['startdateselected'];
    $endDateSelected = $postData['enddateselected'];



    // Perform any necessary operations with the data
    // For example, you can query a database and retrieve expenses based on the selected options and dates
    $resultArray = array();
    if (in_array('0', $selectedOptions)) {

        // Sanitize and prepare date range
        $startDateSelected = $conn->real_escape_string($startDateSelected);
        $endDateSelected = $conn->real_escape_string($endDateSelected);

        $orderBy = "ORDER BY money_expenses.expenses_id DESC"; // Replace 'expenses_id' with the actual column name you want to sort by
        $sql = "SELECT *
                    FROM money_expenses 
                    Where expense_Date BETWEEN '$startDateSelected' AND '$endDateSelected' 
                    $orderBy";
        $products_category = $conn->query($sql);
        // Dummy response for demonstration

        while ($row = $products_category->fetch_assoc()) {
            $resultArray[] = $row;
        }
        $responseData = array(
            'expenses' => $resultArray
        );
    } else {
        foreach ($selectedOptions as $Category) {
            // Sanitize and prepare selected category
            $selectedCategory = $conn->real_escape_string($Category); // Assuming $Category contains the selected category

            // Sanitize and prepare date range
            $startDateSelected = $conn->real_escape_string($startDateSelected);
            $endDateSelected = $conn->real_escape_string($endDateSelected);

            $orderBy = "ORDER BY money_expenses.expenses_id DESC"; // Replace 'expenses_id' with the actual column name you want to sort by
            $sql = "SELECT *
                    FROM money_expenses 
                    WHERE Catogory='$selectedCategory' 
                    AND expense_Date BETWEEN '$startDateSelected' AND '$endDateSelected' 
                    $orderBy";

            $products_category = $conn->query($sql);
            // Dummy response for demonstration

            while ($row = $products_category->fetch_assoc()) {
                $resultArray[] = $row;
            }
            $responseData = array(
                'expenses' => $resultArray
            );
        }
    }

    // Send the response as JSON
    header('Content-Type: application/json');
    echo json_encode($responseData);
} else {
    // If the request method is not POST, return an error
    http_response_code(405); // Method Not Allowed
    echo json_encode(array('error' => 'Method Not Allowed'));
}
