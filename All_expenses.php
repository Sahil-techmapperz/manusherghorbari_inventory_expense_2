<?php
include('db_connection.php');
include('common.php');
include('require.php');

session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Fetch distinct categories from the database
$categorySql = "SELECT DISTINCT category FROM products";
$categoryResult = $conn->query($categorySql);
// Assuming $conn is your database connection

// Define the SQL query
$productcategorySql = "SELECT DISTINCT category_value FROM products_category";

// Prepare and execute the statement
$productcategoryStmt = $conn->prepare($productcategorySql);
$productcategoryStmt->execute();

// Bind result variables
$productcategoryStmt->bind_result($category_value);

// Fetch values and store them in an array
$categoryValues = [];
while ($productcategoryStmt->fetch()) {
    $categoryValues[] = htmlspecialchars($category_value);
}

// Close statement
$productcategoryStmt->close();

// Sort the array in descending order
rsort($categoryValues);

// Iterate over the fetched category values using foreach loop

$productcategorySql1 = "SELECT * FROM products_category";

// Prepare and execute the statement
$productcategoryStmt1 = $conn->prepare($productcategorySql1);
$productcategoryStmt1->execute();

// Get the result set
$result1 = $productcategoryStmt1->get_result();

// Fetch all rows as an associative array
$rows = $result1->fetch_all(MYSQLI_ASSOC);

// Close the statement


// Sort the array in descending order
rsort($rows);

// Handle search, filter, and sorting logic
$search = isset($_GET['search']) ? $_GET['search'] : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : '';
// print_r($category);
// print_r($search);

// die();
$whereClause = "WHERE 1";

// Add search condition
if (!empty($search)) {
    $whereClause .= " AND (name LIKE '%$search%' OR price LIKE '%$search%' OR quantity LIKE '%$search%' OR category LIKE '%$search%')";
}

// Add filter condition
if (!empty($category)) {
    $whereClause .= " AND category = '$category'";
}

// Add sorting condition
$orderBy = '';
if (!empty($sort)) {
    switch ($sort) {
        case 'quantity_asc':
            $orderBy = 'ORDER BY quantity ASC';
            break;
        case 'quantity_desc':
            $orderBy = 'ORDER BY quantity DESC';
            break;
        case 'price_asc':
            $orderBy = 'ORDER BY price ASC';
            break;
        case 'price_desc':
            $orderBy = 'ORDER BY price DESC';
            break;
            // Add more sorting options if needed
    }
}


$orderBy = "ORDER BY product_expenses.Product_Expenses_id DESC"; // Replace 'column_name' with the actual column name you want to sort by
$sql = "SELECT * FROM product_expenses
INNER JOIN product_balances ON product_balances.Product_Balances_id = product_expenses.Product_id
INNER JOIN products_category ON products_category.id = product_balances.Product_catogory_id
$orderBy";
$result = $conn->query($sql);


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management</title>
    <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script> -->
    <!-- Include Bootstrap CSS -->
    <link rel="stylesheet" href="bootstrap.min.css">
    <!-- <link rel="stylesheet" href="font-awesome-all.min.css"> -->
    <!-- <link rel="stylesheet" href="dataTables.dataTables.min.css"> -->
    <style>
        body {
            background-image: url('bg.png');
            font-size: 14px;
        }

        .nav_button {
            display: flex;
            gap: 20px;
            justify-content: end;
            margin-right: 0px;
        }

        .nowrap td {
            white-space: nowrap;
        }

        .message {
            color: red;
        }

        table tr th {
            text-align: center !important;
            /* padding-right: 15px !important; */
        }

        table tr td {
            text-align: center !important;
        }
        .btn-success {
            width: fit-content;
            height: 30px;
            display: flex;
            justify-content: center;
            align-items: center;
        }
    </style>

</head>

<body>
    <div class="container mt-1">
        <div style="display: flex; justify-content:space-between">
            <h2>All Consume Product Listing</h2>
            <h5>Welcome, <?php echo $_SESSION['username']; ?>! <button type="button" class="btn btn-danger"><a href="logout.php" style="text-decoration: none; color:aliceblue">Logout</a></button></h5>
        </div>
        <!-- Search, Filter, and Sort Form -->
        <div style="display: flex; justify-content:space-between;">
            <div>
                <button type="button" class="btn btn-success mb-2">
                    <a href="index.php" style="color: white; text-decoration: none;">Home</a>
                </button>
            </div>
            <!-- <div class="nav_button">
                <?php
                if ($result->num_rows > 0) : ?>
                    <button type="button" class="btn btn-primary mb-2" data-bs-toggle="modal" data-bs-target="#eportexpensesProductModal">
                        Export
                    </button>
                <?php endif; ?>
            </div> -->
        </div>

        <div class="table-responsive" style="position: relative;">

            <div class="col-md-2  selectshortby" style="position: absolute; margin-left:50%;z-index: 999;">
                <select class="form-select" id="sortSelect" name="sort">
                    <option value="">Sort By</option>
                    <option value="quantity_asc" <?php echo ($sort === 'quantity_asc') ? 'selected' : ''; ?>>Quantity Low to High</option>
                    <option value="quantity_desc" <?php echo ($sort === 'quantity_desc') ? 'selected' : ''; ?>>Quantity High to Low</option>
                    <option value="price_desc" <?php echo ($sort === 'price_asc') ? 'selected' : ''; ?>>Price Low to High</option>
                    <option value="price_asc" <?php echo ($sort === 'price_desc') ? 'selected' : ''; ?>>Price High to Low</option>
                    <!-- Add more sorting options if needed -->
                </select>
            </div>
            <table class="table table-bordered nowrap" id="example1">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Product Name</th>
                        <!-- <th>Price</th> -->
                        <th>Quantity</th>
                        <!-- <th>Total Amount</th> -->
                        <th>Category</th>
                        <th>Expenses By</th>
                        <th>Expenses Date</th>
                        <!-- <th>Action</th> -->
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result->num_rows > 0) : ?>
                        <?php $count = 0; ?>
                        <?php while ($row = $result->fetch_assoc()) : ?>
                            <tr>
                                <td><?= ++$count; ?></td>
                                <td><?= $row['Product_name']; ?></td>
                                <!-- <td><?= $row['price']; ?></td> -->
                                <td><?= $row['Expenses_quantity']; ?></td>
                                <!-- <td><?= $row['price'] * $row['Expenses_quantity']; ?></td> -->
                                <td><?= $row['category_value']; ?></td>
                                <td><?= $row['Expenses_by']; ?></td>
                                <td><?= convertToIndianDate($row['Created_at']); ?></td>
                                <!-- <td class='d-flex'>
                                    <div class='me-2'>
                                        <button type='button' class='btn btn-warning btn-sm' data-bs-toggle='modal' data-bs-target='#editProductModal<?= $row['Product_Expenses_id']; ?>'><i class='fa-solid fa-pen-to-square'></i></button>
                                    </div>
                                    <div>
                                        <button type='button' class='btn btn-danger btn-sm' data-bs-toggle='modal' data-bs-target='#deleteProductModal<?= $row['Product_Expenses_id']; ?>'><i class='fa-solid fa-trash'></i></button>
                                    </div>
                                </td> -->
                            </tr>
                            <!-- Edit Product Modal -->
                            <div class='modal fade' id='editProductModal<?php echo $row['Product_Expenses_id']; ?>' tabindex='-1' aria-labelledby='editProductModalLabel<?= $row['Product_Expenses_id']; ?>' aria-hidden='true'>
                                <div class='modal-dialog'>
                                    <div class='modal-content'>
                                        <div class='modal-header'>
                                            <h5 class='modal-title' id='editProductModalLabel<?= $row['Product_Expenses_id']; ?>'><?php echo $row['Product_Expenses_id']; ?>Edit Product</h5>
                                            <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                                        </div>
                                        <div class='modal-body'>
                                            <form action='edit_product_expenses.php' method='post'>
                                                <input type='hidden' name='Product_Expenses_id' value="<?php echo $row["Product_Expenses_id"] ?>">
                                                <div class='mb-3'>
                                                    <label for='productCategory' class='form-label'>Product Category</label>
                                                    <select class='form-control' id='productCategory' name='category' required>
                                                        <option value='<?= $row['category_id']; ?>'><?= $row['category_value']; ?></option>
                                                        <?php foreach ($rows as $category) : ?>
                                                            <option value='<?= $category['Product_Expenses_id']; ?>'><?= $category['category_value']; ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="productName" class="form-label">Product Name</label>
                                                    <input type="text" class="form-control" id="productName" name="name" value="<?php echo $row['name'] ?>" readonly>
                                                    <input type="hidden" class="form-control" id="productName" name="Product_id" value="<?php echo $row['Product_id'] ?>" required>
                                                </div>

                                                <div class="mb-3">
                                                    <label for="productQuantity" class="form-label">Product Quantity</label>
                                                    <input type="text" class="form-control" id="productQuantity" name="quantity" value="<?php echo $row['quantity'] ?>" readonly>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="productQuantity" class="form-label">Expenses Quantity</label>
                                                    <input type="text" class="form-control" id="Expenses_quantity" name="Expenses_quantity" value="<?php echo $row['Expenses_quantity'] ?>" required>
                                                </div>
                                                <div id="messageContainer" class="message"></div>

                                                <div class="mb-3">
                                                    <label for="productDateAdded" class="form-label">Expenses By</label>
                                                    <input type="text" class="form-control" id="Expenses_by" name="	Expenses_by" value="<?php echo $row['username'] ?>" readonly>
                                                    <input type="hidden" class="form-control" id="Expenses_by" name="Expenses_by" value="<?php echo $row['Expenses_by'] ?>" required>
                                                </div>
                                                <button type="submit" class="btn btn-primary btn-save">Save Changes</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Delete Product Modal -->
                            <div class='modal fade' id='deleteProductModal<?= $row['Product_Expenses_id']; ?>' tabindex='-1' aria-labelledby='deleteProductModalLabel<?= $row['Product_Expenses_id']; ?>' aria-hidden='true'>
                                <div class='modal-dialog'>
                                    <div class='modal-content'>
                                        <div class='modal-header'>
                                            <h5 class='modal-title' id='deleteProductModalLabel<?= $row['Product_Expenses_id']; ?>'>Confirm Deletion</h5>
                                            <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                                        </div>
                                        <div class='modal-body'>
                                            Are you sure you want to delete the product: <strong><?= $row['name']; ?></strong>?
                                        </div>
                                        <div class='modal-footer'>
                                            <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Cancel</button>
                                            <a href='delete_Expenses_product.php?Product_Expenses_id=<?= $row['Product_Expenses_id']; ?>' class='btn btn-danger' style="text-decoration: none;">Delete</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan='8'>No products found</td>
                        </tr>
                    <?php endif; ?>
                    <?php $conn->close(); ?>
                </tbody>
            </table>
        </div>
        <br>
    </div>
    </div>
    <div class=" modal fade" id="eportexpensesProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addProductModalLabel">Export Consume Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Your add product form goes here -->
                    <!-- For simplicity, I'm just providing a basic example -->
                    <form action="Product_Expenses_export.php" method="post">
                        <div class='mb-3'>
                            <label>From</label>
                            <input type="datetime-local" class="form-control" name="from_date" id="from_date" required />
                        </div>
                        <div class='mb-3'>
                            <label>To</label>
                            <input type="datetime-local" class="form-control" name="to_date" id="to_date" required />
                        </div>
                        <button type="submit" class="btn btn-primary mb-2" id="Export_button">Export</button>
                    </form>
                </div>
            </div>
        </div>
        <script src="bootstrap.bundle.min.js"></script>
        <!-- <script src="jquery.min.js"></script>
        <script src="dataTables.min.js"></script> -->
        <!-- <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script> -->
        <!-- Bootstrap JS (same as before) -->
        <!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script> -->
        <script>
            $(document).ready(function() {
                $.fn.dataTable.ext.errMode = 'none';
                var table = $('#example1').DataTable({
                    "lengthMenu": [
                        [10, 25, 50, -1],
                        [10, 25, 50, "All"]
                    ],
                    "pageLength": 10,
                    "order": [
                        [0, 'asc']
                    ],
                    dom: 'Bfrtip', // Add buttons to the DataTable
                    buttons: [
                        'csv', // Add CSV export button
                        'pdf', // Add PDF export button
                        'colvis' // Add column visibility control
                    ]
                })
                $('.dt-button').css({
                    'background-color': 'rgb(35 33 41)', // Solid color background
                    'color': '#ffffff', // Text color
                    'border-radius': '10%', // Border radius
                    'border': 'none'
                });

                // Handle sort select change event
                $('#sortSelect').on('change', function() {
                    var value = $(this).val();
                    if (value) {
                        var columnIdx;
                        var direction;
                        switch (value) {
                            case 'quantity_asc':
                                columnIdx = 1; // Assuming quantity column is at index 1
                                direction = 'asc';
                                break;
                            case 'quantity_desc':
                                columnIdx = 1;
                                direction = 'desc';
                                break;
                            case 'price_asc':
                                columnIdx = 2; // Assuming price column is at index 2
                                direction = 'asc';
                                break;
                            case 'price_desc':
                                columnIdx = 2;
                                direction = 'desc';
                                break;
                                // Add more cases if needed for additional sorting options
                        }
                        table.order([columnIdx, direction]).draw();
                    }
                });
            });
        </script>

        <script>
            $(document).ready(function() {
                $("#productQuantity, #Expenses_quantity").on("input", function() {
                    var productQuantity = parseInt($("#productQuantity").val());
                    var Expenses_quantity = parseInt($("#Expenses_quantity").val());
                    $("#messageContainer").empty();
                    if (productQuantity < Expenses_quantity) {
                        // Append a message to a container
                        $("#messageContainer").append("<p>Expenses quantity is greater than product quantity!</p>");
                        $(".btn-save").hide();

                        // You can also perform other actions here
                    } else {
                        $(".btn-save").show();
                    }
                });
            });
        </script>

</body>

</html>