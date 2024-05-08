<?php
include('db_connection.php');
include('common.php');
include('require.php');



session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
$groupBy = "GROUP BY Catogory"; // Replace 'Catogory' with the actual column name you want to group by
$orderBy = "ORDER BY money_expenses.expenses_id DESC"; // Replace 'column_name' with the actual column name you want to sort by
$sql = "SELECT Catogory
        FROM money_expenses 
        $groupBy
        $orderBy";
$products_category = $conn->query($sql);

$orderBy = "ORDER BY money_expenses.expenses_id DESC"; // Replace 'column_name' with the actual column name you want to sort by
$sql = "SELECT *
        FROM money_expenses 
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
    <!-- <link rel="stylesheet" href="all.min.css"> -->
    <link rel="stylesheet" href="font-awesome-all.min.css">
    <!-- <link rel="stylesheet" href="dataTables.dataTables.min.css"> -->

    <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" /> -->



    <style>
        body {
            background-image: url('bg.png');
            font-size: 14px;
        }

        .nav_button {
            display: flex;
            gap: 20px;
            justify-content: space-between;
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

        .dropdown-checkbox-container {
            width: 200px;
        }

        .select-box {
            position: relative;
            width: 200px;
            /* Adjust width as needed */
            cursor: pointer;
            /* Make room for selected options display above */
        }

        .selected-options {
            width: 100%;
            /* Match the width of select-box */
            height: auto;
            /* Adjust height based on content */
            background-color: #f2f2f2;
            /* Light background for visibility */
            border: 1px solid #ccc;
            /* Consistent with select-box styling */
            padding: 5px;
            /* Padding for aesthetics */
            box-sizing: border-box;
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
            /* margin-bottom: 5px; */
            /* Space between this div and the select-box */
        }

        .over-select {
            position: relative;
            width: 100%;
            height: 40px;
            /* Fixed height */
            line-height: 40px;
            /* Center text vertically */
            background-color: #FFF;
            border: 1px solid #ccc;
            box-sizing: border-box;
            padding-left: 10px;
            /* Add some padding for the text */
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .checkboxes {
            display: none;
            position: absolute;
            width: 200px;
            border: 1px #dadada solid;
            z-index: 2;
            /* Ensure it lays on top of other content */
            background-color: #FFF;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            /* Optional: Add some shadow for better visibility */
        }

        .checkboxes label {
            display: block;
            margin: 0;
            /* Remove default margins */
            padding: 10px;
            /* Add some padding */
        }

        .checkboxes label:hover {
            background-color: #1e90ff;
        }


        .nav_button_div {
            height: 30px;
            display: flex;
            justify-content: center;
            align-items: center
        }

        .btn-success {
            width: fit-content;
            height: 30px;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 10px;
        }

        .Date-div {
            margin-bottom: 10px;
            display: flex;
            gap: 10px;
            justify-content: center;
            align-items: center;
        }

        .nav_button-div {
            justify-content: right;
            align-items: center
        }
    </style>
</head>

<body>
    <div class="container mt-1">
        <div style="display: flex; justify-content:space-between">
            <h2>Expenses Listing</h2>
            <h5>Welcome, <?php echo $_SESSION['username']; ?>! <button type="button" class="btn btn-danger "><a href="logout.php" style="text-decoration: none; color:aliceblue">Logout</a></button></h5>
        </div>

        <div style="display: flex; justify-content:space-between">
            <div class="nav_button">
                <button type="button" class="btn btn-success nav_button_div mb-3">
                    <a href="index.php" style="color: white; text-decoration: none;">Home</a>
                </button>
                <button type="button" class="btn btn-success nav_button_div mb-3" data-bs-toggle="modal" data-bs-target="#addProductModal">
                    Add Expenses
                </button>
            </div>
        </div>
        <div class="nav_button nav_button-div">
            <?php
            if ($result->num_rows > 0) : ?>
                <!-- Search, Filter, and Sort Form -->
                <div class="dropdown-checkbox-container">
                    <div class="dropdown-checkbox">
                        <div class="select-box" onclick="showCheckboxes()">
                            <div class="over-select">Select options<span class="arrow">&#9660;</span></div>
                        </div>
                        <?php if ($products_category->num_rows > 0) : ?>
                            <?php $count = 0; ?>
                            <div id="checkboxes" class="checkboxes">
                                <?php while ($row = $products_category->fetch_assoc()) : ?>
                                    <label for="option-<?php echo $row['Catogory']; ?>">
                                        <input type="checkbox" id="option-<?php echo $row['Catogory']; ?>" value="<?php echo $row['Catogory']; ?>" />
                                        <?php echo $row['Catogory']; ?>
                                    </label>
                                <?php endwhile; ?>
                                <label for="option-0">
                                    <input type="checkbox" id="option-0" value="0" />
                                    All Category
                                </label>
                            </div>
                        <?php endif; ?>

                    </div>

                </div>
                <div class="Date-div">
                    <span><strong>From</strong></span><input type="date" class="start date form-control" id="start-date-selected">
                </div>
                <div class="Date-div">
                    <span><strong>To</strong></span><input type="date" class="end date form-control" id="end-date-selected">
                </div>
                <button class="get-selected-btn btn btn-success" onclick="getSelectedCategories()">Selected Data</button>

            <?php endif; ?>
        </div>

        <div class="table-responsive" style="position: relative;">
            <table class="table table-bordered nowrap" id="example1">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Category</th>
                        <th>Product Name</th>
                        <th>Expense Amount</th>
                        <th>Description</th>
                        <th>Expense Date</th>
                        <!-- <th>Actions</th> -->
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0) : ?>
                        <?php $count = 0; ?>
                        <?php while ($row = $result->fetch_assoc()) : ?>
                            <tr>
                                <td><?= ++$count; ?></td>
                                <td><?php echo $row['Catogory']; ?></td>
                                <td><?php echo $row['Product_name']; ?></td>
                                <td><?php echo $row['Amount']; ?></td>
                                <td><?php echo $row['description']; ?></td>
                                <td><?php echo convertToIndianDate($row['expense_Date']); ?></td>
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

    <!-- Add Expense Modal -->
    <div class=" modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addProductModalLabel">Add Expense</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="add_amountexpenses.php" method="post">
                        <div class="mb-3">
                            <label for="productCategory" class="form-label"><b>Expense Category</b></label>
                            <input type="text" class="form-control" id="productCategory" name="expenses_catogory" required>
                        </div>
                        <div class="mb-3">
                            <label for="productCategory" class="form-label"><b>Expense Name</b></label>
                            <input type="text" class="form-control" id="Product_name" name="Product_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="productQuantity" class="form-label"><b>Expense Amount</b></label>
                            <input type="text" class="form-control" id="productQuantity" name="expenses_amount" required>
                        </div>
                        <div class="mb-3">
                            <label for="productDateAdded" class="form-label"><b>Expenses Date</b></label>
                            <input type="date" class="form-control" id="expense_Date" name="expense_Date">
                        </div>
                        <div class="mb-3">
                            <label for="productQuantity" class="form-label"><b>Description</b></label>
                            <!-- <input type="text" class="form-control" id="description" name="description" required> -->
                            <textarea class="form-control" id="description" name="description" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Add</button>
                    </form>
                </div>
            </div>
        </div>
    </div>



    <!-- Bootstrap JS (same as before) -->
    <script src="bootstrap.bundle.min.js"></script>
    <!-- <script src="jquery.min.js"></script> -->
    <!-- <script src="dataTables.min.js"></script> -->

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
        });
    </script>
    <script>
        function convertDate(inputDate) {
            // Convert input date string to Date object
            var dateObject = new Date(inputDate);
            // Extract day, month, and year
            var day = dateObject.getDate();
            var month = dateObject.getMonth() + 1; // Months are zero-indexed
            var year = dateObject.getFullYear();

            // Ensure day and month are formatted with leading zeros if needed
            day = (day < 10) ? '0' + day : day;
            month = (month < 10) ? '0' + month : month;

            // Format the date as 'DD/MM/YYYY'
            var formattedDate = day + '/' + month + '/' + year;
            return formattedDate;
        }
    </script>

    <script type="text/javascript">
        var show = false;

        function showCheckboxes() {
            var checkboxes = document.getElementById("checkboxes");
            var arrow = document.querySelector('.arrow');

            if (show) {
                checkboxes.style.display = "none";
                arrow.innerHTML = '&#9660;'; // Downward arrow
                show = false;
            } else {
                checkboxes.style.display = "block";
                arrow.innerHTML = '&#9650;'; // Upward arrow
                show = true;
            }

        }

        document.addEventListener("click", function(event) {
            var checkboxes = document.getElementById("checkboxes");
            var arrow = document.querySelector('.arrow');

            if (!checkboxes.contains(event.target) && !arrow.contains(event.target)) {
                checkboxes.style.display = "none";
                arrow.innerHTML = '&#9660;'; // Downward arrow
                show = false;
            }
        });

        var checkboxes = document.querySelectorAll('.checkboxes input[type="checkbox"]');
        var selectedOptionsDiv = document.getElementById('selected-options');


        function getSelectedCategories() {
            var startdateselected = document.getElementById('start-date-selected').value;
            var enddateselected = document.getElementById('end-date-selected').value;
            var selectedOptions = [];

            checkboxes.forEach(function(checkbox) {
                if (checkbox.checked) {
                    selectedOptions.push(checkbox.value);
                }
            });
            // Here you can replace alert with any action you want to perform with the selected options
            // alert("Selected Categories: " + selectedOptions.join(', '));
            var data = {
                'selectedOptions': selectedOptions,
                'startdateselected': startdateselected,
                'enddateselected': enddateselected
            }
            getdatabycatogories(data);
        }

        function getdatabycatogories(data) {
            let url = 'get_expenses_by_selected.php';

            fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data),
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(result => {
                    // Handle the result if needed
                    // console.log('Backend response:', result);
                    populateTable(result.expenses);

                })
                .catch(error => {
                    console.error('There was a problem with the fetch operation:', error);
                });
        }

        function populateTable(data) {
            // Retrieve the existing DataTable instance
            var table = $('#example1').DataTable();
            let id = 1;
            // Check if the DataTable instance already exists
            if ($.fn.dataTable.isDataTable('#example1')) {
                // Clear the existing data
                table.clear();

                // Add new data
                table.rows.add(data.map(item => [
                    id++,
                    item.Catogory,
                    item.Product_name,
                    item.Amount,
                    item.description,
                    convertDate(item.expense_Date)
                ]));

                // Redraw the table
                table.draw();
            } else {
                // If the table hasn't been initialized yet, initialize it with the provided data
                $('#example1').DataTable({
                    data: data.map(item => [
                        id++,
                        item.Catogory,
                        item.Product_name,
                        item.Amount,
                        item.description,
                        convertDate(item.expense_Date)
                    ]),
                    columns: [{
                            title: "ID"
                        },
                        {
                            title: "Category"
                        },
                        {
                            title: "Product Name"
                        },
                        {
                            title: "Expense Amount"
                        },
                        {
                            title: "Description"
                        },
                        {
                            title: "Expense Date"
                        }
                    ]
                });
            }
        }
    </script>

</body>

</html>