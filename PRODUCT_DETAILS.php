<?php
session_start();
include 'db_conn.php';

// 1. 检查是否有 ID
if (!isset($_GET['id'])) {
    header("Location: HOME.php");
    exit();
}

$product_id = intval($_GET['id']); // 安全过滤

// 2. 查询商品主信息
$sql = "SELECT p.*, c.category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.category_id 
        WHERE p.product_id = '$product_id'";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    die("Product not found.");
}

$product = $result->fetch_assoc();

// 3. 查询评分信息
$sql_rating = "SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews 
               FROM reviews 
               WHERE product_id = '$product_id'";
$rating_result = $conn->query($sql_rating);
$rating_data = $rating_result->fetch_assoc();
$avg_rating = round($rating_data['avg_rating'] ?? 0, 1);
$total_reviews = $rating_data['total_reviews'];

// 4. 构建相册数组 (只存有图的)
$gallery_images = [];
if (!empty($product['product_image'])) $gallery_images[] = $product['product_image'];
if (!empty($product['image_gallery_1'])) $gallery_images[] = $product['image_gallery_1'];
if (!empty($product['image_gallery_2'])) $gallery_images[] = $product['image_gallery_2'];
if (!empty($product['image_gallery_3'])) $gallery_images[] = $product['image_gallery_3'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['product_name']); ?> | Furniture Direct</title>
    
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="product_details.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <style>
        /* --- 修正后的相册样式 (直接写在这里确保生效) --- */
        .gallery-section {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        .main-image-box {
            width: 100%;
            background-color: #fff;
            border: 1px solid #eee;
            border-radius: 8px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            aspect-ratio: 1/1; /* 保持正方形或根据需要调整 */
        }
        .main-image-box img {
            width: 100%;
            height: 100%;
            object-fit: contain; /* 保证图片完整显示 */
        }
        .thumbnail-row {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .thumb-box {
            width: 70px;
            height: 70px;
            border: 2px solid #ddd; /* 默认灰色边框 */
            border-radius: 4px;
            overflow: hidden;
            cursor: pointer;
            opacity: 0.7;
            transition: 0.2s;
        }
        .thumb-box:hover, .thumb-box.active {
            border-color: #20c997; /* 选中变绿 */
            opacity: 1;
        }
        .thumb-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
    </style>
</head>
<body>

    <?php include 'navbar.php'; ?>

    <div class="page-content">
        <div class="container main-product-container">
            
            <div class="breadcrumb" style="margin-bottom: 20px; color: #666; font-size: 0.9rem;">
                <a href="HOME.php" style="text-decoration:none; color:#666;">Home</a> &gt; 
                
                <?php 
                if (isset($_GET['from']) && $_GET['from'] == 'furniture') {
                    // 1. 如果是从 Furniture 页面来的
                    echo '<a href="FURNITURE.php" style="text-decoration:none; color:#666;">Furniture</a>';
                
                } elseif (isset($_GET['from']) && $_GET['from'] == 'cart') {
                    // 2. ★ 如果是从 购物车 (Cart) 来的 ★
                    echo '<a href="CART.php" style="text-decoration:none; color:#666;">Shopping Cart</a>';
                
                } else {
                    // 3. 默认：显示分类 (例如 Bedroom / Study Room)
                    echo '<a href="CATEGORY_PRODUCTS.php?category=' . urlencode($product['category_name']) . '" style="text-decoration:none; color:#666;">' . htmlspecialchars($product['category_name']) . '</a>';
                }
                ?>
                
                &gt; 
                <span style="color:#333; font-weight:bold;"><?php echo htmlspecialchars($product['product_name']); ?></span>
            </div>

            <div class="product-detail-wrapper" style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px;">
                
                <div class="gallery-section">
                    <div class="main-image-box">
                        <img id="mainImage" src="img/<?php echo htmlspecialchars($product['product_image']); ?>" alt="Main Product">
                    </div>
                    
                    <?php if (count($gallery_images) > 1): ?>
                        <div class="thumbnail-row">
                            <?php foreach($gallery_images as $img): ?>
                                <div class="thumb-box" onclick="changeImage('img/<?php echo $img; ?>')">
                                    <img src="img/<?php echo $img; ?>" alt="Thumbnail">
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="info-section">
                    <h1 class="product-title" style="font-size: 2rem; margin-bottom: 10px;"><?php echo htmlspecialchars($product['product_name']); ?></h1>
                    
                    <div class="rating-box" style="margin-bottom: 15px; color: #f1c40f;">
                        <div class="stars" style="display:inline-block;">
                            <?php 
                            for($i=1; $i<=5; $i++) {
                                if($i <= $avg_rating) echo '<i class="fa-solid fa-star"></i>';
                                elseif($i - 0.5 <= $avg_rating) echo '<i class="fa-solid fa-star-half-stroke"></i>';
                                else echo '<i class="fa-regular fa-star" style="color:#ccc;"></i>';
                            }
                            ?>
                        </div>
                        <span class="review-count" style="color:#666; font-size:0.9rem;">(<?php echo $total_reviews; ?> reviews)</span>
                    </div>

                    <div class="price-box" style="font-size: 1.5rem; margin-bottom: 20px;">
                        <?php if($product['compare_at_price'] > $product['price']): ?>
                            <span class="old-price" style="text-decoration: line-through; color: #999; font-size: 1.1rem; margin-right: 10px;">RM <?php echo number_format($product['compare_at_price'], 2); ?></span>
                        <?php endif; ?>
                        <span class="current-price" style="color: #20c997; font-weight: bold;">RM <?php echo number_format($product['price'], 2); ?></span>
                    </div>

                    <div class="description-box" style="margin-bottom: 25px; line-height: 1.6; color: #555;">
                        <?php echo nl2br(htmlspecialchars_decode($product['description'])); ?>
                    </div>

                    <div class="meta-info" style="font-size: 0.9rem; color: #666; margin-bottom: 20px;">
                        <?php if(!empty($product['brand'])): ?>
                            <div class="meta-item"><strong>Brand:</strong> <?php echo htmlspecialchars($product['brand']); ?></div>
                        <?php endif; ?>
                        
                        <?php if(!empty($product['sku'])): ?>
                            <div class="meta-item"><strong>SKU:</strong> <?php echo htmlspecialchars($product['sku']); ?></div>
                        <?php endif; ?>

                        <div class="meta-item" style="margin-top: 5px;">
                            <strong>Availability:</strong> 
                            <?php if($product['stock_quantity'] > 0): ?>
                                <span style="color: #20c997;"><i class="fa-solid fa-check"></i> In Stock (<?php echo $product['stock_quantity']; ?> left)</span>
                            <?php else: ?>
                                <span style="color: #e74c3c;"><i class="fa-solid fa-xmark"></i> Out of Stock</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <hr class="divider" style="border: 0; border-top: 1px solid #eee; margin: 20px 0;">

                    <div class="variants-box" style="margin-bottom: 20px;">
                        <?php if(!empty($product['variants_color'])): ?>
                            <div class="variant-row" style="margin-bottom: 5px;">
                                <strong>Color:</strong> <?php echo htmlspecialchars($product['variants_color']); ?>
                            </div>
                        <?php endif; ?>

                        <?php if(!empty($product['variants_size'])): ?>
                            <div class="variant-row" style="margin-bottom: 5px;">
                                <strong>Size:</strong> <?php echo htmlspecialchars($product['variants_size']); ?>
                            </div>
                        <?php endif; ?>

                        <?php if(!empty($product['variants_material'])): ?>
                            <div class="variant-row" style="margin-bottom: 5px;">
                                <strong>Material:</strong> <?php echo htmlspecialchars($product['variants_material']); ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if(!empty($product['dimensions'])): ?>
                            <div class="variant-row" style="margin-bottom: 5px;">
                                <strong>Dimensions:</strong> <?php echo htmlspecialchars($product['dimensions']); ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <form action="add_to_cart.php" method="POST" class="add-cart-form" style="display: flex; gap: 15px; align-items: center;">
                        <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                        
                        <div class="qty-wrapper" style="display: flex; border: 1px solid #ddd; border-radius: 4px;">
                            <button type="button" onclick="updateQty(-1)" style="border:none; background:white; padding: 10px 15px; cursor:pointer;">-</button>
                            <input type="number" name="quantity" id="qtyInput" value="1" min="1" max="<?php echo max(1, $product['stock_quantity']); ?>" style="width: 50px; text-align: center; border: none; border-left: 1px solid #ddd; border-right: 1px solid #ddd;" readonly>
                            <button type="button" onclick="updateQty(1)" style="border:none; background:white; padding: 10px 15px; cursor:pointer;">+</button>
                        </div>

                        <?php if($product['stock_quantity'] > 0): ?>
                            <button type="submit" class="btn-add-cart" style="background: #2c3e50; color: white; border: none; padding: 12px 30px; border-radius: 4px; font-weight: bold; cursor: pointer; transition: 0.3s;">ADD TO CART</button>
                        <?php else: ?>
                            <button type="button" class="btn-add-cart disabled" style="background: #ccc; color: white; border: none; padding: 12px 30px; border-radius: 4px; font-weight: bold; cursor: not-allowed;" disabled>OUT OF STOCK</button>
                        <?php endif; ?>
                    </form>

                </div>
            </div>

        </div>
    </div>

    <script>
        // 切换主图
        function changeImage(src) {
            document.getElementById('mainImage').src = src;
        }

        // 数量控制
        function updateQty(change) {
            const input = document.getElementById('qtyInput');
            let currentVal = parseInt(input.value);
            let maxVal = parseInt(input.getAttribute('max'));
            let newVal = currentVal + change;

            if (newVal >= 1 && newVal <= maxVal) {
                input.value = newVal;
            }
        }

        // 导航栏滚动效果
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