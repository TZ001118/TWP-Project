<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login / Register</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="LOGIN-REGISTER-style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="page-header">
        <h1>My Account</h1>
    </div>

    <div class="main-content">
        
        <div class="auth-box">
            
            <h2>Login</h2>
            <form action="login_process.php" method="POST">
                <div class="form-group">
                    <label>Username or email address <span class="required">*</span></label>
                    <input type="text" name="identifier" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Password <span class="required">*</span></label>
                    <input type="password" name="password" id="login_pass" class="form-control" required>
                    <i class="fa fa-eye toggle-password" onclick="togglePwd('login_pass', this)"></i>
                </div>
                <button type="submit" class="btn-teal">Log In</button>
                <div style="margin-top:15px; display:flex; justify-content:flex-end; font-size:13px;">
                    <a href="forgot_password.php" style="color:#99d5c5; text-decoration:none;">Lost your password?</a>
                </div>
            </form>
        </div>

    <div class="vertical-line"></div>
        <div class="auth-box">
            <h2>Register</h2>
            <form action="register_process.php" method="POST" id="regForm">
                <div class="form-group">
                    <label>Email address <span class="required">*</span></label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Password <span class="required">*</span></label>
                    <input type="password" name="password" id="reg_pass" class="form-control" onfocus="showRegHint()" onblur="hideRegHint()" onkeyup="checkPassword()" required>
                    <i class="fa fa-eye toggle-password" onclick="togglePwd('reg_pass', this)"></i>
                    <div id="reg_hint_text" class="password-hint">
                        Hint: The password should be at least twelve characters long. To make it stronger, use upper and lower case letters, numbers, and symbols like ! " ? $ % ^ & ).
                    </div>
                    <div id="password-strength-box" class="password-feedback"></div>
                </div>
                <p style="font-size:12px; color:#666; line-height:1.6; margin-top:15px;">
                    Your personal data will be used to support your experience throughout this website, to manage access to your account, and for other purposes described in our privacy policy.
                </p>
                <button type="submit" class="btn-teal" id="reg_btn">Register</button>
            </form>
        </div>
    </div>

    <script>

    function showRegHint() {
        document.getElementById('reg_hint_text').style.display = 'block';
    }

    function hideRegHint() {
        document.getElementById('reg_hint_text').style.display = 'none';
    }

    function checkPassword() {
        const password = document.getElementById('reg_pass').value;
        const feedbackBox = document.getElementById('password-strength-box');
        const submitBtn = document.getElementById('reg_btn');

        if (password.length === 0) {
            feedbackBox.style.display = 'none';
            return;
        }
        feedbackBox.style.display = 'block';

        const minLength = 12;
        const hasUpper = /[A-Z]/.test(password);
        const hasLower = /[a-z]/.test(password);
        const hasNumber = /[0-9]/.test(password);
        const hasSymbol = /[@!"?$%\^&)]/.test(password);
        const upperCount = (password.match(/[A-Z]/g) || []).length;
        const symbolCount = (password.match(/[@!"?$%\^&)]/g) || []).length;
        let status = "";
        if (password.length < minLength || !hasUpper || !hasLower || !hasNumber || !hasSymbol) {
            status = "Weak";
        } 
        else if (upperCount >= 2 && symbolCount >= 2) {
            status = "Strong";
        } 
        else {
            status = "Medium";
        }

        if (status === "Weak") {
            feedbackBox.innerText = "Very weak - Please enter a stronger password";
            feedbackBox.className = "password-feedback status-weak";
            submitBtn.disabled = true; 
            submitBtn.style.opacity = "0.5";
        } 
        else if (status === "Medium") {
            feedbackBox.innerText = "Medium";
            feedbackBox.className = "password-feedback status-good";
            submitBtn.disabled = false; 
            submitBtn.style.opacity = "1";
        } 
        else if (status === "Strong") {
            feedbackBox.innerText = "Strong";
            feedbackBox.className = "password-feedback status-good";
            submitBtn.disabled = false; 
            submitBtn.style.opacity = "1";
        }
    }

    function togglePwd(inputId, icon) {
        const input = document.getElementById(inputId);
        
        if (input.type === "password") {
            input.type = "text";
            icon.classList.remove("fa-eye");
            icon.classList.add("fa-eye-slash"); 
            } 
        else {
            input.type = "password";
            icon.classList.remove("fa-eye-slash");
            icon.classList.add("fa-eye"); 
            }
        }
    
    </script>
</body>
</html>