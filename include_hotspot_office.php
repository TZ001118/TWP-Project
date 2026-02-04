<?php
if (!isset($conn)) { include 'db_conn.php'; }

// --- Office 热点 (基于白色圆桌图) ---
$hotspots_office = [
    ['id' => 11, 'top' => '37%', 'left' => '40%'], // 圆桌
    ['id' => 10, 'top' => '65%', 'left' => '70%']  // 右边的黑椅子
];

// --- 获取数据 ---
$office_ids = array_column($hotspots_office, 'id');
$office_data = [];
if (!empty($office_ids)) {
    $ids_str = implode(',', $office_ids);
    $result = $conn->query("SELECT * FROM products WHERE product_id IN ($ids_str)");
    while ($row = $result->fetch_assoc()) {
        $office_data[$row['product_id']] = $row;
    }
}
?>

<div class="hotspot-section">
    <div class="hotspot-container">
        <img src="img\Home\showroom_office.png" alt="Office Space" class="main-bg">

        <?php foreach ($hotspots_office as $spot): 
            $pid = $spot['id'];
            if (isset($office_data[$pid])): 
                $prod = $office_data[$pid];
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