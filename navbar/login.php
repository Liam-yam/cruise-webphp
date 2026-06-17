<?php
session_start();
require_once "../db.php";

if (!isset($_COOKIE['login_timer'])) {
    setcookie("login_timer", time(), time() + 600, "/");
    $_COOKIE['login_timer'] = time();
}

$startTime = $_COOKIE['login_timer'];
$remainingTime = 600 - (time() - $startTime);

if ($remainingTime <= 0) {
    setcookie("login_timer", "", time() - 3600, "/");
    header("Location: ../index.php");
    exit();
}

$message = "";

// Clean up expired OTP verification tokens (5-minute TTL)
if (!empty($_SESSION['otp_verified_expires'])) {
    foreach ($_SESSION['otp_verified_expires'] as $em => $exp) {
        if (time() > $exp) {
            unset($_SESSION['otp_verified'][$em], $_SESSION['otp_verified_expires'][$em]);
        }
    }
}

if (isset($_POST['register'])) {

    $firstName      = trim($_POST['first_name']);
    $lastName       = trim($_POST['last_name']);
    $email          = trim($_POST['email']);
    $password       = $_POST['password'];
    $confirmPassword= $_POST['confirm_password'];
    $otpToken       = $_POST['otp_verified_token'] ?? '';

    // Server-side OTP gate: JS sets a hidden token after a successful
    // OTP verification. The server compares it with a session-bound
    // token that was generated when the OTP was accepted client-side.
    $expectedOtpToken = $_SESSION['otp_verified'][$email] ?? '';
    $otpValid = $expectedOtpToken !== '' && hash_equals($expectedOtpToken, $otpToken);

    if (!$otpValid) {

        $message = "Please verify your email with the OTP before creating an account.";

    } elseif ($firstName === '' || $lastName === '') {

        $message = "Please enter your first and last name.";

    } elseif ($password !== $confirmPassword) {

        $message = "Passwords do not match.";

    } else {

        $check = $conn->prepare("SELECT r.reg_no FROM tbl_registration r WHERE r.email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows > 0) {

            $message = "Email already exists.";

        } else {

            $conn->begin_transaction();

            try {

                $stmtUser = $conn->prepare("INSERT INTO tbl_user(fname, lname) VALUES(?, ?)");
                $stmtUser->bind_param("ss", $firstName, $lastName);
                $stmtUser->execute();
                $newUserId = $conn->insert_id;

                $stmtReg = $conn->prepare("INSERT INTO tbl_registration(email, password, user_id) VALUES(?, ?, ?)");
                $stmtReg->bind_param("ssi", $email, $password, $newUserId);
                $stmtReg->execute();

                $conn->commit();

                setcookie("login_timer", "", time() - 3600, "/");

                $_SESSION['user']       = trim($firstName . ' ' . $lastName);
                $_SESSION['user_email'] = $email;
                $_SESSION['user_id']    = $newUserId;

                // Consume the OTP token so it cannot be reused.
                unset($_SESSION['otp_verified'][$email], $_SESSION['otp_verified_expires'][$email]);

                header("Location: ../index.php");
                exit();

            } catch (Exception $e) {

                $conn->rollback();
                $message = "Something went wrong.";

            }
        }
    }
}

if (isset($_POST['login'])) {

    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare(
        "SELECT r.reg_no, r.email, r.password, r.user_id,
                u.fname, u.lname
           FROM tbl_registration r
           JOIN tbl_user u ON u.user_id = r.user_id
          WHERE r.email = ?"
    );
    $stmt->bind_param("s", $email);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows > 0) {

        $user = $result->fetch_assoc();

        if ($password === $user['password']) {

            setcookie("login_timer", "", time() - 3600, "/");

            $_SESSION['user']       = trim(($user['fname'] ?? '') . ' ' . ($user['lname'] ?? ''));
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_id']    = $user['user_id'] ?? null;

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

<script src="https://cdn.jsdelivr.net/npm/@emailjs/browser@4/dist/email.min.js"></script>

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

.form-row{
    display:flex;
    gap:12px;
}

.form-row .form-group{
    flex:1;
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

.modal-btn:disabled{
    background:#aab2c0;
    cursor:not-allowed;
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

/* ===== OTP Verification Step ===== */
.otp-step{
    display:none;
    animation:fadeIn .25s ease;
}
.otp-step.is-visible{
    display:block;
}
@keyframes fadeIn{
    from{opacity:0; transform:translateY(6px);}
    to{opacity:1; transform:translateY(0);}
}

.verify-email-row{
    display:flex;
    gap:8px;
    align-items:stretch;
}
.verify-email-row input{
    flex:1;
    padding:12px 14px;
    border:1px solid #dde1e9;
    border-radius:8px;
    outline:none;
    font-size:.9rem;
}
.verify-email-row input:focus{
    border-color:#67B5D1;
}
.verify-email-row--center{
    justify-content:center;
}
.otp-verify-btn{
    background:#0a1628;
    color:#fff;
    border:none;
    border-radius:8px;
    padding:15px 15px;
    font-size:.88rem;
    font-weight:600;
    cursor:pointer;
    transition:.2s;
    width:auto;
    min-width:160px;
    margin:0;
}
.otp-verify-btn:hover{
    background:#67B5D1;
}
.otp-verify-btn:disabled{
    background:#aab2c0;
    cursor:not-allowed;
}
/* .verify-email-row button rules removed: were overriding .otp-verify-btn */


.otp-input-row{
    display:flex;
    gap:8px;
    align-items:stretch;
}
.otp-input-row input{
    flex:1;
    letter-spacing:.4em;
    text-align:center;
    font-size:1.1rem;
    font-weight:600;
    padding:12px 14px;
    border:1px solid #dde1e9;
    border-radius:8px;
    outline:none;
}
.otp-input-row input:focus{
    border-color:#67B5D1;
}
.otp-input-row button{
    padding:0 16px;
    background:#0a1628;
    color:#fff;
    border:none;
    border-radius:8px;
    font-size:.82rem;
    font-weight:600;
    cursor:pointer;
    transition:.2s;
    white-space:nowrap;
}
.otp-input-row button:hover{
    background:#67B5D1;
}
.otp-input-row button:disabled{
    background:#aab2c0;
    cursor:not-allowed;
}

.otp-sent-info{
    text-align:center;
    font-size:.78rem;
    color:#777;
    margin-top:8px;
}

.status-message{
    text-align:center;
    font-size:.85rem;
    margin-bottom:14px;
    padding:10px 12px;
    border-radius:8px;
    display:none;
}
.status-message.is-visible{
    display:block;
}
.status-message[data-type="success"]{
    background:#e6f7ee;
    color:#0f6b3b;
    border:1px solid #b6e4c8;
}
.status-message[data-type="error"]{
    background:#fdecec;
    color:#a32525;
    border:1px solid #f5b5b5;
}
.status-message[data-type="info"]{
    background:#eaf4fb;
    color:#1d5b85;
    border:1px solid #b9dcef;
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

            <h3>Cookie Preferences</h3>

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

        <!-- STEP 1: Name, Email, Passwords -->
        <div class="form-row">
            <div class="form-group">
                <label>First Name</label>
                <input type="text" name="first_name" id="signupFirstName" required>
            </div>

            <div class="form-group">
                <label>Last Name</label>
                <input type="text" name="last_name" id="signupLastName" required>
            </div>
        </div>

        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" id="signupEmail" required>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" id="signupPassword" required>
            </div>

            <div class="form-group">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" id="signupConfirm" required>
            </div>
        </div>

        <!-- Hidden token populated by JS after successful OTP verification -->
        <input type="hidden" name="otp_verified_token" id="otpVerifiedToken" value="">

        <!-- STEP 2: Send OTP -->
        <div class="form-group">
            <div class="verify-email-row verify-email-row--center">
                <button type="button" id="sendOtpBtn" class="otp-verify-btn">
                    Verify My Email
                </button>
            </div>
        </div>

        <div class="status-message" id="otpStatus" data-type="info"></div>

        <!-- STEP 3: Enter OTP (the LAST step before submit) -->
        <div class="form-group otp-step" id="otpStep">
            <label>Enter the 6-digit code we sent to your email</label>
            <div class="otp-input-row">
                <input type="text"
                       id="otpInput"
                       maxlength="6"
                       inputmode="numeric"
                       pattern="[0-9]{6}"
                       placeholder="6-digit code"
                       autocomplete="one-time-code">
                <button type="button" id="resendOtpBtn">Resend</button>
            </div>
            <div class="otp-sent-info">
                Code expires in <span id="otpCountdown">5:00</span>
            </div>
        </div>

        <button type="submit"
        name="register"
        id="createAccountBtn"
        class="modal-btn"
        disabled>
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

// ============================================================
// TAB SWITCHING
// ============================================================
function switchTab(tab){

    const signupForm = document.getElementById("formSignup");
    const loginForm  = document.getElementById("formLogin");

    const tabSignup  = document.getElementById("tabSignup");
    const tabLogin   = document.getElementById("tabLogin");

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

// ============================================================
// OTP VERIFICATION (client-side)
// ============================================================
(function () {
    // EmailJS credentials
    const SERVICE_ID  = "service_2z9u3jk";
    const TEMPLATE_ID = "template_hytw4xk";
    const PUBLIC_KEY  = "5QG5iTForDmYl0BwE";

    const OTP_TTL_MS          = 5 * 60 * 1000;   // 5 minutes
    const RESEND_COOLDOWN_MS  = 30 * 1000;       // 30s between resends
    const MAX_ATTEMPTS        = 5;

    let otpHash          = null;
    let otpExpiry        = 0;
    let attemptsLeft     = MAX_ATTEMPTS;
    let lastSentAt       = 0;
    let countdownTimer   = null;

    // ------- DOM refs -------
    const emailInput       = document.getElementById("signupEmail");
    const sendOtpBtn       = document.getElementById("sendOtpBtn");
    const resendOtpBtn     = document.getElementById("resendOtpBtn");
    const otpInput         = document.getElementById("otpInput");
    const otpStep          = document.getElementById("otpStep");
    // (passwordStep removed: passwords are now visible from the start)
    const otpStatus        = document.getElementById("otpStatus");
    const otpCountdown     = document.getElementById("otpCountdown");
    const createBtn        = document.getElementById("createAccountBtn");
    const tokenInput       = document.getElementById("otpVerifiedToken");
    // (passwordField ref removed: not needed in the new flow)

    // ------- Init EmailJS -------
    if (typeof emailjs !== "undefined") {
        emailjs.init(PUBLIC_KEY);
    }

    // ------- Helpers -------
    function setStatus(msg, type) {
        if (typeof type === "undefined") type = "info";
        otpStatus.textContent = msg;
        otpStatus.dataset.type = type;
        otpStatus.classList.add("is-visible");
    }
    function clearStatus() {
        otpStatus.classList.remove("is-visible");
        otpStatus.textContent = "";
    }

    function isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    function generateOTP() {
        // Cryptographically secure 6-digit code
        const buf = new Uint32Array(1);
        crypto.getRandomValues(buf);
        return (buf[0] % 1000000).toString().padStart(6, "0");
    }

    async function sha256(str) {
        const buf = await crypto.subtle.digest(
            "SHA-256",
            new TextEncoder().encode(str)
        );
        const arr = Array.from(new Uint8Array(buf));
        let out = "";
        for (let i = 0; i < arr.length; i++) {
            out += arr[i].toString(16).padStart(2, "0");
        }
        return out;
    }

    function startCountdown() {
        stopCountdown();
        countdownTimer = setInterval(function(){
            const remaining = otpExpiry - Date.now();
            if (remaining <= 0) {
                stopCountdown();
                otpCountdown.textContent = "0:00";
                setStatus("OTP expired. Click Resend to get a new code.", "error");
                otpHash = null;
                return;
            }
            const m = Math.floor(remaining / 60000);
            const s = Math.floor((remaining % 60000) / 1000);
            otpCountdown.textContent = m + ":" + s.toString().padStart(2, "0");
        }, 1000);
    }

    function stopCountdown() {
        if (countdownTimer) clearInterval(countdownTimer);
        countdownTimer = null;
    }

    function lockCreateButton() {
        createBtn.disabled = true;
        createBtn.textContent = "Verify Email to Continue";
        tokenInput.value = "";
    }

    function unlockCreateButton(token) {
        createBtn.disabled = false;
        createBtn.textContent = "Create Account";
        tokenInput.value = token;
    }

    // ------- Send OTP -------
    async function sendOtp() {
        const email = emailInput.value.trim();

        if (!isValidEmail(email)) {
            setStatus("Please enter a valid email address.", "error");
            return;
        }

        const now = Date.now();
        if (now - lastSentAt < RESEND_COOLDOWN_MS) {
            const wait = Math.ceil((RESEND_COOLDOWN_MS - (now - lastSentAt)) / 1000);
            setStatus("Please wait " + wait + "s before requesting again.", "error");
            return;
        }

        const otp = generateOTP();
        otpHash = await sha256(otp + PUBLIC_KEY);
        otpExpiry = now + OTP_TTL_MS;
        attemptsLeft = MAX_ATTEMPTS;
        lastSentAt = now;

        const originalText = sendOtpBtn.textContent;
        sendOtpBtn.disabled = true;
        sendOtpBtn.textContent = "Sending...";

        try {
            await emailjs.send(SERVICE_ID, TEMPLATE_ID, {
                to_email: email,
                passcode: otp,
                time: new Date(otpExpiry).toLocaleTimeString(),
            });

            setStatus("OTP sent to " + email + ". Check your inbox.", "success");
            sendOtpBtn.textContent = "Sent";

            // Reveal OTP step
            otpStep.classList.add("is-visible");
            otpInput.value = "";
            otpInput.focus();
            startCountdown();

            // Lock the submit button until OTP is verified
            lockCreateButton();

        } catch (err) {
            console.error("EmailJS error:", err);
            setStatus("Failed to send OTP. Please try again.", "error");
            sendOtpBtn.textContent = originalText;
        }

        // Re-enable send button after a short delay so label can update
        setTimeout(function(){ sendOtpBtn.disabled = false; }, 1500);
    }

    // ------- Verify OTP -------
    async function verifyOtp() {
        const entered = otpInput.value.trim();

        if (!entered) {
            setStatus("Please enter the OTP.", "error");
            return;
        }
        if (!otpHash) {
            setStatus("Please request an OTP first.", "error");
            return;
        }
        if (Date.now() > otpExpiry) {
            setStatus("OTP expired. Please request a new one.", "error");
            otpHash = null;
            stopCountdown();
            return;
        }
        if (attemptsLeft <= 0) {
            setStatus("Too many attempts. Please request a new OTP.", "error");
            otpHash = null;
            stopCountdown();
            return;
        }

        const enteredHash = await sha256(entered + PUBLIC_KEY);

        if (enteredHash === otpHash) {
            setStatus("Email verified! Now create your password.", "success");
            otpHash = null;
            stopCountdown();
            otpInput.disabled = true;
            resendOtpBtn.disabled = true;

            // Generate a verification token the server can match.
            const token = (crypto.randomUUID && crypto.randomUUID())
                || (Date.now().toString(36) + Math.random().toString(36).slice(2));

            // Tell the server this email was verified so PHP will
            // accept the registration submission.
            try {
                await fetch("otp_mark_verified.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({ email: emailInput.value.trim(), token: token })
                });
            } catch (_) { /* server endpoint optional */ }

            // Unlock the create-account button
            unlockCreateButton(token);
            otpInput.blur();

        } else {
            attemptsLeft--;
            setStatus("Incorrect OTP. " + attemptsLeft + " attempt(s) left.", "error");
            otpInput.value = "";
            otpInput.focus();
        }
    }

    // ------- Wire up events -------
    if (sendOtpBtn)   sendOtpBtn.addEventListener("click", sendOtp);
    if (resendOtpBtn) resendOtpBtn.addEventListener("click", sendOtp);
    if (otpInput)     otpInput.addEventListener("keyup", function(e){
        if (e.key === "Enter") verifyOtp();
    });

    // ------- Auto-verify when 6 digits entered -------
    if (otpInput) otpInput.addEventListener("input", function(){
        if (otpInput.value.length === 6) verifyOtp();
    });

    // ------- Reset state when email changes -------
    if (emailInput) emailInput.addEventListener("input", function(){
        otpStep.classList.remove("is-visible");
        clearStatus();
        stopCountdown();
        otpHash = null;
        lockCreateButton();
        sendOtpBtn.textContent = "Verify My Email";
    });
})();

</script>

</body>
</html>
