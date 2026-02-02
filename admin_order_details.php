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
    $_SESSION['swal'] = ['type' => 'error', 'title' => 'Error', 'text' => 'No Order ID Provided!'];
    header("Location: admin_orders.php");
    exit();
}
$order_id = mysqli_real_escape_string($conn, $_GET['id']);

// --- 3. 处理表单提交 (更新状态) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $new_status = $_POST['status'];
    $tracking = isset($_POST['tracking_number']) ? mysqli_real_escape_string($conn, $_POST['tracking_number']) : '';
    $courier = isset($_POST['courier']) ? mysqli_real_escape_string($conn, $_POST['courier']) : '';
    
    $update_sql = "UPDATE orders SET status = '$new_status', tracking_number = '$tracking', courier = '$courier' WHERE order_id = '$order_id'";
    
    if ($conn->query($update_sql)) {
        $_SESSION['swal'] = ['type' => 'success', 'title' => 'Success!', 'text' => 'Order Status Updated!'];
    } else {
        $_SESSION['swal'] = ['type' => 'error', 'title' => 'Error!', 'text' => 'Error updating status'];
    }
    header("Location: admin_order_details.php?id=$order_id");
    exit();
}

// 处理取消订单
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cancel_order'])) {
    $cancel_sql = "UPDATE orders SET status = 'Cancelled' WHERE order_id = '$order_id'";
    if ($conn->query($cancel_sql)) {
        $_SESSION['swal'] = ['type' => 'success', 'title' => 'Cancelled', 'text' => 'Order has been Cancelled!'];
    }
    header("Location: admin_order_details.php?id=$order_id");
    exit();
}

// --- 4. ★★★ 修正后的查询逻辑 ★★★ ---

// 第一步：获取订单主体信息 (Main Order Info)
$sql_order = "SELECT * FROM orders WHERE order_id = '$order_id'";
$res_order = $conn->query($sql_order);

if ($res_order->num_rows == 0) {
    $_SESSION['swal'] = ['type' => 'error', 'title' => 'Not Found', 'text' => 'Order Not Found!'];
    header("Location: admin_orders.php");
    exit();
}

$main_info = $res_order->fetch_assoc();
$internal_id = $main_info['id']; // 获取数据库自增 ID (int)，用于关联商品表

// 第二步：获取订单商品详情 (Order Items + Products)
// 我们需要把 order_items 和 products 表连接起来，才能拿到图片和商品名
$sql_items = "SELECT oi.*, p.product_name, p.product_image 
              FROM order_items oi 
              JOIN products p ON oi.product_id = p.product_id 
              WHERE oi.order_id = '$internal_id'";

$res_items = $conn->query($sql_items);
$order_items = [];
while ($row = $res_items->fetch_assoc()) {
    $order_items[] = $row;
}

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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        @media print {
            .no-print { display: none !important; }
            .card { border: 1px solid #ddd !important; box-shadow: none !important; margin-bottom: 20px; }
            body.packing-slip .price-col { display: none !important; }
            body.packing-slip .invoice-title { display: none; }
            body.packing-slip .packing-title { display: block !important; }
        }
        .packing-title { display: none; } 
        .product-thumb { width: 50px; height: 50px; object-fit: cover; border-radius: 6px; border: 1px solid #eee; }
    </style>
</head>
<body>
    <script>
        if (localStorage.getItem('sb|sidebar-toggle') === 'true') {
            document.body.classList.add('sb-sidenav-toggled');
        }
    </script>

    <div class="d-flex" id="wrapper">
        <div class="border-end" id="sidebar-wrapper">
            <div class="sidebar-heading border-bottom bg-dark text-white">
                <i class="bi bi-box-seam-fill me-2" style="color: #99d5c5;"></i>
                <span class="sidebar-text">DOMEA</span> 
            </div>
            <div class="list-group list-group-flush">
                <a href="admin_dashboard.php" class="list-group-item list-group-item-action">
                    <i class="bi bi-grid-1x2-fill me-3"></i><span class="sidebar-text">Dashboard</span> 
                </a>
                <a href="admin_orders.php" class="list-group-item list-group-item-action active">
                    <i class="bi bi-cart3 me-3"></i><span class="sidebar-text">Orders</span> 
                </a>
                <a href="admin_categories.php" class="list-group-item list-group-item-action">
                    <i class="bi bi-tags-fill me-3"></i><span class="sidebar-text">Categories</span> 
                </a>
                <a href="admin_products.php" class="list-group-item list-group-item-action">
                    <i class="bi bi-bag-check me-3"></i><span class="sidebar-text">Products</span> 
                </a>
                <a href="admin_customers.php" class="list-group-item list-group-item-action">
                    <i class="bi bi-people-fill me-3"></i><span class="sidebar-text">Customers</span> 
                </a>
                <a href="admin_reports.php" class="list-group-item list-group-item-action">
                    <i class="bi bi-graph-up-arrow me-3"></i><span class="sidebar-text">Reports</span> 
                </a>
                <a href="admin_profile.php" class="list-group-item list-group-item-action">
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
                        <h2 class="fw-bold text-dark m-0 invoice-title">Invoice #<?php echo $order_id; ?></h2>
                        <h2 class="fw-bold text-dark m-0 packing-title">Packing Slip #<?php echo $order_id; ?></h2>
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
                                            // 重新计算总价 (基于 order_items)
                                            $calculated_total = 0;
                                            foreach($order_items as $item): 
                                                $subtotal = $item['price'] * $item['quantity'];
                                                $calculated_total += $subtotal;
                                            ?>
                                            <tr>
                                                <td class="ps-4">
                                                    <div class="d-flex align-items-center">
                                                        <img src="img/<?php echo !empty($item['product_image']) ? $item['product_image'] : 'default_product.png'; ?>" class="product-thumb me-3" alt="Product">
                                                        <div>
                                                            <div class="fw-bold"><?php echo htmlspecialchars($item['product_name']); ?></div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-center price-col">RM <?php echo number_format($item['price'], 2); ?></td>
                                                <td class="text-center">x <?php echo $item['quantity']; ?></td>
                                                <td class="text-end pe-4 fw-bold price-col">RM <?php echo number_format($subtotal, 2); ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                        <tfoot class="bg-light price-col">
                                            <tr>
                                                <td colspan="3" class="text-end fw-bold pt-3">Items Subtotal:</td>
                                                <td class="text-end pe-4 pt-3">RM <?php echo number_format($calculated_total, 2); ?></td>
                                            </tr>
                                            <?php 
                                                $db_grand_total = $main_info['grand_total'];
                                                $extra_cost = $db_grand_total - $calculated_total; 
                                                // 简单的逻辑：如果总价 > 商品价，剩下的就是运费+税
                                            ?>
                                            <?php if($extra_cost > 0): ?>
                                            <tr>
                                                <td colspan="3" class="text-end fw-bold border-0">Shipping & Tax:</td>
                                                <td class="text-end pe-4 border-0">RM <?php echo number_format($extra_cost, 2); ?></td>
                                            </tr>
                                            <?php endif; ?>
                                            
                                            <tr>
                                                <td colspan="3" class="text-end fw-bold fs-5 border-0 pb-3">Grand Total:</td>
                                                <td class="text-end pe-4 fw-bold fs-5 text-success border-0 pb-3">RM <?php echo number_format($db_grand_total, 2); ?></td>
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
                                    <div class="col-md-6">
                                        <div class="small text-muted">Payment Method</div>
                                        <div class="fw-bold"><?php echo $main_info['payment_method']; ?></div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="small text-muted">Payment Status</div>
                                        <?php if($main_info['grand_total'] > 0): ?>
                                            <span class="badge bg-success bg-opacity-10 text-success">Paid</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning bg-opacity-10 text-warning">Pending</span>
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
                                <button type="button" onclick="confirmCancel()" class="btn btn-outline-danger w-100">Cancel Order</button>
                                <form action="" method="POST" id="cancelForm" style="display:none;">
                                    <input type="hidden" name="cancel_order" value="1">
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
                                        <div class="small text-muted">User ID: #<?php echo $main_info['user_id']; ?></div>
                                    </div>
                                </div>
                                <hr>
                                <div class="mb-2">
                                    <div class="small text-muted fw-bold">Order Date</div>
                                    <div><?php echo $order_date; ?></div>
                                </div>
                                <div class="mb-3">
                                    <div class="small text-muted fw-bold">Shipping Address</div>
                                    <div><?php echo htmlspecialchars($main_info['address']); ?></div>
                                </div>
                                <div class="mb-3">
                                    <div class="small text-muted fw-bold">Phone</div>
                                    <div><?php echo htmlspecialchars($main_info['phone']); ?></div>
                                </div>
                                <div class="mb-3">
                                    <div class="small text-muted fw-bold">Email</div>
                                    <div><?php echo htmlspecialchars($main_info['email']); ?></div>
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
        // SweetAlert
        <?php if(isset($_SESSION['swal'])): ?>
            Swal.fire({
                icon: '<?php echo $_SESSION['swal']['type']; ?>',
                title: '<?php echo $_SESSION['swal']['title']; ?>',
                text: '<?php echo $_SESSION['swal']['text']; ?>',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
            });
            <?php unset($_SESSION['swal']); ?>
        <?php endif; ?>

        function confirmCancel() {
            Swal.fire({
                title: 'Cancel Order?',
                text: "Are you sure you want to cancel this order?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, Cancel it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('cancelForm').submit();
                }
            })
        }

        const statusSelect = document.getElementById('statusSelect');
        const fulfillmentInfo = document.getElementById('fulfillmentInfo');
        
        function updateColor(select) {
            select.classList.remove('text-warning', 'border-warning', 'text-success', 'border-success', 'text-danger', 'border-danger', 'text-primary', 'border-primary');
            if (select.value === 'Pending') select.classList.add('text-warning', 'border-warning');
            else if (select.value === 'Completed') select.classList.add('text-success', 'border-success');
            else if (select.value === 'Cancelled') select.classList.add('text-danger', 'border-danger');
            else if (select.value === 'Shipped') select.classList.add('text-primary', 'border-primary');
        }

        if (statusSelect) {
            updateColor(statusSelect);
            statusSelect.addEventListener('change', function() {
                updateColor(this);
                if (fulfillmentInfo) {
                    if(this.value === 'Shipped') {
                        fulfillmentInfo.classList.remove('d-none');
                    } else {
                        fulfillmentInfo.classList.add('d-none');
                    }
                }
            });
        }

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