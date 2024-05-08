<?php
include('db_connection.php');
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

// $sql = "SELECT * FROM products $whereClause $orderBy";
// $result = $conn->query($sql);
$orderBy = "ORDER BY products.id DESC"; // Replace 'column_name' with the actual column name you want to sort by
$sql = "SELECT products.name,products_category.category_value  AS category_value, product_balances.Balance_Quantity AS quantity
        FROM products_category 
        INNER JOIN products ON products_category.id = products.category_id
        INNER JOIN product_balances ON product_balances.Product_name = products.name 
        $whereClause
        GROUP BY products.name
        $orderBy";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management</title>
    <!-- Include Bootstrap CSS -->
    <link rel="stylesheet" href="bootstrap.min.css">
    <!-- <style>
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

        /* table tr th {
            text-align: center !important;
            padding-right: 0px !important;
        }

        table tr td {
            text-align: center !important;
        } */
    </style> -->
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

        .btn-success {
            width: fit-content;
            height: 30px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        table tr th {
            text-align: center !important;
            padding-right: 0px !important;
        }

        table tr td {
            text-align: center !important;
        }
    </style>
</head>

<body>
    <div class="container mt-1">
        <div style="display: flex; justify-content:space-between">
            <h2>Product Balance Listing</h2>
            <h5>Welcome, <?php echo $_SESSION['username']; ?>! <button type="button" class="btn btn-danger"><a href="logout.php" style="text-decoration: none; color:aliceblue">Logout</a></button></h5>
        </div>

        <div style="display: flex; justify-content:space-between">
            <div class="nav_button">
                <button type="button" class="btn btn-success mb-3">
                    <a href="All_Inventory.php" style="color: white; text-decoration: none;">All Inventory</a>
                </button>
                <button type="button" class="btn btn-success mb-3">
                    <a href="All_expenses.php" style="color: white; text-decoration: none;">All Consume Product</a>
                </button>
                <!-- Button to trigger modal for adding a product Catogory -->
                <button type="button" class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#addProductCatogoryModal">
                    Add Categories
                </button>
                <!-- Button to trigger modal for adding a product -->
                <button type="button" class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#addProductModal">
                    Add Product
                </button>
                <button type="button" class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#addProductExpensesModal">
                    Consume Product
                </button>
                <button type="button" class="btn btn-success mb-3">
                    <a href="expenses.php" style="color: white; text-decoration: none;">Expenses</a>
                </button>
            </div>
        </div>
        <div class="nav_button">
            <?php
            if ($result->num_rows > 0) : ?>
                <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#exportinventoryModal">
                    Export
                </button>
            <?php endif; ?>
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
                        <th>Category</th>
                        <th>Stock Quantity</th>
                        <!-- <th>Expenses</th> -->
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0) : ?>
                        <?php $count = 0; ?>
                        <?php while ($row = $result->fetch_assoc()) : ?>
                            <tr>
                                <td><?= ++$count; ?></td>
                                <td><?php echo $row['name']; ?></td>
                                <td><?php echo $row['category_value']; ?></td>
                                <td><?php echo $row['quantity']; ?></td>
                                <!-- <td>
                                    <div>
                                        <?php // if ($row['quantity'] == 0) { 
                                        ?>
                                            <p>Out of stock</p>
                                        <?php // } else { 
                                        ?>
                                            <button type='button' class='btn btn-warning btn-sm' data-bs-toggle='modal' data-bs-target='#expensesProductModal<?= $row['id']; ?>'><img src="icons/expense-svgrepo-com.svg" alt="" style="width: 15px;"></i></button>
                                        <?php // } 
                                        ?>
                                    </div>
                                </td> -->
                                <td class='d-flex'>
                                    <div class='me-2'>
                                        <button type='button' class='btn btn-warning btn-sm'><a href="Add_history.php?name=<?= $row['name']; ?>" style="text-decoration: none;color:aliceblue;">Addition History</a></button>
                                    </div>
                                    <div>
                                        <button type='button' class='btn btn-danger btn-sm'><a href="Expenses_history.php?name=<?= $row['name']; ?>" style="text-decoration: none; color:aliceblue;">Consume History</a></i></button>
                                    </div>
                                </td>
                            </tr>
                            <!-- Edit Product Modal -->
                            <!-- <div class='modal fade' id='editProductModal<?php echo $row['id']; ?>' tabindex='-1' aria-labelledby='editProductModalLabel<?= $row['id']; ?>' aria-hidden='true'>
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
                            </div> -->
                            <!-- Delete Product Modal -->
                            <!-- <div class='modal fade' id='deleteProductModal<?= $row['id']; ?>' tabindex='-1' aria-labelledby='deleteProductModalLabel<?= $row['id']; ?>' aria-hidden='true'>
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
                            </div> -->
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
                            <select class="form-select" id="productCategory" name="category" required>
                                <option value="">Select Category</option>
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

    <div class="modal fade" id="addProductCatogoryModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addProductModalLabel">Add Product Category</h5>
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

    <div class="modal fade" id="exportinventoryModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addProductModalLabel">Export Inventory Products</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="Inventory_export.php" method="post">
                        <div class="mb-3">
                            <label for="from_date" class="form-label">From</label>
                            <input type="datetime-local" class="form-control" name="from_date" id="from_date" required />
                        </div>
                        <div class="mb-3">
                            <label for="to_date" class="form-label">To</label>
                            <input type="datetime-local" class="form-control" name="to_date" id="to_date" required />
                        </div>
                        <button type="submit" class="btn btn-primary mb-2" id="Export_button">Export</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class=" modal fade" id="addProductExpensesModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addProductModalLabel">Add Expenses</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Your add product form goes here -->
                    <!-- For simplicity, I'm just providing a basic example -->
                    <form action="add_Expense_product.php" method="post">
                        <div class="mb-3">
                            <label for="productQuantity" class="form-label"><b>Consume By</b></label>
                            <input type="text" class="form-control" id="Expenses_by" name="Expenses_by" required>
                        </div>
                        <div class="mb-3">
                            <label for="productCategory" class="form-label"><b>Product Category</b></label>
                            <!-- <select class="form-select" id="productCategory" name="category" onchange="SelectCatogory(this.value) " required> -->
                            <select class="form-select" id="productCategory11" name="category" required>
                                <option value="">Select Category</option>
                                <?php
                                foreach ($rows as $row) { ?>
                                    <option value="<?php echo $row['id'] ?>"><?php echo $row['category_value'] ?></option>
                                <?php  } ?>
                            </select>
                            <input type="hidden" class="form-control" id="productCategory_name" name="productCategory_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="productName" class="form-label"><b>Product Name</b></label>
                            <!-- <select name="Product_id" id="productDisplay" class="form-select" onchange="SelectProduct(this.value)"></select> -->
                            <select name="Product_id" id="productDisplay" class="form-select"></select>
                            <input type="hidden" class="form-control" id="productName_Display" name="productName_Display" required>
                        </div>
                        <div class="mb-3">
                            <label for="productQuantity" class="form-label"><b>Product Quantity</b></label>
                            <input type="text" class="form-control" id="SelectproductQuantity" name="quantity" readonly>
                        </div>
                        <div class="mb-3" id="Expense_product_qunatity_div">
                        </div>
                        <div id="messageContainer"></div>
                        <button type="submit" class="btn btn-primary btn-save" id="btn-save">Add</button>
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
                // dom: 'Bfrtip', // Add buttons to the DataTable
                // buttons: [
                //     'csv', // Add CSV export button
                //     'pdf', // Add PDF export button
                //     'colvis' // Add column visibility control
                // ]
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
        document.getElementById("productCategory11").addEventListener("change", function() {
            var selectedvalue = this.value;
            var selectElement = document.getElementById("productCategory11");
            var productCategory_name = document.getElementById("productCategory_name");
            var options = selectElement.options;
            productCategory_name.value = "";

            for (var i = 0; i < options.length; i++) {
                if (options[i].value === selectedvalue) {
                    var innerHTML = options[i].innerHTML;
                    // console.log(innerHTML);
                    productCategory_name.value = innerHTML;
                    console.log(productCategory_name.value);
                    SelectCatogory(selectedvalue)
                    // break; // Exit loop after finding the desired option
                }
            }
        });

        function SelectCatogory(value) {
            const Expense_product_qunatity_div = document.getElementById('Expense_product_qunatity_div');
            const productDisplay = document.getElementById('SelectproductQuantity');
            const productDisplay1 = document.getElementById('productDisplay');
            productDisplay.value = "";
            productDisplay1.innerHTML = '';
            productDisplay1.value = '';


            Expense_product_qunatity_div.innerHTML = "";
            if (value == "") {
                const productDisplay = document.getElementById('productDisplay');

                // Clear previous content
                productDisplay.innerHTML = '';
                return;
            }
            const url = "get_product_for_expences.php";

            // Assuming you want to send the selected category value in the request body
            const data = {
                id: value
            };

            fetch(url, {
                    method: 'POST', // Specify the method as POST
                    headers: {
                        'Content-Type': 'application/json', // Specify the content type of the request body
                    },
                    body: JSON.stringify(data), // Convert the JavaScript object to a JSON string
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    // Handle the response data
                    displayProducts(data);
                    console.log(data);
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }

        function displayProducts(products) {
            const productDisplay = document.getElementById('productDisplay');
            productDisplay.innerHTML = '';

            // Check if there are products
            if (products.length === 0) {
                productDisplay.innerHTML = 'No products found.';
                return;
            }
            // Create and append elements for each product
            const defaultOption = document.createElement('option');
            defaultOption.innerHTML = 'Select Product';
            defaultOption.value = "";
            productDisplay.appendChild(defaultOption);
            const productQuantityDisplay = document.getElementById('SelectproductQuantity');
            productQuantityDisplay.value = "";
            products.forEach(product => {
                const div = document.createElement('option');
                div.innerHTML = product.Product_name;
                div.value = product.Product_Balances_id;
                productDisplay.appendChild(div);
            });


        }




        document.getElementById("productDisplay").addEventListener("change", function() {
            var selectedvalue = this.value;
            var selectElement = document.getElementById("productDisplay");
            var productCategory_name = document.getElementById("productName_Display");
            var SelectproductQuantity = document.getElementById("SelectproductQuantity");
            SelectproductQuantity.value = "";
            productCategory_name.innerHTML = "";
            productCategory_name.value = "";

            var options = selectElement.options;
            productCategory_name.value = "";

            for (var i = 0; i < options.length; i++) {
                if (options[i].value === selectedvalue) {
                    var innerHTML = options[i].innerHTML;
                    // console.log(innerHTML);
                    productCategory_name.value = innerHTML;
                    console.log(productCategory_name.value);
                    // SelectCatogory(selectedvalue)
                    SelectProduct(selectedvalue)
                    // break; // Exit loop after finding the desired option
                }
            }
        });

        function SelectProduct(value) {
            if (value == "") {
                const productDisplay = document.getElementById('productDisplay');
                // Clear previous content
                productDisplay.innerHTML = '';
                return;
            }
            const url = "get_product_Quantity_for_expences.php";

            // Assuming you want to send the selected category value in the request body
            const data = {
                id: value
            };

            fetch(url, {
                    method: 'POST', // Specify the method as POST
                    headers: {
                        'Content-Type': 'application/json', // Specify the content type of the request body
                    },
                    body: JSON.stringify(data), // Convert the JavaScript object to a JSON string
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    // Handle the response data
                    displayProductsQuantity(data);
                    // console.log(data);
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }

        function displayProductsQuantity(products) {
            const productDisplay = document.getElementById('SelectproductQuantity');
            const Expense_product_qunatity_div = document.getElementById('Expense_product_qunatity_div');
            let product_Quantity = products[0]['Balance_Quantity'];
            Expense_product_qunatity_div.innerHTML = "";
            if (products.length === 0) {
                productDisplay.value = 'No products found.';
                return;
            }
            productDisplay.value = product_Quantity;

            if (product_Quantity === 0) {
                const p = document.createElement('p');
                p.innerText = "Out of Stock";
                p.style.color = "red";
                Expense_product_qunatity_div.appendChild(p);
            } else {
                const label = document.createElement('label');
                const input = document.createElement('input');
                label.innerHTML = '<b>Consume Quantity</b>';
                label.className = 'form-label';
                input.id = 'Expenses_Quantity';
                input.className = 'form-control';
                input.name = 'Expenses_quantity';
                input.addEventListener('input', function() {
                    validate_quantity();
                })
                Expense_product_qunatity_div.appendChild(label);
                Expense_product_qunatity_div.appendChild(input);

            }

        }
    </script>


    <script>
        function validate_quantity() {
            $(document).ready(function() {
                var productQuantity = parseInt($("#SelectproductQuantity").val());
                var Expenses_quantity = parseInt($("#Expenses_Quantity").val());
                $("#messageContainer").empty();
                if (productQuantity <= 0 || Expenses_quantity <= 0) {
                    // Append a message to a container
                    $("#messageContainer").append("<p>Product or Expenses quantity cannot be zero or negative!</p>");
                    $("#btn-save").hide(); // Hide save button as quantities are invalid
                } else if (productQuantity < Expenses_quantity) {
                    // Append a message to a container if expenses quantity is greater
                    $("#messageContainer").append("<p>Expenses quantity is greater than product quantity!</p>");
                    $("#btn-save").hide(); // Hide the save button in this scenario as well
                } else {
                    // Show the save button if all conditions are met
                    $("#btn-save").show();
                }
            });

            // Prevent or allow form submission on Enter press
            $(document).on('keypress', function(e) {
                if (e.which == 13) { // 13 is the Enter key
                    var isSubmitButtonVisible = $("#btn-save").is(':visible');
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