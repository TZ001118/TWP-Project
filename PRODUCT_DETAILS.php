<?php
session_start();
include 'db_conn.php';

if (!isset($_GET['id'])) {
    header("Location: HOME.php");
    exit();
}

$product_id = $_GET['id'];

$sql = "SELECT p.*, c.category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.category_id 
        WHERE p.product_id = '$product_id'";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    echo "Product not found.";
    exit();
}

$product = $result->fetch_assoc();

$sql_rating = "SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews 
               FROM reviews 
               WHERE product_id = '$product_id'";
$rating_result = $conn->query($sql_rating);
$rating_data = $rating_result->fetch_assoc();
$avg_rating = round($rating_data['avg_rating'] ?? 0, 1);
$total_reviews = $rating_data['total_reviews'];

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
    <title><?php echo $product['product_name']; ?> | Furniture Direct</title>
    
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="product_details.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>

    <?php include 'navbar.php'; ?>

    <div class="page-content">
        <div class="container main-product-container">
            
            <div class="breadcrumb">
                <a href="HOME.php">Home</a> &gt; 
                <a href="CATEGORY_PRODUCTS.php?category=<?php echo $product['category_name']; ?>"><?php echo $product['category_name']; ?></a> &gt; 
                <span><?php echo $product['product_name']; ?></span>
            </div>

            <div class="product-detail-wrapper">
                
                <div class="gallery-section">
                    <div class="main-image-box">
                        <img id="mainImage" src="img/<?php echo $product['product_image']; ?>" alt="<?php echo $product['product_name']; ?>">
                    </div>
                    <div class="thumbnail-row">
                        <?php foreach($gallery_images as $img): ?>
                            <div class="thumb-box" onclick="changeImage('img/<?php echo $img; ?>')">
                                <img src="img/<?php echo $img; ?>" alt="Thumbnail">
                            </div>
                        <?php endforeach; ?>
                        
                        <?php 
                        $empty_slots = 4 - count($gallery_images);
                        for($i=0; $i<$empty_slots; $i++): 
                        ?>
                            <div class="thumb-box empty"></div>
                        <?php endfor; ?>
                    </div>
                </div>

                <div class="info-section">
                    <h1 class="product-title"><?php echo $product['product_name']; ?></h1>
                    
                    <div class="rating-box">
                        <div class="stars">
                            <?php 
                            for($i=1; $i<=5; $i++) {
                                if($i <= $avg_rating) echo '<i class="fa-solid fa-star text-warning"></i>';
                                elseif($i - 0.5 <= $avg_rating) echo '<i class="fa-solid fa-star-half-stroke text-warning"></i>';
                                else echo '<i class="fa-regular fa-star text-muted"></i>';
                            }
                            ?>
                        </div>
                        <span class="review-count">(<?php echo $total_reviews; ?> reviews)</span>
                    </div>

                    <div class="price-box">
                        <?php if($product['compare_at_price'] > 0): ?>
                            <span class="old-price">RM <?php echo number_format($product['compare_at_price'], 2); ?></span>
                        <?php endif; ?>
                        <span class="current-price">RM <?php echo number_format($product['price'], 2); ?></span>
                    </div>

                    <div class="description-box">
                        <?php echo nl2br(htmlspecialchars_decode($product['description'])); ?>
                    </div>

                    <div class="meta-info">
                        <?php if(!empty($product['brand'])): ?>
                            <div class="meta-item"><strong>Brand:</strong> <?php echo $product['brand']; ?></div>
                        <?php endif; ?>
                        
                        <?php if(!empty($product['sku'])): ?>
                            <div class="meta-item"><strong>SKU:</strong> <?php echo $product['sku']; ?></div>
                        <?php endif; ?>

                        <?php if(!empty($product['tags'])): ?>
                            <div class="meta-item"><strong>Tags:</strong> <?php echo $product['tags']; ?></div>
                        <?php endif; ?>

                        <div class="meta-item">
                            <strong>Availability:</strong> 
                            <?php if($product['stock_quantity'] > 0): ?>
                                <span class="text-success"><i class="fa-solid fa-check"></i> In Stock (<?php echo $product['stock_quantity']; ?> left)</span>
                            <?php else: ?>
                                <span class="text-danger"><i class="fa-solid fa-xmark"></i> Out of Stock</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <hr class="divider">

                    <div class="variants-box">
                        <?php if(!empty($product['variants_color'])): ?>
                            <div class="variant-row">
                                <span class="variant-label">Color:</span>
                                <span class="variant-value"><?php echo $product['variants_color']; ?></span>
                            </div>
                        <?php endif; ?>

                        <?php if(!empty($product['variants_size'])): ?>
                            <div class="variant-row">
                                <span class="variant-label">Size:</span>
                                <span class="variant-value"><?php echo $product['variants_size']; ?></span>
                            </div>
                        <?php endif; ?>

                        <?php if(!empty($product['variants_material'])): ?>
                            <div class="variant-row">
                                <span class="variant-label">Material:</span>
                                <span class="variant-value"><?php echo $product['variants_material']; ?></span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="specs-box">
                        <?php if($product['weight'] > 0): ?>
                            <div class="spec-item"><strong>Weight:</strong> <?php echo $product['weight']; ?> kg</div>
                        <?php endif; ?>
                        <?php if(!empty($product['dimensions'])): ?>
                            <div class="spec-item"><strong>Dimensions:</strong> <?php echo $product['dimensions']; ?></div>
                        <?php endif; ?>
                    </div>

                    <form action="add_to_cart.php" method="POST" class="add-cart-form">
                        <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                        
                        <div class="qty-wrapper">
                            <button type="button" onclick="updateQty(-1)">-</button>
                            <input type="number" name="quantity" id="qtyInput" value="1" min="1" max="<?php echo $product['stock_quantity']; ?>">
                            <button type="button" onclick="updateQty(1)">+</button>
                        </div>

                        <?php if($product['stock_quantity'] > 0): ?>
                            <button type="submit" class="btn-add-cart">ADD TO CART</button>
                        <?php else: ?>
                            <button type="button" class="btn-add-cart disabled" disabled>OUT OF STOCK</button>
                        <?php endif; ?>
                    </form>

                </div>
            </div>

        </div>
    </div>

    <script>
        function changeImage(src) {
            document.getElementById('mainImage').src = src;
        }

        function updateQty(change) {
            const input = document.getElementById('qtyInput');
            let newVal = parseInt(input.value) + change;
            if (newVal < 1) newVal = 1;
            const max = parseInt(input.getAttribute('max'));
            if (newVal > max) newVal = max;
            input.value = newVal;
        }

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