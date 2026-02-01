<?php 
include 'db_conn.php';
$current_page = 'furniture';

$max_price_sql = "SELECT MAX(price) AS max_p FROM products";
$max_res = $conn->query($max_price_sql);
$row_max = $max_res->fetch_assoc();
$absolute_max = $row_max['max_p'] ? ceil($row_max['max_p']) : 5000;

$cat_filter = isset($_GET['cat']) ? $_GET['cat'] : 'all';
$price_filter = isset($_GET['max_p']) ? $_GET['max_p'] : $absolute_max;
$sort_filter = isset($_GET['sort']) ? $_GET['sort'] : 'latest';

$sql = "SELECT p.*, c.category_name FROM products p 
        LEFT JOIN categories c ON p.category_id = c.category_id 
        WHERE p.price <= $price_filter";

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
</head>
<body>

    <?php include 'navbar.php'; ?>

    <div class="furniture-layout">
        <aside class="filter-sidebar">
            <form action="FURNITURE.php" method="GET">
                <input type="hidden" name="cat" value="<?php echo $cat_filter; ?>">
                
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
                        <li><a href="FURNITURE.php?cat=all&max_p=<?php echo $price_filter; ?>">● All Products</a></li>
                        <li><a href="FURNITURE.php?cat=Bedroom&max_p=<?php echo $price_filter; ?>">○ Bedroom</a></li>
                        <li><a href="FURNITURE.php?cat=Living Room&max_p=<?php echo $price_filter; ?>">○ Living Room</a></li>
                        <li><a href="FURNITURE.php?cat=Study Room&max_p=<?php echo $price_filter; ?>">○ Study Room</a></li>
                        <li><a href="FURNITURE.php?cat=Home Living&max_p=<?php echo $price_filter; ?>">○ Home Living</a></li>
                        <li><a href="FURNITURE.php?cat=Office&max_p=<?php echo $price_filter; ?>">○ Office</a></li>
                    </ul>
                </div>
            </form>
        </aside>

        <main class="main-content">
            <div class="content-header">
                <h2><?php echo ($cat_filter == 'all') ? 'All Products' : $cat_filter; ?></h2>
                <div class="sort-box">
                    <label>Sort by:</label>
                    <select onchange="location.href='FURNITURE.php?cat=<?php echo $cat_filter; ?>&max_p=<?php echo $price_filter; ?>&sort='+this.value">
                        <option value="latest" <?php echo ($sort_filter=='latest')?'selected':''; ?>>Latest Arrival</option>
                        <option value="low" <?php echo ($sort_filter=='low')?'selected':''; ?>>Price: Low to High</option>
                        <option value="high" <?php echo ($sort_filter=='high')?'selected':''; ?>>Price: High to Low</option>
                    </select>
                </div>
            </div>

            <div class="product-grid">
                <?php if ($results->num_rows > 0): ?>
                    <?php while($row = $results->fetch_assoc()): ?>
                        <div class="product-item">
                            <img src="<?php echo $row['image_url']; ?>" alt="Furniture">
                            <div class="product-name"><?php echo $row['product_name']; ?></div>
                            <div class="product-price">RM <?php echo number_format($row['price'], 2); ?></div>
                            <button class="btn-filter" style="margin-top:15px; background:#333;">ADD TO CART</button>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="padding: 50px; text-align: center; color: #999;">No furniture found matching your criteria.</p>
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
    </script>
</body>
</html>