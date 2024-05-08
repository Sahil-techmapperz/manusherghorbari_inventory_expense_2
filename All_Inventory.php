<?php
include('db_connection.php');
include('common.php');
include('require.php');

session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}


// Example usage

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

// $sql = "SELECT * FROM products $whereClause $orderBy";
// $result = $conn->query($sql);
$orderBy = "ORDER BY products.id DESC"; // Replace 'column_name' with the actual column name you want to sort by
$sql = "SELECT * FROM products_category 
        INNER JOIN products ON products_category.id = products.category_id 
        $whereClause 
        $orderBy";
$result = $conn->query($sql);

// while ($row = $result->fetch_assoc()) {
//     echo '<pre>';
//     print_r($row['name']);
// }


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WSFPL Inventory Management</title>
    <!-- Include Bootstrap CSS -->
    <link rel="stylesheet" href="bootstrap.min.css">
    <!-- <link rel="stylesheet" href="all.min.css"> -->
    <!-- <link rel="stylesheet" href="font-awesome-all.min.css">
    <link rel="stylesheet" href="dataTables.dataTables.min.css"> -->
    <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" /> -->
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

        #messageContainer {
            color: red;
        }

        table tr th {
            text-align: center !important;
            padding-right: 0px !important;
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
            <h2>All Inventory Listing</h2>
            <h5>Welcome, <?php echo $_SESSION['username']; ?>! <button type="button" class="btn btn-danger"><a href="logout.php" style="text-decoration: none; color:aliceblue">Logout</a></button></h5>
        </div>

        <div style="display: flex; justify-content:space-between">
            <div class="nav_button">
                <button type="button" class="btn btn-success mb-2">
                    <a href="index.php" style="color: white; text-decoration: none;">Home</a>
                </button>
                <!-- <button type="button" class="btn btn-success mb-2">
                    <a href="All_expenses.php" style="color: white; text-decoration: none;">All Expenses</a>
                </button>
                <button type="button" class="btn btn-success mb-2" data-bs-toggle="modal" data-bs-target="#addProductCatogoryModal">
                    Add Catogories
                </button>
                <button type="button" class="btn btn-success mb-2" data-bs-toggle="modal" data-bs-target="#addProductModal">
                    Add Product
                </button> -->
            </div>
            <div class="nav_button">
                <?php
                if ($result->num_rows > 0) : ?>
                    <!-- <button type="button" class="btn btn-primary mb-2" data-bs-toggle="modal" data-bs-target="#exportproductsModal">
                        Export
                    </button> -->
                <?php endif; ?>
            </div>
        </div>


        <!-- Search, Filter, and Sort Form -->


        <div class="table-responsive" style="position: relative;">

            <div class="col-md-2  selectshortby" style="position: absolute; margin-left:50%;z-index: 999;">
                <select class="form-select" id="sortSelect" name="sort">
                    <option value="">Sort By</option>
                    <option value="quantity_asc" <?php echo ($sort === 'quantity_asc') ? 'selected' : ''; ?>>Quantity Low to High</option>
                    <option value="quantity_desc" <?php echo ($sort === 'quantity_desc') ? 'selected' : ''; ?>>Quantity High to Low</option>
                    <option value="price_desc" <?php echo ($sort === 'price_asc') ? 'selected' : ''; ?>>Price Low to High</option>
                    <option value="price_asc" <?php echo ($sort === 'price_desc') ? 'selected' : ''; ?>>Price High to Low</option> -->
                    <!-- Add more sorting options if needed -->
                </select>
            </div>
            <table class="table table-bordered nowrap" id="example1">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Total Amount</th>
                        <th>Category</th>
                        <th>Added Date</th>
                        <th>Added By</th>
                        <!-- <th>Edit By</th>
                        <th>Last Edit date</th> -->
                        <!-- <th>Expenses</th> -->
                        <!-- <th>Actions</th> -->
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0) : ?>
                        <?php $count = 0; ?>
                        <?php while ($row = $result->fetch_assoc()) : ?>
                            <tr>
                                <td><?= ++$count; ?></td>
                                <td><?= $row['name']; ?></td>
                                <td><?= $row['quantity']; ?></td>
                                <td>₹<?= $row['price'];
                                        ?></td>
                                <td>₹<?= $row['price'] * $row['quantity'];
                                        ?></td>
                                <td><?= $row['category_value']; ?></td>
                                <td><?= convertToIndianDate($row['date_added']);
                                    ?></td>
                                <td><?= $row['Add_by'];
                                    ?></td>
                                <!-- <td><?= ($row['edit_by'] !== '' ? $row['edit_by'] : 'Not Available');
                                            ?></td>
                                <td><?= convertToIndianDate(($row['lastedit_date'] !== '' ? $row['lastedit_date'] : 'Not Available'));
                                    ?></td> -->
                                <!-- <td>
                                    <div>
                                        <?php if ($row['quantity'] == 0) { ?>
                                            <p>Out of stock</p>
                                        <?php } else { ?>
                                            <button type='button' class='btn btn-warning btn-sm' data-bs-toggle='modal' data-bs-target='#expensesProductModal<?= $row['id']; ?>'><img src="icons/expense-svgrepo-com.svg" alt="" style="width: 15px;"></i></button>
                                        <?php } ?>
                                    </div>
                                    <div class='modal fade' id='expensesProductModal<?php echo $row['id']; ?>' tabindex='-1' aria-labelledby='editProductModalLabel<?= $row['id']; ?>' aria-hidden='true'>
                                        <div class='modal-dialog'>
                                            <div class='modal-content'>
                                                <div class='modal-header'>
                                                    <h5 class='modal-title' id='editProductModalLabel<?= $row['id']; ?>'>Edit Product</h5>
                                                    <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                                                </div>
                                                <div class='modal-body'>
                                                    <form action='Add_Expense_product.php' method='post'>
                                                        <input type='hidden' name='Product_id' value="<?php echo $row["id"] ?>">
                                                        <div class="mb-3">
                                                            <label for="productQuantity" class="form-label">Product Quantity</label>
                                                            <input type="text" class="form-control" id="productQuantity_total<?php echo $row['id']; ?>" name="quantity" value="<?php echo $row['quantity'] ?>" readonly>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label for="productQuantity" class="form-label">Expenses Product Quantity</label>
                                                            <input type="number" class="form-control" id="productQuantity_Expenses<?php echo $row['id']; ?>" name="Expenses_quantity" oninput="validate_quantity('<?php echo $row['id']; ?>')" value="" required>
                                                        </div>
                                                        <div id="messageContainer<?php echo $row['id']; ?>" style="color:red"></div>
                                                        <button type="submit" class="btn btn-primary" id="btn-save<?php echo $row['id']; ?>">Save Changes</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td> -->
                                <!-- <td class='d-flex'>
                                    <div class='me-2'>
                                        <button type='button' class='btn btn-warning btn-sm' data-bs-toggle='modal' data-bs-target='#editProductModal<?= $row['id']; ?>'><img src="icons/edit-svgrepo-com.svg" alt="" style="width: 15px;"></i></button>
                                    </div>
                                    <div>
                                        <button type='button' class='btn btn-danger btn-sm' data-bs-toggle='modal' data-bs-target='#deleteProductModal<?= $row['id']; ?>'><img src="icons/delete-2-svgrepo-com.svg" alt="" style="width: 15px;"></i></button>
                                    </div>
                                </td> -->
                            </tr>
                            <!-- Edit Product Modal -->
                            <div class='modal fade' id='editProductModal<?php echo $row['id']; ?>' tabindex='-1' aria-labelledby='editProductModalLabel<?= $row['id']; ?>' aria-hidden='true'>
                                <div class='modal-dialog'>
                                    <div class='modal-content'>
                                        <div class='modal-header'>
                                            <h5 class='modal-title' id='editProductModalLabel<?= $row['id']; ?>'>Edit Product</h5>
                                            <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                                        </div>
                                        <div class='modal-body'>
                                            <form action='edit_product.php' method='post'>
                                                <input type='hidden' name='id' value="<?php echo $row["id"] ?>">
                                                <div class='mb-3'>
                                                    <label for='productCategory' class='form-label'>Product Category</label>
                                                    <select class='form-control' id='productCategory' name='category' required>
                                                        <option value='<?= $row['category_id']; ?>'><?= $row['category_value']; ?></option>
                                                        <?php foreach ($rows as $category) : ?>
                                                            <option value='<?= $category['id']; ?>'><?= $category['category_value']; ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="productName" class="form-label">Product Name</label>
                                                    <input type="text" class="form-control" id="productName" name="name" value="<?php echo $row['name'] ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="productPrice" class="form-label">Product Price</label>
                                                    <input type="text" class="form-control" id="productPrice" name="price" value="<?php echo $row['price'] ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="productQuantity" class="form-label">Product Quantity</label>
                                                    <input type="text" class="form-control" id="productQuantity" name="quantity" value="<?php echo $row['quantity'] ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="productDateAdded" class="form-label">Date Added</label>
                                                    <input type="text" class="form-control" id="productDateAdded" name="date_added" value="<?php echo $row['date_added'] ?>" readonly>
                                                </div>
                                                <button type="submit" class="btn btn-primary">Save Changes</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Delete Product Modal -->
                            <div class='modal fade' id='deleteProductModal<?= $row['id']; ?>' tabindex='-1' aria-labelledby='deleteProductModalLabel<?= $row['id']; ?>' aria-hidden='true'>
                                <div class='modal-dialog'>
                                    <div class='modal-content'>
                                        <div class='modal-header'>
                                            <h5 class='modal-title' id='deleteProductModalLabel<?= $row['id']; ?>'>Confirm Deletion</h5>
                                            <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                                        </div>
                                        <div class='modal-body'>
                                            Are you sure you want to delete the product: <strong><?= $row['name']; ?></strong>?
                                        </div>
                                        <div class='modal-footer'>
                                            <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Cancel</button>
                                            <a href='delete_product.php?id=<?= $row['id']; ?>' class='btn btn-danger' style="text-decoration: none;">Delete</a>
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

    <!-- Add Product Modal -->
    <div class=" modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addProductModalLabel">Add Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Your add product form goes here -->
                    <!-- For simplicity, I'm just providing a basic example -->
                    <form action="add_product.php" method="post">
                        <div class="mb-3">
                            <label for="productCategory" class="form-label">Product Category</label>
                            <select class="form-control" id="productCategory" name="category" required>
                                <option value="">Select Catogory</option>
                                <?php
                                foreach ($rows as $row) { ?>
                                    <option value="<?php echo $row['id'] ?>"><?php echo $row['category_value'] ?></option>
                                <?php  } ?>
                            </select>
                            <!-- <label for="productCategory" class="form-label">Product Category</label>
                            <input type="text" class="form-control" id="productCategory" name="category" required> -->
                        </div>
                        <div class="mb-3">
                            <label for="productName" class="form-label">Product Name</label>
                            <input type="text" class="form-control" id="productName" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="productPrice" class="form-label">Product Price</label>
                            <input type="text" class="form-control" id="productPrice" name="price" required>
                        </div>
                        <div class="mb-3">
                            <label for="productQuantity" class="form-label">Product Quantity</label>
                            <input type="text" class="form-control" id="productQuantity" name="quantity" required>
                        </div>
                        <div class="mb-3">
                            <!-- <label for="productCategory" class="form-label">Product Category</label>
                            <select class="form-control" id="productCategory" name="category" required>
                                <?php
                                // foreach ($categoryValues as $category_value) { 
                                ?>
                                    <option value="<?php // echo $category_value 
                                                    ?>"><?php // echo $category_value 
                                                        ?></option>
                                <?php // } 
                                ?>
                            </select> -->
                            <!-- <label for="productCategory" class="form-label">Product Category</label>
                            <input type="text" class="form-control" id="productCategory" name="category" required> -->
                        </div>
                        <div class="mb-3">
                            <label for="productDateAdded" class="form-label">Date Added</label>
                            <input type="text" class="form-control" id="productDateAdded" name="date_added" value="<?php echo date('Y-m-d H:i:s'); ?>" readonly>
                        </div>
                        <button type="submit" class="btn btn-primary">Add</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class=" modal fade" id="exportproductsModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addProductModalLabel">Export Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Your add product form goes here -->
                    <!-- For simplicity, I'm just providing a basic example -->
                    <form action="Product_export.php" method="post">
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
    </div>


    <div class="modal fade" id="addProductCatogoryModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addProductModalLabel">Add Product Catogory</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Your add product form goes here -->
                    <!-- For simplicity, I'm just providing a basic example -->
                    <form action="add_product_Catogory.php" method="post">
                        <div class="mb-3">
                            <label for="productCategory" class="form-label">Product Category</label>
                            <input type="text" class="form-control" id="productCategory" name="category" required>
                        </div>
                        <div class="mb-3">
                            <label for="productDateAdded" class="form-label">Date Added</label>
                            <input type="text" class="form-control" id="productDateAdded" name="date_added" value="<?php echo date('Y-m-d H:i:s'); ?>" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Add</button>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <!-- Bootstrap JS (same as before) -->
    <script src="bootstrap.bundle.min.js"></script>
    <!-- <script src="jquery.min.js"></script>
    <script src="dataTables.min.js"></script> -->

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
        function validate_quantity(id) {
            // Make sure the document is ready
            $(document).ready(function() {
                var productQuantity = parseInt($("#productQuantity_total" + id).val());
                var Expenses_quantity = parseInt($("#productQuantity_Expenses" + id).val());
                console.log(productQuantity);
                console.log(Expenses_quantity);
                $("#messageContainer" + id).empty();
                if (productQuantity < Expenses_quantity) {
                    // Append a message to a container
                    $("#messageContainer" + id).append("<p>Expenses quantity is greater than product quantity!</p>");
                    $("#btn-save" + id).hide();
                } else {
                    $("#btn-save" + id).show();
                }
            });

            // Prevent or allow form submission on Enter press
            $(document).on('keypress', function(e) {
                if (e.which == 13) { // 13 is the Enter key
                    var isSubmitButtonVisible = $("#btn-save" + id).is(':visible');
                    if (!isSubmitButtonVisible) {
                        e.preventDefault(); // Prevent form submission if the submit button is hidden
                    } else {
                        // Optionally, trigger form submission here if needed
                        // $("#yourFormId").submit();
                    }
                }
            });
        }
    </script>
</body>

</html>