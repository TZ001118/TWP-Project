<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 确保引入了数据库连接，路径根据你的文件结构调整
require_once 'db_conn.php'; 

$nav_cart_count = 0;
$nav_cart_total = 0;

// 现在只需检查 session 即可，因为 $conn 已经在上面 require 了
if (isset($_SESSION['user_id'])) {
    $uid = $_SESSION['user_id'];
    
    // 使用预处理语句更安全
    $sql_nav = "SELECT cart.quantity, products.price 
                FROM cart 
                JOIN products ON cart.product_id = products.product_id 
                WHERE cart.user_id = $uid";
    
    $result_nav = $conn->query($sql_nav);
    
    if ($result_nav && $result_nav->num_rows > 0) {
        while ($row = $result_nav->fetch_assoc()) {
            $nav_cart_count += $row['quantity'];
            $nav_cart_total += ($row['quantity'] * $row['price']);
        }
    }
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
                <div class="logo-text">Domea</div>
            </div>
            
            <div class="search-section">
                <form action="FURNITURE.php" method="GET" class="search-box">
                    <input type="text" name="search" placeholder="Try: Sofa Bed" required 
                           value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                    <button type="submit" class="search-icon">
                        <img src="img/Search.png" alt="🔍" style="width:30px; height:30px;">
                    </button>
                </form>
            </div>

            <div class="user-info" style="display: flex; align-items: center; gap: 20px;">
                
                <div class="login-reg" style="display: flex; align-items: center;">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="USER-DASHBOARD.php" style="text-decoration: none; color: #333; font-weight: 600;">MY PROFILE</a>
                        <span style="margin: 0 10px; color: #ccc;">|</span>
                        <a href="logout.php" style="color: #e74c3c; text-decoration: none; font-weight: 600;">LOGOUT</a>
                    <?php else: ?>
                        <a href="LOGIN-REGISTER.php">LOGIN / REGISTER</a>
                    <?php endif; ?>
                </div>

                <a href="CART.php" class="cart-box" style="text-decoration: none; display: flex; align-items: center; color: #333;">
                    <div style="position: relative;">
                        <img src="img/cart-icon.png" alt="Cart" style="width: 25px; height: 25px;"> 
                        <span style="position: absolute; top: -8px; right: -8px; background: #20c997; color: white; border-radius: 50%; padding: 2px 6px; font-size: 10px; font-weight: bold;">
                            <?php echo $nav_cart_count; ?>
                        </span>
                    </div>
                    <span class="cart-amount" style="margin-left: 8px; font-weight: 500;">
                        RM <?php echo number_format($nav_cart_total, 2); ?>
                    </span>
                </a>

            </div>
        </div>
    </div>

    <nav class="category-bar">
        <div class="container nav-links">
            <div class="dropdown-container">
                <a href="" class="menu-btn">☰ CATEGORIES</a>
                <div class="dropdown-content">
                    <a href="CATEGORY_PRODUCTS.php?category=Bedroom">Bedroom</a>
                    <a href="CATEGORY_PRODUCTS.php?category=Living Room">Living Room</a>
                    <a href="CATEGORY_PRODUCTS.php?category=Dining Room">Dining Room</a>
                    <a href="CATEGORY_PRODUCTS.php?category=Study Room">Study Room</a>
                    <a href="CATEGORY_PRODUCTS.php?category=Home Living">Home Living</a>
                    <a href="CATEGORY_PRODUCTS.php?category=Office">Office</a>
                </div>
            </div>
            
            <a href="HOME.php" class="<?php echo (isset($current_page) && $current_page == 'home') ? 'active' : ''; ?>">HOME</a>
            
            <a href="FURNITURE.php" class="<?php echo (isset($current_page) && $current_page == 'furniture') ? 'active' : ''; ?>">FURNITURE</a>
            
            <a href="CUSTOM-MADE.php" class="<?php echo (isset($current_page) && $current_page == 'custom') ? 'active' : ''; ?>">CUSTOM MADE</a>
            
            <a href="VIRTUAL SHOWROOM.php" class="<?php echo (isset($current_page) && $current_page == 'virtual') ? 'active' : ''; ?>">VIRTUAL SHOWROOM</a>
            
            <a href="REVIEWS.php" class="<?php echo (isset($current_page) && $current_page == 'reviews') ? 'active' : ''; ?>">REVIEWS</a>
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
        }
    });

    <?php 
    if (isset($_SESSION['swal'])) {
        $type = $_SESSION['swal']['type'];
        $title = $_SESSION['swal']['title'];
        $text = $_SESSION['swal']['text'];
        echo "Toast.fire({ icon: '$type', title: '$title', text: '$text' });";
        unset($_SESSION['swal']); 
    }
    
    if (isset($_GET['success']) && $_GET['success'] == 1) {
        echo "Toast.fire({ icon: 'success', title: 'Success!', text: 'Profile updated successfully!' });";
        echo "window.history.replaceState(null, null, window.location.pathname);";
    }
    ?>
</script>