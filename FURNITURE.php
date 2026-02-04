<?php 
session_start();
include 'db_conn.php';
$current_page = 'furniture';

// 获取最高价格用于筛选条
$max_price_sql = "SELECT MAX(price) AS max_p FROM products";
$max_res = $conn->query($max_price_sql);
$row_max = $max_res->fetch_assoc();
$absolute_max = $row_max['max_p'] ? ceil($row_max['max_p']) : 5000;

// 获取各类参数
$cat_filter = isset($_GET['cat']) ? $_GET['cat'] : 'all';
$price_filter = isset($_GET['max_p']) ? $_GET['max_p'] : $absolute_max;
$sort_filter = isset($_GET['sort']) ? $_GET['sort'] : 'latest';
$search_query = isset($_GET['search']) ? trim($_GET['search']) : ''; // ★ 接收搜索词

// 构建查询
$sql = "SELECT p.*, c.category_name FROM products p 
        LEFT JOIN categories c ON p.category_id = c.category_id 
        WHERE p.price <= $price_filter AND p.status = 'Active'";

// ★★★ 搜索逻辑 ★★★
if (!empty($search_query)) {
    // 防止 SQL 注入
    $safe_search = $conn->real_escape_string($search_query);
    $sql .= " AND p.product_name LIKE '%$safe_search%'";
}

if ($cat_filter !== 'all') {
    $sql .= " AND c.category_name = '$cat_filter'";
}

switch ($sort_filter) {
    case 'low': $sql .= " ORDER BY p.price ASC"; break;
    case 'high': $sql .= " ORDER BY p.price DESC"; break;
    default: $sql .= " ORDER BY p.product_id DESC";
}

$results = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Furniture - Furniture Direct</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="furniture_style.css">
    <style>
        .cat-list a {
            color: #777; 
            text-decoration: none;
            display: block;
            padding: 5px 0;
            transition: color 0.3s;
        }
        .cat-list a:hover {
            color: #333; 
        }
        .cat-list a.active {
            color: #000 !important; 
            font-weight: bold;
            border-left: 3px solid #5eb4a1; 
            padding-left: 10px;
        }
    </style>
</head>
<body>

    <?php include 'navbar.php'; ?>

    <div class="furniture-layout">
        <aside class="filter-sidebar">
            <form action="FURNITURE.php" method="GET">
                <input type="hidden" name="cat" value="<?php echo htmlspecialchars($cat_filter); ?>">
                <?php if(!empty($search_query)): ?>
                    <input type="hidden" name="search" value="<?php echo htmlspecialchars($search_query); ?>">
                <?php endif; ?>
                
                <div class="filter-section">
                    <h3>Filter by Price</h3>
                    <input type="range" name="max_p" class="range-slider" 
                           min="0" max="<?php echo $absolute_max; ?>" 
                           value="<?php echo $price_filter; ?>" 
                           oninput="document.getElementById('current-val').innerText = this.value">
                    <div class="price-labels">
                        <span>RM 0</span>
                        <span>RM <?php echo $absolute_max; ?></span>
                    </div>
                    <p style="margin-top:10px; font-size:14px;">
                        Max: <strong>RM <span id="current-val"><?php echo $price_filter; ?></span></strong>
                    </p>
                    <button type="submit" class="btn-filter">APPLY FILTER</button>
                </div>

                <div class="filter-section">
                    <h3>Categories</h3>
                    <ul class="cat-list">
                        <?php 
                            $search_param = !empty($search_query) ? "&search=".urlencode($search_query) : ""; 
                        ?>
                        <li>
                            <a href="FURNITURE.php?cat=all&max_p=<?php echo $price_filter . $search_param; ?>" 
                               class="<?php echo ($cat_filter == 'all') ? 'active' : ''; ?>">
                               All Products
                            </a>
                        </li>
                        <li>
                            <a href="FURNITURE.php?cat=Bedroom&max_p=<?php echo $price_filter . $search_param; ?>" 
                               class="<?php echo ($cat_filter == 'Bedroom') ? 'active' : ''; ?>">
                               Bedroom
                            </a>
                        </li>
                        <li>
                            <a href="FURNITURE.php?cat=Living Room&max_p=<?php echo $price_filter . $search_param; ?>" 
                               class="<?php echo ($cat_filter == 'Living Room') ? 'active' : ''; ?>">
                               Living Room
                            </a>
                        </li>
                        <li>
                            <a href="FURNITURE.php?cat=Dining Room&max_p=<?php echo $price_filter . $search_param; ?>" 
                               class="<?php echo ($cat_filter == 'Dining Room') ? 'active' : ''; ?>">
                               Dining Room
                            </a>
                        </li>
                        <li>
                            <a href="FURNITURE.php?cat=Study Room&max_p=<?php echo $price_filter . $search_param; ?>" 
                               class="<?php echo ($cat_filter == 'Study Room') ? 'active' : ''; ?>">
                               Study Room
                            </a>
                        </li>
                        <li>
                            <a href="FURNITURE.php?cat=Home Living&max_p=<?php echo $price_filter . $search_param; ?>" 
                               class="<?php echo ($cat_filter == 'Home Living') ? 'active' : ''; ?>">
                               Home Living
                            </a>
                        </li>
                        <li>
                            <a href="FURNITURE.php?cat=Office&max_p=<?php echo $price_filter . $search_param; ?>" 
                               class="<?php echo ($cat_filter == 'Office') ? 'active' : ''; ?>">
                               Office
                            </a>
                        </li>
                    </ul>
                </div>
            </form>
        </aside>

        <main class="main-content">
            <div class="content-header">
                <h2>
                    <?php 
                    if (!empty($search_query)) {
                        echo 'Search: "' . htmlspecialchars($search_query) . '"';
                        if ($cat_filter !== 'all') echo ' in ' . htmlspecialchars($cat_filter);
                    } else {
                        echo ($cat_filter == 'all') ? 'All Products' : htmlspecialchars($cat_filter); 
                    }
                    ?>
                </h2>
                
                <div class="sort-box">
                    <label>Sort by:</label>
                    <select onchange="location.href='FURNITURE.php?cat=<?php echo urlencode($cat_filter); ?>&max_p=<?php echo $price_filter . $search_param; ?>&sort='+this.value">
                        <option value="latest" <?php echo ($sort_filter=='latest')?'selected':''; ?>>Latest Arrival</option>
                        <option value="low" <?php echo ($sort_filter=='low')?'selected':''; ?>>Price: Low to High</option>
                        <option value="high" <?php echo ($sort_filter=='high')?'selected':''; ?>>Price: High to Low</option>
                    </select>
                </div>
            </div>

            <div class="product-grid">
                <?php if ($results && $results->num_rows > 0): ?>
                    <?php while($row = $results->fetch_assoc()): ?>
                        <div class="product-item">
                            
                            <a href="PRODUCT_DETAILS.php?id=<?php echo $row['product_id']; ?>&from=furniture" style="text-decoration:none; color:inherit;">
                                <img src="img/<?php echo $row['product_image']; ?>" alt="<?php echo htmlspecialchars($row['product_name']); ?>">
                                <div class="product-name"><?php echo htmlspecialchars($row['product_name']); ?></div>
                            </a>

                            <div class="product-price">RM <?php echo number_format($row['price'], 2); ?></div>
                            
                            <form action="add_to_cart.php" method="POST">
                                <input type="hidden" name="product_id" value="<?php echo $row['product_id']; ?>">
                                <input type="hidden" name="quantity" value="1">
                                <button type="submit" class="btn-filter" style="margin-top:15px; background:#333; width:100%; cursor:pointer;">ADD TO CART</button>
                            </form>

                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div style="padding: 50px; text-align: center; color: #999; grid-column: 1/-1;">
                        <i class="fa-solid fa-magnifying-glass" style="font-size: 40px; margin-bottom: 20px;"></i>
                        <p>No furniture found matching your criteria.</p>
                        <?php if(!empty($search_query)): ?>
                            <p><a href="FURNITURE.php" style="color: #5eb4a1; text-decoration: none;">Clear Search</a></p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </main>
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
    // ★★★ 核心修复：无刷新购物车逻辑 (含登录检查) ★★★
        document.addEventListener('DOMContentLoaded', function() {
            const forms = document.querySelectorAll('form[action="add_to_cart.php"]');

            forms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    e.preventDefault(); // 阻止表单默认提交

                    const formData = new FormData(this);

                    fetch('add_to_cart.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => {
                        // ★ 检查点：如果后端把我们踢到了 LOGIN-REGISTER.php
                        if (response.redirected && response.url.includes('LOGIN-REGISTER.php')) {
                            // 弹窗提示用户需要登录，然后跳转
                            Swal.fire({
                                icon: 'warning',
                                title: 'Please Login',
                                text: 'You need to login to add items to cart.',
                                showConfirmButton: true,
                                confirmButtonText: 'Go to Login',
                                confirmButtonColor: '#333'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    window.location.href = 'LOGIN-REGISTER.php';
                                }
                            });
                            return; // 终止后续操作
                        }

                        // 如果成功添加
                        if (response.ok) {
                            Toast.fire({
                                icon: 'success',
                                title: 'Added to Cart'
                            });

                            // 更新购物车数字
                            const cartBadge = document.querySelector('.cart-box span');
                            if(cartBadge) {
                                let currentCount = parseInt(cartBadge.innerText);
                                if(isNaN(currentCount)) currentCount = 0;
                                cartBadge.innerText = currentCount + 1;
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    });
                });
            });
        });
    </script>
</body>
</html>