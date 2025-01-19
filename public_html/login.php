<?php
session_start();
require("../includes/functions.inc.php");

employee_forbidden();
member_forbidden();

// check if the form has been submitted

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $postedToken = $_POST["token"];
    try{
        if(isset($postedToken)){
            if(isTokenValid($postedToken)){
                if (isset($_POST["login"])) {

                    $username = filter_var($_POST["username"], FILTER_SANITIZE_SPECIAL_CHARS);
                    $password = filter_var($_POST["password"], FILTER_SANITIZE_SPECIAL_CHARS);
                    $userType = filter_var($_POST["usertype"], FILTER_SANITIZE_SPECIAL_CHARS);

                    if (empty($username) || empty($password)) {
                        makeToast("error", "Either username or password is empty", "Error");
                    }

                    $userData = null;
                    if ($userType == "member") {
                        $userData = verifyMember($username, $password);
                    }
                    else if ($userType == "employee") {
                        $userData = verifyEmployee($username, $password);
                    }

                    if (isset($userData)) {
                        $userData["user_type"] = $userType;
                        $_SESSION["user_data"] = $userData;
                        makeToast("success", "You are now logged in!", "Success");
                        header("Location: ". BASE_URL . "index.php");
                        die();
                    }
                    else {
                        throw new Exception("Either username or password is incorrect.");
                    }
                }
                //password reset code (undecided to proceed or not)
//                if (isset($_POST["pwdreset"])){
//                    $email = htmlspecialchars($_POST["email"]);
//
//                    $user = retrieveUserByEmail($email) or throw new Exception("We apologize, but user with that email does not exist!");
//
//                    $urlToken = bin2hex(random_bytes(32));
//                    $protocol = isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] === "on" ? "https://" : "http://";
//                    $domain = $_SERVER["HTTP_HOST"];
//                    $url = $protocol . $domain . "/create-new-password.php?token=$urlToken";
//
//                    //expires date
//                    $dateVal = date_create("now");
//                    date_add($dateVal, date_interval_create_from_date_string("3 days"));
//                    $dateStr = $dateVal->format('Y-m-d');
//
//                    $subject = "Airasia Password Reset";
//                    $body = "
//                    <html>
//                    <body>
//                      <h2>Reset Your Airasia Password</h2>
//                      <p>Hello, {$user["user_fname"]}</p>
//                      <p>We received a request to reset your Airasia account password. To proceed with the password reset, please click the link below:</p>
//                      <a href='$url'>Reset Password</a>
//                      <p>If you did not initiate this request, you can safely ignore this email.</p>
//                      <p>Thank you,</p>
//                      <p>AirAsia Team</p>
//                    </body>
//                    </html>
//                    ";
//
//                    sendMail($email, $subject, $body) or throw new Exception("Message wasn't sent!");
//
//                    createPasswordReset($email, $urlToken, $dateStr) or throw new Exception("Password reset failed!");
//
//                    makeToast("success", "Password reset URL sent to your mail!", "Success");
//                }
            }
            else{
                makeToast("warning", "Please refrain from attempting to resubmit previous form", "Warning");
            }
        }
        else {
            throw new exception("Token not found");
        }
    }
    catch (exception $e){
        makeToast("error", $e->getMessage(), "Error");
    }

    header("Location: ". BASE_URL . "login.php");
    die();
}

displayToast();
$token = getToken();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php head_tag_content(); ?>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/login.css">
    <title><?= WEBSITE_NAME ?> - Login</title>
</head>
<body>
<?php nav_menu(); ?>
    <section id="billboard" class="position-relative overflow-hidden bg-body">
    </section>
    <div class="container-fluid">
        <div class="container my-5 py-5">

            <div class="login-container">
                <h2>Login</h2>
                <form action="<?php current_page(); ?>" method="POST">
                    <div class="mb-3 d-flex justify-content-center">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="usertype" id="usertype1" value="member" checked>
                            <label class="form-check-label" for="usertype1">Member</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="usertype" id="usertype2" value="employee">
                            <label class="form-check-label" for="usertype2">Employee</label>
                        </div>
                    </div>

                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" placeholder="Enter your username" required>
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter your password" required>
                    <button type="submit" name="login">Login</button>
                    <input type="hidden" name="token" value="<?= $token ?>">
                </form>

                <div class="footer-links">
                    <a href="<?= BASE_URL ?>register.php">Not a member yet? Sign Up Now</a>
                </div>
            </div>

        </div>
        <?php footer(); ?>
    </div>


    <?php body_script_tag_content(); ?>
</body>
</html>

