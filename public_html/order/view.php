<?php
session_start();
require("../../includes/functions.inc.php");

//only requires login
if (!isset($_SESSION["user_data"])) {
    header("Location: ".BASE_URL."./login.php");
}

displayToast();

//literally the same thing as confirm.php, I know this is pretty inefficient but honestly
// I couldn't give two shits

$orderID = htmlspecialchars($_GET["orderId"]);

if (!isset($orderID)) {
    makeToast("warning", "No order specified!", "Warning");
    header("Location: ".BASE_URL."account/dashboard.php");
    die();
}

$order = retrieveOrderSpecific($orderID);

//for members, can only open their own orders
// admin can seee all order
if ($_SESSION["user_data"]["user_type"] == "member") {
    if ($order["CUSTOMER_ID"] != $_SESSION["user_data"]["CUSTOMER_ID"]) {
        makeToast("warning", "You are not authorized to view this order!", "Warning");
        header("Location: ".BASE_URL."account/dashboard.php");
        die();
    }
}

$totalCost = $order["TOTAL_PRICE"];
$redeemedPoints = $order["LOYALTY_POINTS_REDEEMED"] ?? 0;
$redeemedValue = $redeemedPoints * 0.01; // Calculate value of redeemed points


$orderCode = sprintf('%08d', $orderID);
$date = date_create($order["CREATED_AT"]);
$date = date_format($date, "d M Y");

// Adjusted calculations
$shippingCost = 5;
$subTotal = number_format(($totalCost - $shippingCost + $redeemedValue), 2, ".", ","); // Add back redeemed value to calculate subtotal
$total = number_format(($totalCost), 2, ".", ",");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php head_tag_content(); ?>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/invoice.css">
    <title><?= WEBSITE_NAME ?> | Order View</title>
</head>
<body>
<div class="container-fluid">
    <div class="row flex-nowrap">
        <div class="col-auto px-0">
            <?php
            if ($_SESSION["user_data"]["user_type"] == "member") {
                side_bar();
            }
            else {
                admin_side_bar();
            }

            ?>
        </div>
        <main class="col ps-md-2 pt-2">
            <?php
            if ($_SESSION["user_data"]["user_type"] == "member") {
                header_bar("View My Order");
            }
            else {
                admin_header_bar("View Order");
            }
             ?>

            <!-- todo DASHBOARD here  -->
            <div class="container mt-3">
                <div class="row justify-content-center">
                    <div class="col-lg-12 me-3 mt-3 mb-5">

                        <div class="row">
                            <div class="col-md-12 mx-0">
                                <div id="msform">
                                    <!--  invoice-->
                                    <div class="invoice-box bg-white" id="invoice">
                                        <table>
                                            <tr class="top">
                                                <td colspan="2">
                                                    <table>
                                                        <tr>
                                                            <td class="title">
                                                                <img src="<?= BASE_URL ?>assets/images/logo1.png" style="width: 100%; max-width: 200px; object-fit: contain;" />
                                                            </td>

                                                            <td>
                                                                Invoice: <span class="fw-bold">#<?= $orderCode; ?></span><br />
                                                                Created: <?= $date; ?><br />
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>

                                            <tr class="information">
                                                <td colspan="2">
                                                    <table>
                                                        <tr>
                                                            <td>
                                                                Ground Floor, No: 14, Jalan Wallagonia 1 <br/>
                                                                Taman Universiti Wallagonia,<br/>
                                                                35400 Tapah, Perak <br/>
                                                                +6011-115 62807 <br/>
                                                            </td>

                                                            <td>
                                                                <?= ($order["FIRST_NAME"] . " " . $order["LAST_NAME"]) ?><br />
                                                                <?= $order["EMAIL"] ?? "-" ?><br />
                                                                <?= $order["PHONE"] ?? "-" ?>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>

                                            <tr class="heading">
                                                <td>Payment Method</td>
                                                <td></td>
                                            </tr>

                                            <tr class="details">
                                                <td>Debit/Credit Card</td>
                                                <td></td>
                                            </tr>

                                            <tr class="heading">
                                                <td>Item</td>

                                                <td>Price</td>
                                            </tr>

                                            <?php
                                            $orderLines = retrieveAllOrderLines($orderID);
                                            foreach ($orderLines as $item) {
                                                $cost = $item["PRICE"] * $item["QUANTITY"];
                                                $cost = number_format($cost, 2);
                                                echo "<tr class='item'>
                                                    <td>{$item["PRODUCT_NAME"]} ({$item["PRICE"]}) x{$item["QUANTITY"]}</td>
                                                    <td>RM{$cost}</td>
                                                </tr>";
                                            }

                                            ?>

                                            <tr class="item">
                                                <td>Subtotal</td>

                                                <td>RM<?= $subTotal ?></td>
                                            </tr>

                                            <?php if ($redeemedPoints > 0): ?>
                                                <tr class="item">
                                                    <td>Redeemed Points (<?= $redeemedPoints ?> points)</td>
                                                    <td>-RM<?= number_format($redeemedValue, 2) ?></td>
                                                </tr>
                                            <?php endif; ?>

                                            <tr class="item last">
                                                <td>Delivery Cost</td>

                                                <td>RM5.00</td>
                                            </tr>

                                            <tr class="total">
                                                <td></td>

                                                <td>Total: RM<?= $total ?></td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="text-center mt-5">
                                <a href="<?= BASE_URL ?>" class="btn btn-link d-print-none me-1">Back to Home</a>
                                <button class="btn btn-warning text-white d-print-none" style="width: 10%;" onclick="window.print()">Print</button>
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
</body>

</html>