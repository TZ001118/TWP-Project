<?php
if (!isset($conn)) { include 'db_conn.php'; }

// --- 配置：Bedroom 的热点 ---
$hotspots_bedroom = [
    ['id' => 21, 'top' => '45%', 'left' => '32%'],
    ['id' => 19, 'top' => '70%', 'left' => '79%'],
    ['id' => 20, 'top' => '55%', 'left' => '58%'],
    ['id' => 22, 'top' => '72%', 'left' => '23%']
];

// --- 获取商品数据 ---
$bed_ids = array_column($hotspots_bedroom, 'id');
$bed_data = [];
if (!empty($bed_ids)) {
    $ids_str = implode(',', $bed_ids);
    $result = $conn->query("SELECT * FROM products WHERE product_id IN ($ids_str)");
    while ($row = $result->fetch_assoc()) {
        $bed_data[$row['product_id']] = $row;
    }
}
?>

<div class="hotspot-section">
    <div class="hotspot-container">
        <img src="img/Home/BB1.png" alt="Bedroom" class="main-bg">

        <?php foreach ($hotspots_bedroom as $spot): 
            $pid = $spot['id'];
            if (isset($bed_data[$pid])): 
                $prod = $bed_data[$pid];
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