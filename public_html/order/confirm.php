<?php
session_start();
require("../../includes/functions.inc.php");


member_login_required();

displayToast();
//cart no longer needed, unset it
unset($_SESSION["cart"]);

$user = $_SESSION["user_data"];
$orderID = $_SESSION["ORDER_ID"];

$order = retrieveOrderSpecific($orderID);
$loyaltyPointsReward = $_SESSION["loyaltyPointsReward"];
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
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/progress.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/invoice.css">
    <title><?= WEBSITE_NAME ?> | Order Completed</title>
</head>
<body>
<div class="container-fluid">
    <div class="row flex-nowrap">
        <div class="col-auto px-0">
            <?php side_bar() ?>
        </div>
        <main class="col ps-md-2 pt-2">
            <?php header_bar("Complete") ?>

            <!-- todo DASHBOARD here  -->
            <div class="container mt-3">
                <div class="row justify-content-center">
                    <div class="col-lg-12 me-3 mt-3 mb-5">
                        <div class="row ms-4">
                            <h2><strong>Thanks for shopping with <?= WEBSITE_NAME ?></strong></h2>
                            <p>You've been awarded <?= $loyaltyPointsReward ?> loyalty points for your purchase!
                                <br/>We hope you'll order again from us!</p>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mx-0">
                                <div id="msform">
                                    <!-- progressbar -->
                                    <ul id="progressbar">
                                        <li class="active"><strong>Cart</strong></li>
                                        <li class="active"><strong>Checkout</strong></li>
                                        <li class="active"><strong>Finish</strong></li>
                                    </ul>
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
                                                                <?= ($user["FIRST_NAME"] . " " . $user["LAST_NAME"]) ?><br />
                                                                <?= $user["EMAIL"] ?><br />
                                                                <?= $user["PHONE"] ?? "-" ?>
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