<?php
if (!isset($conn)) { include 'db_conn.php'; }

// --- Decor 热点 (基于金属圆桌图) ---
$hotspots_decor = [
    ['id' => 15, 'top' => '20%', 'left' => '50%'], // 金属圆桌
    ['id' => 14, 'top' => '60%', 'left' => '40%']  // 前面的圆凳
];

// --- 获取数据 ---
$decor_ids = array_column($hotspots_decor, 'id');
$decor_data = [];
if (!empty($decor_ids)) {
    $ids_str = implode(',', $decor_ids);
    $result = $conn->query("SELECT * FROM products WHERE product_id IN ($ids_str)");
    while ($row = $result->fetch_assoc()) {
        $decor_data[$row['product_id']] = $row;
    }
}
?>

<div class="hotspot-section">
    <div class="hotspot-container">
        <img src="img\Home\showroom_decor.png" alt="Outdoor Decor" class="main-bg">

        <?php foreach ($hotspots_decor as $spot): 
            $pid = $spot['id'];
            if (isset($decor_data[$pid])): 
                $prod = $decor_data[$pid];
        ?>
            <div class="hotspot" style="top: <?php echo $spot['top']; ?>; left: <?php echo $spot['left']; ?>;">
                <div class="hotspot-dot"></div>
                <div class="hotspot-card">
                    <a href="PRODUCT_DETAILS.php?id=<?php echo $pid; ?>">
                        <img src="img/<?php echo $prod['product_image']; ?>" alt="<?php echo htmlspecialchars($prod['product_name']); ?>">
                    </a>
                    <div class="card-info">
                        <h4>
                            <a href="PRODUCT_DETAILS.php?id=<?php echo $pid; ?>">
                                <?php echo htmlspecialchars($prod['product_name']); ?>
                            </a>
                        </h4>
                        <div class="price-box">
                            <span class="price">RM <?php echo number_format($prod['price'], 2); ?></span>
                        </div>
                        <form action="add_to_cart.php" method="POST">
                            <input type="hidden" name="product_id" value="<?php echo $pid; ?>">
                            <input type="hidden" name="quantity" value="1">
                            <button type="submit" class="add-to-cart">ADD TO CART</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endif; endforeach; ?>
    </div>
</div>