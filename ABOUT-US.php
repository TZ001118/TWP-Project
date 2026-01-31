<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ABOUT US</title>
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