<?php

require("../../includes/functions.inc.php");

session_start();

employee_login_required();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $postedToken = $_POST["token"];
    try{
        if(!empty($postedToken)){
            if(isTokenValid($postedToken)){
                if (!checkAuthority(3)) {
                    throw new Exception("Requires Employee authority!");
                }

                //todo update transaction
                if (isset($_POST["update"])) {
                    $orderID = htmlspecialchars($_POST["order_id"]);
                    $orderStatus = htmlspecialchars($_POST["status"]);
                    $employeeID = $_SESSION["user_data"]["EMPLOYEE_ID"];

                    updateOrderStatusAndEmployeeID($orderID, $orderStatus, $employeeID) or throw new Exception("Couldn't update transaction status");

                    //notify user here via mail (MEMBER ONLY)
                    // I'm so stupid, I made email in member table only. Now I must suffer the consequences.

                    $order = retrieveOrderSpecificMemberOnly($orderID);

                    if (isset($order)) {
                        $fullName = $order["FIRST_NAME"]." ".$order["LAST_NAME"];

                        $orderCode = sprintf("%08d", $order["ORDER_ID"]);
                        $date = date_create($order["ORDER_CREATED_AT"]);
                        $dateFormatted = date_format($date, "d M Y");

                        require_once("../../mail.inc.php");
                        $subject = "";
                        $content = "";
                        $website_name = WEBSITE_NAME;
                        if ($orderStatus === "PENDING"){
                            $subject = "Your Order #{$orderCode} is Pending Confirmation";
                            $content = "
                        <p>Thank you for choosing {$website_name} for your snack cravings! We have received your order and it is currently being processed. Please note that your payment is pending verification.</p>
                        <p><strong>Order Details:</strong></p>
                        <ul>
                            <li>Order Code: #{$orderCode}</li>
                            <li>Order Date: {$dateFormatted}</li>
                        </ul>
                        <p>We are working diligently to confirm your payment and process your order. Once the payment is verified, we will proceed with packing and shipping your delicious snacks. You will receive a confirmation email with the shipment details.</p>
                        <p>If you have any questions or need further assistance, please feel free to reach out to our customer support team at <a href='mailto:kerepekfunz5@gmail.com'>{$website_name} Customer Support Team</a>.</p>
                        <p>Thank you for your patience!</p>";
                        }
                        else if ($orderStatus === "COMPLETED") {
                            $estimatedDeliveryDate = date('d M Y', strtotime("+10 day"));
                            $subject = "Your Order #{$orderCode} is Complete - Enjoy Your Snacks!";
                            $content = "
                        <p>Hooray! Your order from {$website_name} has been successfully processed and shipped. Your delicious snacks are on their way to you!</p>
                        <p><strong>Order Details:</strong></p>
                        <ul>
                            <li>Order Code: #{$orderCode}</li>
                            <li>Order Date: {$dateFormatted}</li>
                            <li>Estimated Delivery Date: {$estimatedDeliveryDate}</li>
                        </ul>
                        <p>We want you to have the best snacking experience, so please make sure to keep an eye out for your package. Once it arrives, indulge in the mouthwatering flavors of our premium snacks.</p>
                        <p>We hope you enjoy every bite! If you have any feedback or questions about your order, please don't hesitate to contact our friendly customer support team at <a href='mailto:kerepekfunz5@gmail.com'>{$website_name} Customer Support Team</a>.</p>
                        <p>Thank you for choosing {$website_name}!</p>";
                        }
                        else if ($orderStatus === "CANCELLED") {
                            $subject = "Important: Your Order #{$orderCode} has been Cancelled";
                            $content = "
                         <p>We regret to inform you that your order with {$website_name} has been cancelled. We apologize for any inconvenience caused.</p>
                        <p><strong>Order Details:</strong></p>
                        <ul>
                            <li>Order Code: #{$orderCode}</li>
                            <li>Order Date: {$dateFormatted}</li>
                        </ul>
                        <p>Due to unforeseen circumstances, we were unable to fulfill your order as requested. Rest assured that any payment made for the cancelled order will be refunded to your original payment method.</p>
        <p>If you have any questions or concerns about the cancellation, please reach out to our customer support team at <a href='mailto:kerepekfunz5@gmail.com'>{$website_name} Customer Support Team</a>. We value your satisfaction and would be happy to assist you.</p>
        <p>Once again, we apologize for the cancellation and any inconvenience caused. We hope to have the opportunity to serve you better in the future.</p>";
                        }
                        else {
                            throw new Exception("Order status does not exist!");
                        }

                        $body = "<h1>Dear {$fullName},</h1>
                             {$content}
                             <p>Sincerely,</p>
                             <p>{$website_name} Team</p>";

                        sendMail($order["EMAIL"], $subject, $body) or throw new Exception("Message wasn't sent!");;

                    }

                    makeToast("success", "Order successfully updated!", "Success");
                }

                //todo delete order
                else if (isset($_POST["delete"])) {
                    $orderID = htmlspecialchars($_POST["order_id"]);

                    deleteOrder($orderID) or throw new Exception("Couldn't delete order");
                    makeToast("success", "Order successfully deleted!", "Success");
                }
                //todo create order admin side
                else if (isset($_POST["create"])) {
                    $conn = OpenConn();
                    //insert the customer
                    $first_name = htmlspecialchars($_POST["first_name"]);
                    $last_name = htmlspecialchars($_POST["last_name"]);
                    $phone = htmlspecialchars($_POST["phone"]);
                    $address = htmlspecialchars($_POST["address"]);
                    $postcode = htmlspecialchars($_POST["postcode"]);
                    $city = htmlspecialchars($_POST["city"]);
                    $state = htmlspecialchars($_POST["state"]);

                    if (!createCustomer($first_name, $last_name, $phone, $address, $city, $state, $conn)) {
                        throw new Exception("Customer was not able to be created.");
                    }

                    //get customer id
                    $customer_id = getCurrentCustomerId($conn);

                    //create a pseudo cart
                    $total_price = 0;
                    $cart = [];

                    foreach ($_POST['product_ids'] as $index => $product_id) {
                        // Skip empty product IDs or quantities
                        if (empty($product_id) || empty($_POST['quantities'][$index])) {
                            continue;
                        }

                        $product = retrieveProduct($product_id);
                        if (!isset($product)) {
                            throw new Exception("Invalid product ID detected.");
                        }
                        $quantity = intval($_POST['quantities'][$index]);
                        $total_price += $product["PRODUCT_PRICE"] * $quantity;

                        $cart[] = [
                            'quantity' => $quantity,
                            'product' => [
                                'PRODUCT_ID' => $product_id,
                                'PRODUCT_PRICE' => $product["PRODUCT_PRICE"]
                            ]
                        ];
                    }

                    if (empty($cart)) {
                        throw new Exception('Error: No valid products in the cart.');
                    }

                    $shipping_cost = 5;
                    $total_price += $shipping_cost;

                    // Pass in conn to make sure these two are using the same connection
                    //Loyalty points always zero if not a member
                    $order_result = createOrderCustomer($total_price, $customer_id, $cart, 0, $conn);

                    if (isset($order_result)) {
                        makeToast("success", "Order created successfully! Order ID: " . $order_result['ORDER_ID'], "Success");
                    } else {
                        throw new Exception("Failed to create order.");
                    }

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

    header("Location: ".BASE_URL."admin/manage-orders.php");
    die();
}

$orderCount = retrieveOrderCount()["COUNT"] ?? 0;
$orders = retrieveAllOrders();

displayToast();
$token = getToken();
?>
<!DOCTYPE html>
<html>

<head>
    <?php head_tag_content(); ?>
    <title><?= WEBSITE_NAME ?>| Manage Orders</title>
</head>
<body>
<div class="container-fluid">
    <div class="row flex-nowrap">
        <div class="col-auto px-0">
            <?php admin_side_bar() ?>
        </div>
        <main class="col ps-md-2 pt-2">
            <?php admin_header_bar("Manage Orders") ?>

            <!-- todo users here  -->
            <div class="container">
                <div class="row mt-4 gx-4 ms-3">
                    <div class="p-3 mb-5 bg-body rounded row gx-3">
                        <div class="row">
                            <span class="h3"><?= $orderCount ?> orders found</span>
                        </div>
                        <div class="shadow p-3 mb-5 mt-3 bg-body rounded row gx-3 mx-1">
                            <div class="row">
                                <div class="col">
                                    <span class="fs-1 mb-3">Orders</span>
                                </div>
                                <div class="col text-end">
                                    <?php if (checkAuthority(3)): ?>
                                    <button type="button" class="btn btn-danger add-order">
                                        <span class="h5"><i class="bi bi-plus-circle"> </i>Add</span>
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="row">
                                <?php
                                if (isset($orders)) {
                                    orders_adminOrders($orders);
                                }
                                else {
                                    echo "<span class='text-center'>No orders found!</span>";
                                }
                                ?>
                            </div>

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

        //this ones going to be a bit complicated
        // use api (api/products) retrieve all products
        // order is added in a table (can add and delete item) to create a pseudo cart
        // also have to fill in customer info (first name, last name, email, phone, address, postcode, city, state)
        $('.add-order').on('click', function () {
            // Fetch products using AJAX
            $.ajax({
                url: '<?= BASE_URL ?>api/product.php',
                method: 'GET',
                success: function (response) {
                    const parsedJSON = JSON.parse(response);
                    let products = [];

                    if (Array.isArray(parsedJSON.message)) {
                        products = parsedJSON.message;
                    } else if (typeof parsedJSON.message === "object") {
                        products = Object.values(parsedJSON.message);
                    }

                    let formHTML = `
                <form id="order-form" method="POST">
                    <h5>Customer Information</h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="first_name">First Name</label>
                            <input type="text" class="form-control" name="first_name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="last_name">Last Name</label>
                            <input type="text" class="form-control" name="last_name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="phone">Phone</label>
                            <input type="text" class="form-control" name="phone" required>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="address">Address</label>
                            <input type="text" class="form-control" name="address" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="postcode">Postcode</label>
                            <input type="text" class="form-control" name="postcode" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="city">City</label>
                            <input type="text" class="form-control" name="city" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="state">State</label>
                            <input type="text" class="form-control" name="state" required>
                        </div>
                    </div>
                    <hr/>

                    <h5>Order Items</h5>
                    <div id="cart-items">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <select class="form-select product-select" name="product_ids[]">
                                    <option value="" selected>Select a product</option>
                                    ${products.map(product => `
                                        <option value="${product.PRODUCT_ID}">
                                            ${product.PRODUCT_NAME} - RM${product.PRODUCT_PRICE}
                                        </option>
                                    `).join('')}
                                </select>
                            </div>
                            <div class="col-md-3">
                                <input type="number" class="form-control" name="quantities[]" min="1" placeholder="Quantity" required>
                            </div>
                            <div class="col-md-3 text-end">
                                <button type="button" class="btn btn-danger remove-item">Remove</button>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-secondary" id="add-item">Add Item</button>
                </form>
            `;

                    bootbox.dialog({
                        title: "Create Order",
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
                                    let form = $('#order-form')
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

                    // Add functionality for adding/removing items
                    $(document).on('click', '#add-item', function () {
                        const selectedProductIds = $('select.product-select').map(function () {
                            return $(this).val(); // Get the currently selected product IDs
                        }).get();

                        if (selectedProductIds.includes("")) {
                            alert("Please select a product for the existing item before adding a new one.");
                            return;
                        }

                        $('#cart-items').append(`
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <select class="form-select product-select" name="product_ids[]">
                                <option value="" selected>Select a product</option>
                                ${products.map(product => `
                                    <option value="${product.PRODUCT_ID}">
                                        ${product.PRODUCT_NAME} - RM${product.PRODUCT_PRICE}
                                    </option>
                                `).join('')}
                            </select>
                        </div>
                        <div class="col-md-3">
                            <input type="number" class="form-control" name="quantities[]" min="1" placeholder="Quantity" required>
                        </div>
                        <div class="col-md-3 text-end">
                            <button type="button" class="btn btn-danger remove-item">Remove</button>
                        </div>
                    </div>
                `);
                    });

                    $(document).on('click', '.remove-item', function () {
                        $(this).closest('.row').remove();
                    });

                    $(document).on('change', '.product-select', function () {
                        const selectedProductId = $(this).val();
                        const duplicate = $('select.product-select').filter(function () {
                            return $(this).val() === selectedProductId;
                        }).length > 1;

                        if (duplicate) {
                            alert("This product is already in the cart. Please update the quantity instead.");
                            $(this).val(""); // Reset the selection
                        }
                    });
                },
                error: function () {
                    alert("Failed to fetch products. Please try again.");
                }
            });
        });


        $('.edit-order').on('click', function ()  {
            const orderId = $(this).data('order-id');;

            bootbox.confirm({
                title: "Confirm Update",
                message: "Are you sure you want to update this order status? The user will be notified of the order.",
                buttons: {
                    confirm: {
                        label: "Yes, update",
                        className: "btn-danger"
                    },
                    cancel: {
                        label: "Cancel",
                        className: "btn-secondary"
                    }
                },
                callback: function (result) {
                    if (result) {
                        const form = $('#update_status_' + orderId);

                        form.append($("<input>", {
                            type: "hidden",
                            name: "update",
                            value: true
                        }));

                        form.append($("<input>", {
                            type: "hidden",
                            name: "order_id",
                            value: orderId
                        }));

                        form.append($("<input>", {
                            type: "hidden",
                            name: "token",
                            value: token
                        }));

                        form.submit();
                    }
                }
            });

        });

        $('.delete-order').on('click', function () {
            const orderId = $(this).data('order-id');

            bootbox.confirm({
                title: "Confirm Deletion",
                message: "Are you sure you want to delete this order? This action cannot be undone.",
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
                            action: '<?= BASE_URL ?>admin/manage-orders.php',
                            method: 'POST'
                        });

                        form.append($('<input>', {
                            type: 'hidden',
                            name: 'order_id',
                            value: orderId
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