<?php
session_start();
include 'db_conn.php';

// 1. 安全检查：必须登录
if (!isset($_SESSION['user_id'])) {
    header("Location: LOGIN-REGISTER.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// 2. 获取订单 ID
if (!isset($_GET['id'])) {
    echo "<script>alert('No Order ID Provided!'); window.location.href='USER-DASHBOARD.php';</script>";
    exit();
}

$order_db_id = intval($_GET['id']);

// 3. 查询订单详情
$sql = "SELECT * FROM orders WHERE id = $order_db_id AND user_id = $user_id";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    echo "<script>alert('Order Not Found or Access Denied!'); window.location.href='USER-DASHBOARD.php';</script>";
    exit();
}

$order = $result->fetch_assoc();

// 4. 获取订单内的商品
$sql_items = "SELECT oi.*, p.product_name, p.product_image, p.price as current_price
              FROM order_items oi
              JOIN products p ON oi.product_id = p.product_id
              WHERE oi.order_id = $order_db_id";
$items_result = $conn->query($sql_items);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details #<?php echo $order['order_id']; ?> | FurnitureDirect</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body { background-color: #f8f9fa; }
        
        .details-page-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .back-link {
            text-decoration: none;
            color: #666;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: 0.3s;
        }
        .back-link:hover { color: #20c997; }

        .btn-print {
            background: white; border: 1px solid #2c3e50; color: #2c3e50;
            padding: 8px 15px; border-radius: 5px; cursor: pointer; font-weight: bold;
        }
        .btn-print:hover { background: #2c3e50; color: white; }

        .details-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 25px;
        }

        .card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            margin-bottom: 25px;
            overflow: hidden;
        }

        .card-header {
            padding: 20px;
            border-bottom: 1px solid #f0f0f0;
            font-size: 1.1rem;
            font-weight: 700;
            color: #2c3e50;
            background: #fff;
        }

        .card-body { padding: 20px; }

        .items-table { width: 100%; border-collapse: collapse; }
        .items-table th { text-align: left; color: #888; font-size: 0.9rem; padding-bottom: 15px; }
        .items-table td { padding: 15px 0; border-bottom: 1px solid #f9f9f9; vertical-align: middle; }
        .items-table td:last-child { text-align: right; }
        
        .product-flex { display: flex; align-items: center; gap: 15px; }
        .thumb-img { width: 60px; height: 60px; border-radius: 6px; object-fit: cover; border: 1px solid #eee; }
        .p-name { font-weight: 600; color: #333; display: block; }
        .p-price { font-size: 0.85rem; color: #888; }

        .summary-row {
            display: flex; justify-content: space-between; margin-bottom: 10px; color: #666; font-size: 0.95rem;
        }
        .summary-total {
            border-top: 2px solid #eee; margin-top: 15px; padding-top: 15px;
            font-size: 1.2rem; font-weight: 800; color: #20c997;
        }

        .status-badge {
            padding: 5px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: bold; display: inline-block;
        }
        .status-Pending { background: #fff3cd; color: #856404; }
        .status-Paid { background: #d1e7dd; color: #0f5132; }
        .status-Shipped { background: #cff4fc; color: #055160; }
        .status-Cancelled { background: #f8d7da; color: #721c24; }
        .status-Completed { background: #d1e7dd; color: #0f5132; }

        @media (max-width: 768px) {
            .details-grid { grid-template-columns: 1fr; }
        }

        @media print {
            .navbar, .header-section button, .back-link { display: none !important; }
            .page-content { margin-top: 0 !important; padding-top: 0 !important; }
            .details-page-container { margin-top: 0 !important; padding-top: 0 !important; }
            .card { box-shadow: none !important; border: 1px solid #ddd !important; }
        }
    </style>
</head>
<body>

    <?php include 'navbar.php'; ?>

    <div class="page-content">
        <div class="details-page-container">
            
            <div class="header-section">
                <div>
                    <a href="USER-DASHBOARD.php" class="back-link">← Back to Dashboard</a>
                    <h1 style="margin: 10px 0 5px 0; color:#2c3e50;">Order #<?php echo htmlspecialchars($order['order_id']); ?></h1>
                    <span style="color:#888; font-size:0.9rem;">Placed on <?php echo date("d M Y, h:i A", strtotime($order['order_date'])); ?></span>
                </div>
                <button onclick="window.print()" class="btn-print">🖨 Print Invoice</button>
            </div>

            <div class="details-grid">
                
                <div class="left-col">
                    <div class="card">
                        <div class="card-header">Order Items</div>
                        <div class="card-body">
                            <table class="items-table">
                                <thead>
                                    <tr>
                                        <th width="60%">Product</th>
                                        <th width="20%">Price</th>
                                        <th width="20%" style="text-align:right;">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $calc_subtotal = 0;
                                    if ($items_result->num_rows > 0) {
                                        while($item = $items_result->fetch_assoc()) {
                                            $line_total = $item['price'] * $item['quantity'];
                                            $calc_subtotal += $line_total;
                                            ?>
                                            <tr>
                                                <td>
                                                    <div class="product-flex">
                                                        <img src="img/<?php echo $item['product_image']; ?>" class="thumb-img" alt="Img">
                                                        <div>
                                                            <span class="p-name"><?php echo htmlspecialchars($item['product_name']); ?></span>
                                                            <span class="p-price">Qty: <?php echo $item['quantity']; ?></span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>RM <?php echo number_format($item['price'], 2); ?></td>
                                                <td style="font-weight:bold;">RM <?php echo number_format($line_total, 2); ?></td>
                                            </tr>
                                            <?php
                                        }
                                    }
                                    
                                    // --- 💡 智能反推 SST 和 运费 ---
                                    // 1. 算出 SST (10% of Subtotal)
                                    $sst_amount = $calc_subtotal * 0.10;
                                    
                                    // 2. 算出运费 = 总价 - 小计 - SST
                                    $shipping_fee = $order['grand_total'] - $calc_subtotal - $sst_amount;
                                    
                                    // 容错处理：防止精度问题出现 -0.01
                                    if ($shipping_fee < 0) $shipping_fee = 0;
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <?php if(!empty($order['courier']) || !empty($order['tracking_number'])): ?>
                    <div class="card">
                        <div class="card-header">Delivery Information</div>
                        <div class="card-body">
                            <p><strong>Courier:</strong> <?php echo htmlspecialchars($order['courier']); ?></p>
                            <p><strong>Tracking Number:</strong> <?php echo htmlspecialchars($order['tracking_number']); ?></p>
                            <a href="#" style="color:#20c997; font-weight:bold; font-size:0.9rem;">Track Shipment →</a>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="right-col">
                    
                    <div class="card">
                        <div class="card-header">Order Summary</div>
                        <div class="card-body">
                            <div class="summary-row">
                                <span>Subtotal</span>
                                <span>RM <?php echo number_format($calc_subtotal, 2); ?></span>
                            </div>
                            
                            <div class="summary-row">
                                <span>SST (10%)</span>
                                <span>RM <?php echo number_format($sst_amount, 2); ?></span>
                            </div>

                            <div class="summary-row">
                                <span>Shipping Fee</span>
                                <span>
                                    <?php 
                                    if ($shipping_fee <= 0.01) {
                                        echo '<span style="color:#20c997; font-weight:bold;">Free</span>';
                                    } else {
                                        echo 'RM ' . number_format($shipping_fee, 2);
                                    }
                                    ?>
                                </span>
                            </div>

                            <div class="summary-row summary-total">
                                <span>Grand Total</span>
                                <span>RM <?php echo number_format($order['grand_total'], 2); ?></span>
                            </div>
                            
                            <div style="margin-top:20px;">
                                <?php 
                                    $st = $order['status'];
                                    $class = 'status-' . str_replace(' ', '', $st);
                                ?>
                                <div style="display:flex; justify-content:space-between; align-items:center;">
                                    <span style="font-weight:600; color:#555;">Order Status:</span>
                                    <span class="status-badge <?php echo $class; ?>"><?php echo $st; ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">Customer Details</div>
                        <div class="card-body">
                            <div style="margin-bottom:15px;">
                                <div style="color:#888; font-size:0.85rem; font-weight:bold; margin-bottom:5px;">SHIPPING ADDRESS</div>
                                <div style="color:#333; line-height:1.5;">
                                    <strong><?php echo htmlspecialchars($order['customer_name']); ?></strong><br>
                                    <?php echo htmlspecialchars($order['phone']); ?><br>
                                    <?php echo nl2br(htmlspecialchars($order['address'])); ?>
                                </div>
                            </div>
                            
                            <hr style="border:0; border-top:1px solid #eee; margin:15px 0;">

                            <div>
                                <div style="color:#888; font-size:0.85rem; font-weight:bold; margin-bottom:5px;">PAYMENT METHOD</div>
                                <div style="color:#333;">
                                    <?php echo htmlspecialchars($order['payment_method']); ?>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>

    <script>
        window.onscroll = function() {
            const nav = document.querySelector('.navbar');
            if (nav) {
                if (window.scrollY > 20) { nav.classList.add('collapsed'); } 
                else { nav.classList.remove('collapsed'); }
            }
        };
    </script>
</body>
</html>