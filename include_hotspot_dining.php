<?php
if (!isset($conn)) { include 'db_conn.php'; }

// --- Dining Room 热点 (基于长方形白桌图) ---
$hotspots_dining = [
    ['id' => 8, 'top' => '45%', 'left' => '55%'], // 桌子中心
    ['id' => 9, 'top' => '60%', 'left' => '25%']  // 左边的椅子
];

// --- 获取数据 ---
$dining_ids = array_column($hotspots_dining, 'id');
$dining_data = [];
if (!empty($dining_ids)) {
    $ids_str = implode(',', $dining_ids);
    $result = $conn->query("SELECT * FROM products WHERE product_id IN ($ids_str)");
    while ($row = $result->fetch_assoc()) {
        $dining_data[$row['product_id']] = $row;
    }
}
?>

<div class="hotspot-section">
    <div class="hotspot-container">
        <img src="img\Home\showroom_dining.png" alt="Dining Room" class="main-bg">

        <?php foreach ($hotspots_dining as $spot): 
            $pid = $spot['id'];
            // 如果数据库里找不到这个ID，为了不报错，就不显示热点
            if (isset($dining_data[$pid])): 
                $prod = $dining_data[$pid];
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