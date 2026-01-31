<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Furniture Direct</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <header class="navbar">
        <div class="top-nav">
            <div class="container flex-between">
                <div class="top-left">
                    <a href="#">STORE LOCATOR</a>
                    <span class="divider"></span> <a href="#">CONTACT US</a>
                </div>
                <div class="top-right">
                    <a href="#">FAQ</a>
                </div>
            </div>
        </div>

        <div class="main-header">
            <div class="container flex-between">
                <div class="logo">
                    <div class="logo-box">F</div>
                    <div class="logo-text">FURNITURE<span>direct</span></div>
                </div>
                
                <div class="search-section">
                    <div class="search-box">
                        <input type="text" placeholder="Try: Sofa Bed">
                        <button class="search-icon">🔍</button>
                    </div>
                </div>

                <div class="user-info">
                    <div class="login-reg"><a href="#">LOGIN / REGISTER</a></div>
                    <div class="cart-box">
                        <span class="cart-icon">🛒</span>
                        <span class="cart-amount">RM 0.00</span>
                    </div>
                </div>
            </div>
        </div>

        <nav class="category-bar">
            <div class="container nav-links">
                <a href="#" class="menu-btn">☰ CATEGORIES</a>
                <a href="HOME.php">HOME</a>
                <a href="FURNITURE.php" style="color: black;">FURNITURE</a>
                <a href="#">CUSTOM MADE</a>
                <a href="#">BED</a>
                <a href="#">AIRBNB FURNITURE</a>
                <a href="#">VIRTUAL SHOWROOM</a>
                <a href="#">REVIEWS</a>
            </div>
        </nav>
    </header>

    <div class="page-content">
        <div class="dummy-box">
            <p>Scroll down to see the folding animation...</p>
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