<?php $current_page = 'home'; ?> 
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// if (!isset($_SESSION['user_id'])) {
//     header("Location: login.php");
//     exit();
// }

if ($_SESSION['role'] === 'admin') {
    header("Location: admin_dashboard.php");
    exit();
} else {
    // header("Location: index.php"); 
    // exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <?php include 'navbar.php'; ?> <div class="page-content">
        <div class="dummy-box">
        </div>
    </div>

    <script>
        window.onscroll = function() {
            const nav = document.querySelector('.navbar');
            if (window.scrollY > 20) {
                nav.classList.add('collapsed');
            } else {
                nav.classList.remove('collapsed');
            }
        };
    </script>
</body>
</html>