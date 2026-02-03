<?php
// 确保连接数据库 (防止父页面没连)
if (!isset($conn)) { include 'db_conn.php'; }

// --- 配置：Living Room 的热点 ---
$hotspots_living = [
    ['id' => 1, 'top' => '53%', 'left' => '28%'], 
    ['id' => 2, 'top' => '40%', 'left' => '79%'],
    ['id' => 3, 'top' => '65%', 'left' => '48%']
];

// --- 获取商品数据 ---
$living_ids = array_column($hotspots_living, 'id');
$living_data = [];
if (!empty($living_ids)) {
    $ids_str = implode(',', $living_ids);
    $result = $conn->query("SELECT * FROM products WHERE product_id IN ($ids_str)");
    while ($row = $result->fetch_assoc()) {
        $living_data[$row['product_id']] = $row;
    }
}
?>

<div class="hotspot-section">
    <div class="hotspot-container">
        <img src="img/Home/AA1.png" alt="Living Room" class="main-bg">

        <?php foreach ($hotspots_living as $spot): 
            $pid = $spot['id'];
            if (isset($living_data[$pid])): 
                $prod = $living_data[$pid];
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
                            <?php if ($prod['compare_at_price'] > $prod['price']): ?>
                                <span class="old-price">RM <?php echo number_format($prod['compare_at_price'], 2); ?></span>
                            <?php endif; ?>
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