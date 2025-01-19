<?php

require("../../includes/functions.inc.php");

session_start();

employee_login_required();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $postedToken = $_POST["token"];
    try{
        if(!empty($postedToken)){
            if(isTokenValid($postedToken)){
                //update product todo
                if (isset($_POST["update"])) {
                    $productID = htmlspecialchars($_POST["product_id"]);

                    $productName = htmlspecialchars($_POST["product_name"]);
                    $productPrice = htmlspecialchars($_POST["product_price"]);
                    $inventoryQuantity = htmlspecialchars($_POST["inventory_quantity"]);
                    $productDescription = htmlspecialchars($_POST["product_desc"]);

                    //create image
                    $file = $_FILES['product_image'];

                    if (isset($file) && $file['error'] !== UPLOAD_ERR_NO_FILE) {
                        $fileName = $file['name'];
                        $fileTmpName = $file['tmp_name'];
                        $fileSize = $file['size'];

                        $fileArr = explode('.', $fileName);
                        $fileExt = strtolower(end($fileArr));

                        $allowed = ['jpg','jpeg','png'];

                        $fileNameTrue = str_replace(" ", "-", reset($fileArr));
                        $fileNameNew = $fileNameTrue . "." . $fileExt;
                        $fileDestinationRelative = BASE_URL . 'assets/images/' . $fileNameNew;
                        $fileDestination = $_SERVER['DOCUMENT_ROOT'] . $fileDestinationRelative;

                        if (in_array($fileExt, $allowed)) {
                            if ($fileSize < 10485760) {
                                move_uploaded_file($fileTmpName, $fileDestination);
                            }
                            else {
                                makeToast("error", "File too large!", "Error");
                            }
                        }
                        else{
                            makeToast("error", "Filetype is not allowed!", "Error");
                        }
                    }
                    else {
                        $fileDestinationRelative = htmlspecialchars($_POST["product_image_original"]);
                    }

                    //create product here
                    updateProduct($productID, $productName, $fileDestinationRelative, $productPrice, $inventoryQuantity, $productDescription)
                    or throw new Exception("Couldn't update product");

                    makeToast("success", "Product successfully updated!", "Success");

                }
                //delete product todo
                else if (isset($_POST["delete"])) {
                    $productID = htmlspecialchars($_POST["product_id"]);

                    deleteProduct($productID) or throw new Exception("Couldn't delete product");
                    makeToast("success", "Product successfully deleted!", "Success");
                }
                else if (isset($_POST["create"])) {
                    $productName = htmlspecialchars($_POST["product_name"]);
                    $productPrice = htmlspecialchars($_POST["product_price"]);
                    $inventoryQuantity = htmlspecialchars($_POST["inventory_quantity"]);
                    $productDescription = htmlspecialchars($_POST["product_desc"]);

                    $defaultImage = BASE_URL . 'assets/images/default.jpg';
                    $fileDestinationRelative = $defaultImage;

                    // Process image upload
                    $file = $_FILES['product_image'];
                    if (isset($file) && $file['error'] !== UPLOAD_ERR_NO_FILE) {
                        $fileName = $file['name'];
                        $fileTmpName = $file['tmp_name'];
                        $fileSize = $file['size'];

                        $fileArr = explode('.', $fileName);
                        $fileExt = strtolower(end($fileArr));

                        $allowed = ['jpg', 'jpeg', 'png'];

                        // Generate safe filename
                        $fileNameTrue = str_replace(" ", "-", reset($fileArr));
                        $fileNameNew = $fileNameTrue . "." . $fileExt;
                        $fileDestinationRelative = BASE_URL . 'assets/images/' . $fileNameNew;
                        $fileDestination = $_SERVER['DOCUMENT_ROOT'] . '/' . $fileDestinationRelative;

                        //error checking
                        try {
                            if (!in_array($fileExt, $allowed)) {
                                throw new Exception("File type is not allowed!");
                            }

                            if ($fileSize >= 10485760) { // 10MB
                                throw new Exception("File too large! Max size: 10MB.");
                            }

                            if (!move_uploaded_file($fileTmpName, $fileDestination)) {
                                throw new Exception("Couldn't upload file");
                            }
                        } catch (Exception $e) {
                            makeToast('error', $e->getMessage(), "Error");
                            $fileDestinationRelative = $defaultImage; //reset to default
                        }

                    }

                    // Create product
                    createProduct($productName, $fileDestinationRelative, $productPrice, $inventoryQuantity, $productDescription)
                    or throw new Exception("Couldn't create product");

                    makeToast("success", "Product successfully created!", "Success");
                }
            }
            else{
                makeToast("warning", "Please refrain from attempting to resubmit previous form", "Warning");
            }
        }
        else {
            throw new Exception("Token not found");
        }
    }
    catch (Exception $e){
        makeToast("error", $e->getMessage(), "Error");
    }

    header("Location: ".BASE_URL."admin/manage-products.php");
    die();
}

$productCount = retrieveProductCount()["COUNT"] ?? 0;

$products = retrieveAllProduct();

displayToast();
$token = getToken();
?>
<!DOCTYPE html>
<html>

<head>
    <?php head_tag_content(); ?>
    <title><?= WEBSITE_NAME ?> | Manage Products</title>
</head>
<body>
<div class="container-fluid">
    <div class="row flex-nowrap">
        <div class="col-auto px-0">
            <?php admin_side_bar() ?>
        </div>
        <main class="col ps-md-2 pt-2">
            <?php admin_header_bar("Manage Products") ?>

            <!-- todo users here  -->
            <div class="container">
                <div class="row mt-4 gx-4 ms-3">
                    <div class="p-3 mb-5 bg-body rounded row gx-3">
                        <div class="row">
                            <span class="h3"><?= $productCount ?> products found</span>
                        </div>

                        <div class="shadow p-3 mb-5 mt-3 bg-body rounded row gx-3 mx-1">
                            <div class="col">
                                <span class="fs-1 mb-3">Products</span>
                            </div>
                            <div class="col text-end">
                                <button type="button" class="btn btn-danger add-product">
                                    <span class="h5"><i class="bi bi-plus-circle"> </i>Add</span>
                                </button>
                            </div>
                            <table class="table table-responsive table-hover table-bordered">
                                <thead class="table-light">
                                <tr>
                                    <th scope="col">ID</th>
                                    <th scope="col">Image</th>
                                    <th scope="col">Name</th>
                                    <th scope="col">Description</th>
                                    <th scope="col">Price</th>
                                    <th scope="col">Inventory Quantity</th>
                                    <th scope="col">Creation Date</th>
                                    <th scope="col" class="text-center">Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                $base_url = BASE_URL;

                                if ($products != null) {
                                    foreach ($products as $product) {
                                        $date = date_create($product["CREATED_AT"]);
                                        $dateFormatted = date_format($date, "d M Y");
                                        $price = number_format((float)$product["PRODUCT_PRICE"], 2, ".", ",");
                                        echo "
                    <tr class='align-middle'>
                        <th scope='row'>{$product["PRODUCT_ID"]}</th>
                        <td><img class='img-fluid w-100' src='{$product["PRODUCT_IMAGE"]}' style='max-width: 150px; height: auto;'></td>
                        <td>{$product["PRODUCT_NAME"]}</td>
                        <td>{$product["PRODUCT_DESCRIPTION"]}</td>
                        <td>RM{$price}</td>
                        <td>{$product["INVENTORY_QUANTITY"]}</td>
                        <td>{$dateFormatted}</td>
                        <td class='text-center'>
                            <a type='button' class='h4 edit-product' 
                               data-product-id='{$product["PRODUCT_ID"]}' 
                               data-product-name='{$product["PRODUCT_NAME"]}' 
                               data-product-desc='{$product["PRODUCT_DESCRIPTION"]}' 
                               data-product-price='{$product["PRODUCT_PRICE"]}' 
                               data-inventory-quantity='{$product["INVENTORY_QUANTITY"]}' 
                               data-product-image='{$product["PRODUCT_IMAGE"]}'> 
                               <i class='bi bi-pencil-square'></i>
                            </a>
                            <a type='button' class='h4 delete-product' 
                               data-product-id='{$product["PRODUCT_ID"]}'> 
                               <i class='bi bi-trash'></i>
                            </a>
                        </td>
                    </tr>";
                                    }
                                } else {
                                    echo "
                <tr class='align-middle'>
                    <td class='text-center' colspan='8'>No products available</td> 
                </tr>";
                                }
                                ?>
                                </tbody>
                            </table>

                        </div>
                    </div>

                </div>
            </div>

            <?php footer(); ?>
        </main>

    </div>
</div>
<?php body_script_tag_content();?>
<script>
    $(document).ready(function () {
        const token = '<?= $_SESSION["token"]; ?>';

        $('.add-product').on('click', function () {
            //dumber way of doing things, but easier
            let formHTML = `<form id="form" action="<?= BASE_URL ?>admin/manage-products.php" method="post" enctype="multipart/form-data">
    <div class="row g-3 p-3 border rounded">
        <div class="col-12">
            <label for="product-name" class="form-label">Product Name:</label>
            <input type="text" class="form-control" id="product-name" name="product_name" placeholder="Enter product name here" required>
        </div>
        <div class="col-12">
            <label for="product-desc" class="form-label">Product Description:</label>
            <textarea class="form-control" id="product-desc" name="product_desc" rows="3" placeholder="Enter description here" required></textarea>
        </div>
        <div class="col-md-6">
            <label for="product-price" class="form-label">Product Price:</label>
            <input type="text" class="form-control" id="product-price" name="product_price" placeholder="Enter product price here" required>
        </div>
        <div class="col-md-6">
            <label for="inventory-quantity" class="form-label">Inventory Quantity:</label>
            <input type="number" class="form-control" id="inventory-quantity" name="inventory_quantity" required>
        </div>
        <div class="col-12">
            <label for="product-image" class="form-label">Product Image:</label>
            <input type="file" class="form-control" id="product-image" name="product_image" required>
        </div>
    </div>
</form>`;

            bootbox.dialog({
                title: "Create Product",
                message: formHTML,
                size: "large",
                buttons: {
                    cancel: {
                        label: "Cancel",
                        className: "btn-secondary"
                    },
                    save: {
                        label: "Create",
                        className: "btn-primary",
                        callback: function () {
                            const form = $('#form');

                            form.append($("<input>", {
                                type: "hidden",
                                name: "create",
                                value: true
                            }));

                            form.append($("<input>", {
                                type: "hidden",
                                name: "token",
                                value: token
                            }));

                            form.submit();
                        }
                    }
                }
            });
        });

        $('.edit-product').on('click', function () {
            const productId = $(this).data('product-id');
            const productName = $(this).data('product-name');
            const productPrice = $(this).data('product-price');
            const productImage = $(this).data('product-image');
            const productDesc = $(this).data('product-desc');
            const inventoryQuantity = $(this).data('inventory-quantity');

            let formHTML = `<form id="form" action="<?= BASE_URL ?>admin/manage-products.php" method="post" enctype="multipart/form-data">
    <div class="row g-3 p-3 border rounded">
        <!-- Product Image Preview -->
        <div class="col-12 text-center mb-3">
            <img src="${productImage}" class="img-thumbnail" style="max-width: 150px; height: auto;" alt="Product Image">
        </div>

        <!-- Product ID (Readonly) -->
        <div class="col-12">
            <label for="product-id" class="form-label">Product ID:</label>
            <input type="text" class="form-control" id="product-id" name="product_id" value="${productId}" readonly>
        </div>

        <!-- Product Name -->
        <div class="col-12">
            <label for="product-name" class="form-label">Product Name:</label>
            <input type="text" class="form-control" id="product-name" name="product_name" value="${productName}" placeholder="Enter product name here" required>
        </div>

        <!-- Product Description -->
        <div class="col-12">
            <label for="product-desc" class="form-label">Product Description:</label>
            <textarea class="form-control" id="product-desc" name="product_desc" rows="3" placeholder="Enter description here" required>${productDesc}</textarea>
        </div>

        <!-- Product Price -->
        <div class="col-md-6">
            <label for="product-price" class="form-label">Product Price:</label>
            <input type="text" class="form-control" id="product-price" name="product_price" value="${productPrice}" placeholder="Enter product price here" required>
        </div>

        <!-- Inventory Quantity -->
        <div class="col-md-6">
            <label for="inventory-quantity" class="form-label">Inventory Quantity:</label>
            <input type="number" class="form-control" id="inventory-quantity" name="inventory_quantity" value="${inventoryQuantity}" required>
        </div>

        <!-- Product Image Upload -->
        <div class="col-12">
            <label for="product-image" class="form-label">Product Image:</label>
            <input type="file" class="form-control" id="product-image" name="product_image">
        </div>
    </div>
</form>
`;

            bootbox.dialog({
                title: "Edit Product",
                message: formHTML,
                size: "large",
                buttons: {
                    cancel: {
                        label: "Cancel",
                        className: "btn-secondary"
                    },
                    save: {
                        label: "Update",
                        className: "btn-primary",
                        callback: function () {
                            const form = $('#form');

                            form.append($("<input>", {
                                type: "hidden",
                                name: "update",
                                value: true
                            }));

                            form.append($("<input>", {
                                type: "hidden",
                                name: "token",
                                value: token
                            }));

                            form.append($("<input>", {
                                type: "hidden",
                                name: "product_image_original",
                                value: productImage
                            }));

                            form.submit();
                        }
                    }
                }
            });
        });

        $('.delete-product').on('click', function () {
            const productId = $(this).data('product-id');

            bootbox.confirm({
                title: "Confirm Deletion",
                message: "Are you sure you want to delete this product? This action cannot be undone.",
                buttons: {
                    confirm: {
                        label: "Yes, Delete",
                        className: "btn-danger"
                    },
                    cancel: {
                        label: "Cancel",
                        className: "btn-secondary"
                    }
                },
                callback: function (result) {
                    if (result) {
                        const form = $('<form>', {
                            action: '<?= BASE_URL ?>admin/manage-products.php',
                            method: 'POST'
                        });

                        form.append($('<input>', {
                            type: 'hidden',
                            name: 'product_id',
                            value: productId
                        }));

                        form.append($('<input>', {
                            type: 'hidden',
                            name: 'token',
                            value: token
                        }));

                        form.append($('<input>', {
                            type: 'hidden',
                            name: 'delete',
                            value: true
                        }));

                        $('body').append(form);
                        form.submit();
                    }
                }
            });
        });
    });
</script>
</body>

</html>