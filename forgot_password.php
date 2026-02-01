<?php
include 'db_conn.php';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $identifier = mysqli_real_escape_string($conn, $_POST['identifier']);
    $new_pass = $_POST['new_password'];

    $check_sql = "SELECT * FROM users WHERE email = '$identifier' OR username = '$identifier'";
    $result = $conn->query($check_sql);

    if ($result->num_rows > 0) {
        $new_pass_hash = password_hash($new_pass, PASSWORD_DEFAULT);
        $update_sql = "UPDATE users SET password='$new_pass_hash' WHERE email='$identifier' OR username='$identifier'";
        
        if ($conn->query($update_sql) === TRUE) {
            echo "<script>alert('Success! Password changed.'); window.location='LOGIN-REGISTER.php';</script>";
        } else {
            echo "<script>alert('Error: " . $conn->error . "');</script>";
        }
    } else {
        echo "<script>alert('User not found!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lost Password</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="forgot_password-style.css">
</head>
<body>
    <?php include 'navbar.php'; ?>
    
    <div class="lp-header">
        <h1>Lost Password</h1>
    </div>

    <div class="lp-container">
        <div class="lp-box">
            
            <p class="lp-text">
                Lost your password? Please enter your username or email address. 
                <br>Enter your <strong>new password</strong> below to reset it immediately.
            </p>
            
            <form action="" method="POST" onsubmit="return validateReset()">
                
                <div class="form-group">
                    <label>Username or email <span class="required">*</span></label>
                    <input type="text" name="identifier" class="lp-input" required>
                </div>

                <div class="form-group">
                    <label>New password <span class="required">*</span></label>
                    
                    <input type="password" name="new_password" id="new_pass" class="lp-input" 
                           onkeyup="checkPassword()" 
                           onfocus="showHint()" 
                           onblur="hideHint()" 
                           required>
                           
                    <i class="fa fa-eye toggle-password" onclick="togglePwd('new_pass', this)"></i>

                    <div id="hint_text" class="password-hint">
                        Hint: The password should be at least twelve characters long. To make it stronger, use upper and lower case letters, numbers, and symbols like ! " ? $ % ^ & ).
                    </div>
                    
                    <div id="password-strength-box" class="password-feedback"></div>
                </div>

                <div class="form-group">
                    <label>Confirm new password <span class="required">*</span></label>
                    <input type="password" id="confirm_pass" class="lp-input" required>
                    <i class="fa fa-eye toggle-password" onclick="togglePwd('confirm_pass', this)"></i>
                </div>

                <button type="submit" class="lp-btn" id="reset_btn">RESET PASSWORD</button>

            </form>
        </div>
    </div>

    <script>
    function showHint() {
        document.getElementById('hint_text').style.display = 'block';
    }
    
    function hideHint() {
        document.getElementById('hint_text').style.display = 'none';
    }
    function togglePwd(inputId, icon) {
        const input = document.getElementById(inputId);
        if (input.type === "password") {
            input.type = "text";
            icon.classList.remove("fa-eye");
            icon.classList.add("fa-eye-slash");
        } else {
            input.type = "password";
            icon.classList.remove("fa-eye-slash");
            icon.classList.add("fa-eye");
        }
    }

    function checkPassword() {
        const password = document.getElementById('new_pass').value;
        const feedbackBox = document.getElementById('password-strength-box');
        const submitBtn = document.getElementById('reset_btn');

        if (password.length === 0) {
            feedbackBox.style.display = 'none';
            return;
        }

        const minLength = 12;
        const hasUpper = /[A-Z]/.test(password);
        const hasLower = /[a-z]/.test(password);
        const hasNumber = /[0-9]/.test(password);
        const hasSymbol = /[@!"?$%\^&)]/.test(password);

        const upperCount = (password.match(/[A-Z]/g) || []).length;
        const symbolCount = (password.match(/[@!"?$%\^&)]/g) || []).length;

        let status = "Weak";

        if (password.length < minLength || !hasUpper || !hasLower || !hasNumber || !hasSymbol) {
            status = "Weak";
        } else if (upperCount >= 2 && symbolCount >= 2) {
            status = "Strong";
        } else {
            status = "Medium";
        }

        if (status === "Weak") {
            feedbackBox.innerText = "Very weak - Please enter a stronger password";
            feedbackBox.className = "password-feedback status-weak";
            submitBtn.disabled = true;
            submitBtn.style.opacity = "0.5";
        } else {
            feedbackBox.innerText = status;
            feedbackBox.className = "password-feedback status-good";
            submitBtn.disabled = false;
            submitBtn.style.opacity = "1";
        }
    }

    function validateReset() {
        var p1 = document.getElementById("new_pass").value;
        var p2 = document.getElementById("confirm_pass").value;
        
        if(p1.length < 12) {
            alert("Password must be at least 12 characters long.");
            return false;
        }
        if (p1 !== p2) {
            alert("New passwords do not match!");
            return false;
        }
        return true;
    }
    </script>
</body>
</html>