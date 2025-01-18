<?php
session_start();
require("../includes/functions.inc.php");

// check if the form has been submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $name = htmlspecialchars($_POST["name"]);
        $email = htmlspecialchars($_POST["email"]);
        $message = htmlspecialchars($_POST["message"]);

        require_once("../mail.inc.php");
        $date = date_create("now");
        $dateFormatted = date_format($date, "d M Y");

        $subject = "Message from $name";
        $content = "<p>This message is from $name ($email)</p>
                    <p>$message</p>";
        $body = "{$content}";

        sendMail("wafithird@gmail.com", $subject, $body) or throw new Exception("Message wasn't sent!");;

        makeToast("success", "Message successfully sent!", "Success");
    }
    catch (exception $e){
            makeToast("error", $e->getMessage(), "Error");
        }

    header("Location: /contact-us.php");
    die();

}
displayToast();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php head_tag_content(); ?>
<title><?= WEBSITE_NAME ?> | Contact Us</title>
</head>
<body>
<?php nav_menu(); ?>
<section id="billboard" class="position-relative overflow-hidden bg-body">
</section>
<div class="container my-5 py-5">
    <form action="<?php current_page(); ?>" method="post">
        <div class="row pt-5">
            <h1>Contact Us</h1>
            <div class="row">
                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" class="form-control" id="name" name="name" placeholder="Enter your name">
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email">
                </div>
                <div class="form-group">
                    <label for="message">Message</label>
                    <textarea class="form-control" id="message" rows="5" name="message" placeholder="Enter your message"></textarea>
                </div>
            </div>
            <div class="row mt-5 justify-content-end">
                <div class="col-3">
                    <button type="submit" class="btn btn-primary w-100">Submit</button>
                </div>

            </div>

        </div>
    </form>
</div>
<?php footer(); ?>


<?php body_script_tag_content(); ?>
</body>
</html>

