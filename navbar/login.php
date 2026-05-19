<?php
session_start();
require_once "../db.php";

if (!isset($_COOKIE['login_timer'])) {
    setcookie("login_timer", time(), time() + 60, "/");
    $_COOKIE['login_timer'] = time();
}

$startTime = $_COOKIE['login_timer'];
$remainingTime = 60 - (time() - $startTime);

if ($remainingTime <= 0) {
    setcookie("login_timer", "", time() - 3600, "/");
    header("Location: ../index.php");
    exit();
}

$message = "";

/* =========================
   SIGN UP
========================= */
if (isset($_POST['register'])) {

    $fullName = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];

    if ($password !== $confirmPassword) {

        $message = "Passwords do not match.";

    } else {

        $check = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();

        $result = $check->get_result();

        if ($result->num_rows > 0) {

            $message = "Email already exists.";

        } else {

            $stmt = $conn->prepare("INSERT INTO users(full_name, email, password) VALUES(?,?,?)");
            $stmt->bind_param("sss", $fullName, $email, $password);

            if ($stmt->execute()) {

    setcookie("login_timer", "", time() - 3600, "/");

    $_SESSION['user'] = $fullName;
    $_SESSION['user_email'] = $email;
    $_SESSION['user_id'] = $conn->insert_id;

    header("Location: ../index.php");
    exit();

            } else {

                $message = "Something went wrong.";

            }
        }
    }
}

/* =========================
   LOG IN
========================= */
if (isset($_POST['login'])) {

    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows > 0) {

        $user = $result->fetch_assoc();

        if ($password === $user['password']) {

    setcookie("login_timer", "", time() - 3600, "/");

    $_SESSION['user'] = $user['full_name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_id'] = $user['id'] ?? null;

    header("Location: ../index.php");
    exit();
} else {

            $message = "Incorrect password.";

        }

    } else {

        $message = "Account does not exist.";

    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Login | Alena</title>

<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{
    font-family:'DM Sans',sans-serif;
    min-height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
    background:
    linear-gradient(rgba(10,22,40,.75), rgba(10,22,40,.75)),
    url('../static/cruise1.jpg');
    background-size:cover;
    background-position:center;
}

.modal{
    background:#ffffff;
    width:100%;
    max-width:420px;
    padding:40px 36px;
    border-radius:18px;
    box-shadow:0 24px 64px rgba(0,0,0,.25);
}

.modal-tabs{
    display:flex;
    margin-bottom:28px;
    border-bottom:1px solid #e8eaf0;
}

.modal-tab{
    flex:1;
    padding:12px;
    border:none;
    background:none;
    cursor:pointer;
    font-size:.95rem;
    font-weight:600;
    color:#888;
    border-bottom:2px solid transparent;
}

.modal-tab.active{
    color:#0a1628;
    border-bottom-color:#67B5D1;
}

.modal-header{
    text-align:center;
    margin-bottom:24px;
}

.modal-logo{
    width:60px;
    margin-bottom:12px;
}

.modal-title{
    font-family:'Playfair Display',serif;
    font-size:1.6rem;
    color:#0a1628;
    margin-bottom:5px;
}

.modal-sub{
    font-size:.85rem;
    color:#777;
}

.form-group{
    margin-bottom:16px;
}

.form-group label{
    display:block;
    margin-bottom:6px;
    font-size:.8rem;
    color:#555;
    font-weight:500;
}

.form-group input{
    width:100%;
    padding:12px 14px;
    border:1px solid #dde1e9;
    border-radius:8px;
    outline:none;
    font-size:.9rem;
}

.form-group input:focus{
    border-color:#67B5D1;
}

.modal-btn{
    width:100%;
    padding:12px;
    border:none;
    border-radius:8px;
    background:#0a1628;
    color:#fff;
    font-size:.9rem;
    font-weight:600;
    cursor:pointer;
    margin-top:8px;
    transition:.2s;
}

.modal-btn:hover{
    background:#67B5D1;
}

.hidden{
    display:none;
}

.message{
    text-align:center;
    margin-bottom:16px;
    color:red;
    font-size:.85rem;
}

.back-home{
    display:block;
    text-align:center;
    margin-top:16px;
    text-decoration:none;
    color:#0a1628;
    font-size:.85rem;
}

#cookieBanner{
    position:fixed;
    bottom:20px;
    left:50%;
    transform:translateX(-50%);
    width:92%;
    max-width:950px;
    background:rgba(10,22,40,0.96);
    backdrop-filter:blur(16px);
    border:1px solid rgba(255,255,255,0.12);
    border-radius:20px;
    padding:22px 24px;
    z-index:99999;
    box-shadow:0 16px 50px rgba(0,0,0,.4);
    animation:slideUp .5s ease;
}

.cookie-content{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:30px;
}

.cookie-text{
    flex:1;
}

.cookie-text h3{
    color:#fff;
    margin-bottom:8px;
    font-size:1.1rem;
}

.cookie-text p{
    color:rgba(255,255,255,0.72);
    line-height:1.7;
    font-size:.88rem;
}

.cookie-buttons{
    display:flex;
    gap:12px;
    flex-wrap:wrap;
}

.cookie-btn{
    border:none;
    padding:12px 18px;
    border-radius:10px;
    font-size:.84rem;
    font-weight:600;
    cursor:pointer;
    transition:.2s;
}

.cookie-btn.primary{
    background:#67B5D1;
    color:#fff;
}

.cookie-btn.primary:hover{
    background:#4fa0be;
}

.cookie-btn.secondary{
    background:rgba(255,255,255,0.08);
    color:#fff;
    border:1px solid rgba(255,255,255,0.15);
}

.cookie-btn.secondary:hover{
    background:rgba(255,255,255,0.15);
}

.cookie-btn.reject{
    background:#932d2d;
    color:#fff;
}

.cookie-btn.reject:hover{
    background:#b33d3d;
}

@keyframes slideUp{

    from{
        opacity:0;
        transform:translate(-50%,50px);
    }

    to{
        opacity:1;
        transform:translate(-50%,0);
    }
}

@media(max-width:768px){

    .cookie-content{
        flex-direction:column;
        align-items:flex-start;
    }

    .cookie-buttons{
        width:100%;
    }

    .cookie-btn{
        flex:1;
    }

}

</style>

</head>

<script>
let remaining = <?php echo $remainingTime; ?>;

setTimeout(function() {
    window.location.href = "../index.php";
}, remaining * 1000);
</script>

<script>

function hideCookieBanner(){

    document.getElementById("cookieBanner").style.display = "none";
}

function acceptAllCookies(){

    document.cookie =
    "cookieChoice=all; max-age=31536000; path=/";

    hideCookieBanner();
}

function rejectCookies(){

    document.cookie =
    "cookieChoice=rejected; max-age=31536000; path=/";

    hideCookieBanner();
}

window.onload = function(){

    if(document.cookie.includes("cookieChoice=")){

        hideCookieBanner();
    }
}
</script>

<body>

<div id="cookieBanner">

    <div class="cookie-content">

        <div class="cookie-text">

            <h3>🍪 Cookie Preferences</h3>

            <p>
                We use cookies to improve website performance,
                secure login sessions, personalize your cruise
                experience, and analyze website traffic aboard
                Alena Cruise Lines.
            </p>

        </div>

        <div class="cookie-buttons">

            <button onclick="acceptAllCookies()"
            class="cookie-btn primary">

                Accept All

            </button>


            <button onclick="rejectCookies()"
            class="cookie-btn reject">

                Reject

            </button>

        </div>

    </div>

</div>

<div class="modal">

    <!-- TABS -->
    <div class="modal-tabs">

        <button class="modal-tab active"
        id="tabSignup"
        onclick="switchTab('signup')">
            Sign Up
        </button>

        <button class="modal-tab"
        id="tabLogin"
        onclick="switchTab('login')">
            Log In
        </button>

    </div>

    <!-- ERROR MESSAGE -->
    <?php if($message != ""): ?>

        <div class="message">
            <?php echo $message; ?>
        </div>

    <?php endif; ?>

    <!-- SIGN UP -->
    <form method="POST" id="formSignup">

        <div class="modal-header">

            <img src="../static/logo.svg" class="modal-logo">

            <h2 class="modal-title">
                Create Account
            </h2>

            <p class="modal-sub">
                Experience the Pearl of the Orient Sea
            </p>

        </div>

        <div class="form-group">
            <label>Full Name</label>
            <input type="text" name="full_name" required>
        </div>

        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" required>
        </div>

        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required>
        </div>

        <div class="form-group">
            <label>Confirm Password</label>
            <input type="password" name="confirm_password" required>
        </div>

        <button type="submit"
        name="register"
        class="modal-btn">
            Create Account
        </button>

    </form>

    <!-- LOG IN -->
    <form method="POST"
    id="formLogin"
    class="hidden">

        <div class="modal-header">

            <img src="../static/logo.svg" class="modal-logo">

            <h2 class="modal-title">
                Welcome Back
            </h2>

            <p class="modal-sub">
                Log in to your account
            </p>

        </div>

        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" required>
        </div>

        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required>
        </div>

        <button type="submit"
        name="login"
        class="modal-btn">
            Log In
        </button>

    </form>

    <a href="../index.php" class="back-home">
        Back to Home
    </a>

</div>

<script>

function switchTab(tab){

    const signupForm = document.getElementById("formSignup");
    const loginForm = document.getElementById("formLogin");

    const tabSignup = document.getElementById("tabSignup");
    const tabLogin = document.getElementById("tabLogin");

    if(tab === "signup"){

        signupForm.classList.remove("hidden");
        loginForm.classList.add("hidden");

        tabSignup.classList.add("active");
        tabLogin.classList.remove("active");

    }else{

        signupForm.classList.add("hidden");
        loginForm.classList.remove("hidden");

        tabSignup.classList.remove("active");
        tabLogin.classList.add("active");

    }
}

</script>

</body>
</html>
