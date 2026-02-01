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
                <div class="logo-box">D</div>
                <div class="logo-text">DOMEA</div>
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
            <div class="dropdown-container">
                <a href="CATEGORIES.php" class="menu-btn">☰ CATEGORIES</a>
                <div class="dropdown-content">
                    
                </div>
            </div>
            
            <a href="HOME.php" class="<?php echo ($current_page == 'home') ? 'active' : ''; ?>">HOME</a>
            
            <a href="FURNITURE.php" class="<?php echo ($current_page == 'furniture') ? 'active' : ''; ?>">FURNITURE</a>
            
            <a href="CUSTOM-MADE.php" class="<?php echo ($current_page == 'custom') ? 'active' : ''; ?>">CUSTOM MADE</a>
            
            <a href="VIRTUAL SHOWROOM.php" class="<?php echo ($current_page == 'virtual') ? 'active' : ''; ?>">VIRTUAL SHOWROOM</a>
            
            <a href="REVIEWS.php" class="<?php echo ($current_page == 'reviews') ? 'active' : ''; ?>">REVIEWS</a>
        </div>
    </nav>
    <style>
        .dropdown-container {
            position: relative;
            display: inline-block;
            height: 100%;
        }

        .dropdown-container .menu-btn {
            height: 100%;
            display: flex;
            align-items: center;
            cursor: pointer;
        }

        .dropdown-content {
            display: none; 
            position: absolute;
            background-color: #ffffff;
            min-width: 200px;
            box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
            z-index: 1000;
            top: 50px;
            left: 0;
            border-top: 3px solid #333;
        }

        .dropdown-container:hover .dropdown-content {
            display: block;
            animation: fadeIn 0.3s ease;
        }

        .dropdown-content a {
            color: #333 !important;
            padding: 12px 20px;
            text-decoration: none;
            display: block;
            font-size: 14px;
            font-weight: 500;
            border-bottom: 1px solid #f0f0f0;
            transition: all 0.2s ease;
            text-align: left;
        }

        .dropdown-content a:hover {
            background-color: #f8f8f8;
            color: #5eb4a1 !important;
            transform: translateX(5px) !important;
            padding-left: 25px;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</header>