<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<header class="navbar">
    <div class="top-nav">
        <div class="container flex-between">
            <div class="top-left">
                <a href="STORE-LOCATOR.php">STORE LOCATOR</a>
                <span class="divider"></span>
                <a href="ABOUT-US.php">ABOUT US</a>
            </div>
            <div class="top-right">
                <a href="FAQ.php">FAQ</a>
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
                    <button class="search-icon"><img src="img/Search.png" alt="🔍" style="width:30px; height:30px;"></button>
                </div>
            </div>
            <div class="user-info">
                <div class="login-reg">
                    <?php 
                    if (isset($_SESSION['user_id'])): 
                    ?>
                        <a href="USER-DASHBOARD.php"><span style="color: #5eb4a1; margin-right: 10px;">Hello, <?php echo $_SESSION['username']; ?></span></a>
                        <a href="logout.php"> logout</a>
                    <?php else: ?>
                        <a href="LOGIN-REGISTER.php">LOGIN / REGISTER</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <nav class="category-bar">
        <div class="container nav-links">
            <a href="CATEGORIES.php" class="menu-btn">☰ CATEGORIES</a>
            
            <a href="HOME.php" class="<?php echo ($current_page == 'home') ? 'active' : ''; ?>">HOME</a>
            
            <a href="FURNITURE.php" class="<?php echo ($current_page == 'furniture') ? 'active' : ''; ?>">FURNITURE</a>
            
            <a href="CUSTOM-MADE.php" class="<?php echo ($current_page == 'custom') ? 'active' : ''; ?>">CUSTOM MADE</a>
            
            <a href="VIRTUAL SHOWROOM.php" class="<?php echo ($current_page == 'virtual') ? 'active' : ''; ?>">VIRTUAL SHOWROOM</a>
            
            <a href="REVIEWS.php" class="<?php echo ($current_page == 'reviews') ? 'active' : ''; ?>">REVIEWS</a>
        </div>
    </nav>
</header>