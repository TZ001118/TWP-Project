<?php
session_start();
include 'db_conn.php';

$category_name = isset($_GET['category']) ? $_GET['category'] : 'Bedroom';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'latest';

$order_sql = "p.created_at DESC";

switch ($sort) {
    case 'price_low':
        $order_sql = "p.price ASC";
        break;
    case 'price_high':
        $order_sql = "p.price DESC";
        break;
    case 'latest':
    default:
        $order_sql = "p.created_at DESC";
        break;
}

$safe_cat_name = $conn->real_escape_string($category_name);

$sql = "SELECT p.*, c.category_name 
        FROM products p 
        JOIN categories c ON p.category_id = c.category_id 
        WHERE c.category_name = '$safe_cat_name' 
        AND p.status = 'Active' 
        ORDER BY $order_sql";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($category_name); ?> | Furniture Direct</title>
    
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="category.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>

    <?php include 'navbar.php'; ?>

    <div class="page-content">
        
        <div class="category-header-bar">
            <div class="container flex-between">
                
                <div class="header-left">
                    <a href="javascript:history.back()" class="back-link">
                        <i class="fa-solid fa-chevron-left"></i>
                    </a>
                    <h1 class="cat-title"><?php echo htmlspecialchars($category_name); ?></h1>
                </div>

                <div class="header-right">
                    <form action="" method="GET" id="sortForm">
                        <input type="hidden" name="category" value="<?php echo htmlspecialchars($category_name); ?>">
                        
                        <label for="sort" class="sort-label">Sort by:</label>
                        <select name="sort" id="sort" class="sort-select" onchange="document.getElementById('sortForm').submit()">
                            <option value="latest" <?php if($sort == 'latest') echo 'selected'; ?>>Latest Arrival</option>
                            <option value="price_low" <?php if($sort == 'price_low') echo 'selected'; ?>>Price: Low to High</option>
                            <option value="price_high" <?php if($sort == 'price_high') echo 'selected'; ?>>Price: High to Low</option>
                        </select>
                    </form>
                </div>

            </div>
        </div>

        <div class="container main-container">
            <div class="products-grid">
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        
                        <div class="product-card">
                            <a href="PRODUCT_DETAILS.php?id=<?php echo $row['product_id']; ?>" class="img-wrapper-link">
                                <div class="img-wrapper">
                                    <img src="img/<?php echo $row['product_image']; ?>" alt="<?php echo $row['product_name']; ?>">
                                </div>
                            </a>
                            
                            <div class="card-info">
                                <div>
                                    <h3 class="prod-name"><?php echo $row['product_name']; ?></h3>
                                    
                                    <?php if($row['compare_at_price'] > 0): ?>
                                        <div class="compare-price">
                                            RM <?php echo number_format($row['compare_at_price'], 2); ?>
                                        </div>
                                    <?php endif; ?>

                                    <p class="prod-price">RM <?php echo number_format($row['price'], 2); ?></p>
                                </div>
                                
                                <form action="add_to_cart.php" method="POST">
                                    <input type="hidden" name="product_id" value="<?php echo $row['product_id']; ?>">
                                    <input type="hidden" name="quantity" value="1">
                                    <button type="submit" class="add-btn">ADD TO CART</button>
                                </form>
                            </div>
                        </div>

                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fa-regular fa-folder-open"></i>
                        <h3>No products found in this category yet.</h3>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>

    <script>
        window.onscroll = function() {
            const nav = document.querySelector('.navbar');
            if (nav) {
                if (window.scrollY > 20) nav.classList.add('collapsed');
                else nav.classList.remove('collapsed');
            }
        };
    </script>
</body>
</html>