<?php
session_start();
include 'db_conn.php';

// --- 1. 安全检查 ---
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: LOGIN-REGISTER.php");
    exit();
}

// --- 2. 获取订单 ID ---
if (!isset($_GET['id'])) {
    echo "<script>alert('No Order ID Provided!'); window.location='admin_orders.php';</script>";
    exit();
}
$order_id = mysqli_real_escape_string($conn, $_GET['id']);

// --- 3. 处理表单提交 ---

// A. 更新状态
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $new_status = $_POST['status'];
    
    // 如果提交了发货信息
    $tracking = isset($_POST['tracking_number']) ? mysqli_real_escape_string($conn, $_POST['tracking_number']) : '';
    $courier = isset($_POST['courier']) ? mysqli_real_escape_string($conn, $_POST['courier']) : '';
    
    $update_sql = "UPDATE orders SET status = '$new_status', tracking_number = '$tracking', courier = '$courier' WHERE order_id = '$order_id'";
    
    if ($conn->query($update_sql)) {
        echo "<script>alert('Order Status & Fulfillment Info Updated!');</script>";
    } else {
        echo "<script>alert('Error updating status');</script>";
    }
}

// B. 取消订单
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cancel_order'])) {
    $cancel_sql = "UPDATE orders SET status = 'Cancelled' WHERE order_id = '$order_id'";
    if ($conn->query($cancel_sql)) {
        echo "<script>alert('Order has been Cancelled!');</script>";
    }
}

// --- 4. 获取订单详情 ---
$sql = "SELECT * FROM orders WHERE order_id = '$order_id'";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    echo "<script>alert('Order Not Found!'); window.location='admin_orders.php';</script>";
    exit();
}

$order_items = [];
while ($row = $result->fetch_assoc()) {
    $order_items[] = $row;
}

// 提取公共信息
$main_info = $order_items[0];
$customer_name = $main_info['customer_name'];
$order_date = date("d M Y, h:i A", strtotime($main_info['order_date']));
$current_status = $main_info['status'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order #<?php echo $order_id; ?> Details</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="admin_style.css">

    <style>
        /* 打印样式控制 */
        @media print {
            .no-print { display: none !important; }
            .card { border: 1px solid #ddd !important; box-shadow: none !important; margin-bottom: 20px; }
            
            /* 打包单模式：隐藏价格 */
            body.packing-slip .price-col { display: none !important; }
            body.packing-slip .invoice-title { display: none; }
            body.packing-slip .packing-title { display: block !important; }
        }
        .packing-title { display: none; } /* 屏幕上不显示打包单标题 */
        
        .product-thumb { width: 50px; height: 50px; object-fit: cover; border-radius: 6px; border: 1px solid #eee; }
    </style>
</head>
<body>

    <div class="d-flex" id="wrapper">
        <div class="border-end" id="sidebar-wrapper">
            <div class="sidebar-heading border-bottom bg-dark text-white">
                <i class="bi bi-box-seam-fill me-2" style="color: #99d5c5;"></i>
                <span class="sidebar-text">Furniture Direct</span> 
            </div>
            <div class="list-group list-group-flush">
                <a href="admin_dashboard.php" class="list-group-item list-group-item-action">
                    <i class="bi bi-grid-1x2-fill me-3"></i><span class="sidebar-text">Dashboard</span> 
                </a>
                <a href="admin_orders.php" class="list-group-item list-group-item-action active">
                    <i class="bi bi-cart3 me-3"></i><span class="sidebar-text">Orders</span> 
                </a>
                <a href="#" class="list-group-item list-group-item-action">
                    <i class="bi bi-bag-check me-3"></i><span class="sidebar-text">Products</span> 
                </a>
                <a href="admin_customers.php" class="list-group-item list-group-item-action">
                    <i class="bi bi-people-fill me-3"></i><span class="sidebar-text">Customers</span> 
                </a>
                <a href="#" class="list-group-item list-group-item-action">
                    <i class="bi bi-graph-up-arrow me-3"></i><span class="sidebar-text">Reports</span> 
                </a>
                <a href="admin_profile.php" class="list-group-item list-group-item-action mt-5 border-top border-secondary pt-3">
                    <i class="bi bi-person-circle me-3"></i><span class="sidebar-text">Admin Profile</span> 
                </a>
            </div>
        </div>

        <div id="page-content-wrapper">
            <nav class="navbar navbar-light border-bottom px-4 py-3 bg-white">
                <div class="container-fluid p-0 d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <button class="btn btn-light btn-sm me-3 border" id="sidebarToggle"><i class="bi bi-list fs-5"></i></button>
                        <h5 class="m-0 d-none d-md-block text-secondary">Order Details</h5>
                    </div>
                    <ul class="navbar-nav ms-auto flex-row align-items-center">
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <div class="bg-secondary rounded-circle text-white d-flex justify-content-center align-items-center me-2" style="width: 35px; height: 35px;">AD</div>
                                <span class="fw-bold d-none d-sm-block"><?php echo isset($_SESSION['username']) ? $_SESSION['username'] : 'Admin'; ?></span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0 position-absolute">
                                <li><a class="dropdown-item" href="admin_profile.php">Profile</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="logout.php">Logout</a></li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </nav>

            <div class="container-fluid p-4">
                
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <a href="admin_orders.php" 
                           class="text-decoration-none text-muted mb-2 d-inline-block no-print"
                           style="position: relative; z-index: 1050;">
                            <i class="bi bi-arrow-left"></i> Back to Orders
                        </a>
                        <h2 class="fw-bold text-dark m-0 invoice-title">Invoice #<?php echo str_pad($order_id, 3, '0', STR_PAD_LEFT); ?></h2>
                        <h2 class="fw-bold text-dark m-0 packing-title">Packing Slip #<?php echo str_pad($order_id, 3, '0', STR_PAD_LEFT); ?></h2>
                    </div>
                    <div class="d-flex gap-2 no-print">
                        <button onclick="printPackingSlip()" class="btn btn-outline-dark"><i class="bi bi-box-seam me-2"></i>Print Packing Slip</button>
                        <button onclick="printInvoice()" class="btn btn-dark"><i class="bi bi-printer me-2"></i>Print Invoice</button>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-8">
                        <div class="card border-0 shadow-sm rounded-3 mb-4">
                            <div class="card-header bg-white py-3 border-bottom">
                                <h5 class="m-0 fw-bold">Order Items</h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="ps-4">Product</th>
                                                <th class="text-center price-col">Unit Price</th>
                                                <th class="text-center">Quantity</th>
                                                <th class="text-end pe-4 price-col">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $grand_total = 0;
                                            foreach($order_items as $item): 
                                                $subtotal = $item['price'] * $item['quantity'];
                                                $grand_total += $subtotal;
                                            ?>
                                            <tr>
                                                <td class="ps-4">
                                                    <div class="d-flex align-items-center">
                                                        <img src="img/<?php echo $item['product_image'] ? $item['product_image'] : 'default_product.png'; ?>" class="product-thumb me-3" alt="Product">
                                                        <div>
                                                            <div class="fw-bold"><?php echo $item['product_name']; ?></div>
                                                            <div class="small text-muted">Variant: <?php echo $item['product_variant']; ?></div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-center price-col">$<?php echo number_format($item['price'], 2); ?></td>
                                                <td class="text-center">x <?php echo $item['quantity']; ?></td>
                                                <td class="text-end pe-4 fw-bold price-col">$<?php echo number_format($subtotal, 2); ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                        <tfoot class="bg-light price-col">
                                            <tr>
                                                <td colspan="3" class="text-end fw-bold pt-3">Subtotal:</td>
                                                <td class="text-end pe-4 pt-3">$<?php echo number_format($grand_total, 2); ?></td>
                                            </tr>
                                            <tr>
                                                <td colspan="3" class="text-end fw-bold border-0">Shipping:</td>
                                                <td class="text-end pe-4 border-0">$0.00</td>
                                            </tr>
                                            <tr>
                                                <td colspan="3" class="text-end fw-bold fs-5 border-0 pb-3">Grand Total:</td>
                                                <td class="text-end pe-4 fw-bold fs-5 text-success border-0 pb-3">$<?php echo number_format($grand_total, 2); ?></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="card border-0 shadow-sm rounded-3 mb-4">
                            <div class="card-header bg-white py-3 border-bottom">
                                <h5 class="m-0 fw-bold">Payment Details</h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <div class="small text-muted">Payment Method</div>
                                        <div class="fw-bold"><?php echo $main_info['payment_method']; ?></div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="small text-muted">Transaction ID</div>
                                        <div class="fw-bold text-monospace"><?php echo $main_info['transaction_id']; ?></div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="small text-muted">Payment Status</div>
                                        <?php if($main_info['payment_status'] == 'Paid'): ?>
                                            <span class="badge bg-success bg-opacity-10 text-success">Paid</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning bg-opacity-10 text-warning">Unpaid</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        
                        <div class="card border-0 shadow-sm rounded-3 mb-4 no-print">
                            <div class="card-body">
                                <h6 class="fw-bold text-muted mb-3 text-uppercase small">Order Actions</h6>
                                
                                <form action="" method="POST" id="statusForm">
                                    <label class="form-label small fw-bold">Update Status</label>
                                    <select name="status" id="statusSelect" class="form-select mb-3 fw-bold">
                                    <option value="Pending" class="text-warning fw-bold" <?php if($current_status == 'Pending') echo 'selected'; ?>>Pending</option>
                                    
                                    <option value="Completed" class="text-success fw-bold" <?php if($current_status == 'Completed') echo 'selected'; ?>>Completed</option>
                                    
                                    <option value="Shipped" class="text-primary fw-bold" <?php if($current_status == 'Shipped') echo 'selected'; ?>>Shipped</option>
                                    
                                    <option value="Cancelled" class="text-danger fw-bold" <?php if($current_status == 'Cancelled') echo 'selected'; ?>>Cancelled</option>
                                </select>

                                    <div id="fulfillmentInfo" class="mb-3 p-3 bg-light rounded border <?php if($current_status != 'Shipped') echo 'd-none'; ?>">
                                        <h6 class="small fw-bold mb-2">Fulfillment Info</h6>
                                        <div class="mb-2">
                                            <input type="text" name="courier" class="form-control form-control-sm" placeholder="Courier (e.g. DHL)" value="<?php echo $main_info['courier']; ?>">
                                        </div>
                                        <div>
                                            <input type="text" name="tracking_number" class="form-control form-control-sm" placeholder="Tracking Number" value="<?php echo $main_info['tracking_number']; ?>">
                                        </div>
                                    </div>

                                    <button type="submit" name="update_status" class="btn btn-dark w-100 mb-2">Update Status</button>
                                </form>

                                <?php if($current_status != 'Cancelled'): ?>
                                <form action="" method="POST" onsubmit="return confirm('Are you sure you want to CANCEL this order?');">
                                    <button type="submit" name="cancel_order" class="btn btn-outline-danger w-100">Cancel Order</button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="card border-0 shadow-sm rounded-3">
                            <div class="card-body">
                                <h6 class="fw-bold text-muted mb-3 text-uppercase small">Customer Details</h6>
                                <div class="d-flex align-items-center mb-3">
                                    <div class="bg-light rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                        <i class="bi bi-person text-secondary"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold"><?php echo $customer_name; ?></div>
                                        <div class="small text-muted">ID: #<?php echo $main_info['user_id']; ?></div>
                                    </div>
                                </div>
                                <hr>
                                <div class="mb-2">
                                    <div class="small text-muted fw-bold">Order Date</div>
                                    <div><?php echo $order_date; ?></div>
                                </div>
                                <div class="mb-3">
                                    <div class="small text-muted fw-bold">Shipping Address</div>
                                    <div>123, Jalan Skudai, Johor Bahru, 81300, Johor.</div>
                                </div>

                                <div class="alert alert-warning mb-0 p-2 small">
                                    <i class="bi bi-chat-square-text me-1"></i> <strong>Note:</strong>
                                    <?php echo $main_info['order_notes'] ? $main_info['order_notes'] : 'No notes provided.'; ?>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="admin_script.js"></script>
    
    <script>
        // 1. 获取元素
        const statusSelect = document.getElementById('statusSelect');
        const fulfillmentInfo = document.getElementById('fulfillmentInfo');
        
        // 2. 颜色切换函数 (独立提取出来)
        function updateColor(select) {
            // 先移除所有颜色类
            select.classList.remove('text-warning', 'border-warning', 'text-success', 'border-success', 'text-danger', 'border-danger', 'text-primary', 'border-primary');
            
            // 再添加新颜色
            if (select.value === 'Pending') select.classList.add('text-warning', 'border-warning');
            else if (select.value === 'Completed') select.classList.add('text-success', 'border-success');
            else if (select.value === 'Cancelled') select.classList.add('text-danger', 'border-danger');
            else if (select.value === 'Shipped') select.classList.add('text-primary', 'border-primary');
        }

        // 3. 监听变化
        if (statusSelect) {
            // 初始化颜色
            updateColor(statusSelect);

            statusSelect.addEventListener('change', function() {
                // A. 变色
                updateColor(this);
                
                // B. 控制发货框显示/隐藏 (如果有这个框的话)
                if (fulfillmentInfo) {
                    if(this.value === 'Shipped') {
                        fulfillmentInfo.classList.remove('d-none');
                    } else {
                        fulfillmentInfo.classList.add('d-none');
                    }
                }
            });
        }

        // ... 下面是你其他的打印函数 ...
        function printInvoice() {
            document.body.classList.remove('packing-slip');
            window.print();
        }

        function printPackingSlip() {
            document.body.classList.add('packing-slip');
            window.print();
            setTimeout(() => {
                document.body.classList.remove('packing-slip');
            }, 1000);
        }
</script>
</body>
</html>