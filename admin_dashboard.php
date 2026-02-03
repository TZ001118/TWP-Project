<?php
session_start();
include 'db_conn.php';

// --- 安全检查 ---
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: LOGIN-REGISTER.php");
    exit();
}

// --- 数据获取逻辑 ---

// 1. 统计数据
// A. 总订单数
$sql_orders = "SELECT COUNT(DISTINCT order_id) as total_orders FROM orders"; 
$result_orders = $conn->query($sql_orders);
$total_orders = $result_orders->fetch_assoc()['total_orders'];

// B. 总收入 (修正：统计 grand_total)
$sql_revenue = "SELECT SUM(grand_total) as total_revenue FROM orders WHERE status != 'Cancelled'";
$result_revenue = $conn->query($sql_revenue);
$total_revenue = $result_revenue->fetch_assoc()['total_revenue'] ?? 0;

// C. 总用户数
$sql_users = "SELECT COUNT(*) as total_users FROM users WHERE role='customer'";
$result_users = $conn->query($sql_users);
$total_users = $result_users->fetch_assoc()['total_users'];

// D. ★★★ 补上缺少的：待处理订单数 ★★★
$sql_pending = "SELECT COUNT(*) as pending_count FROM orders WHERE status = 'Pending'";
$result_pending = $conn->query($sql_pending);
$pending_orders = $result_pending->fetch_assoc()['pending_count'] ?? 0;


// 2. 获取最近订单 (关联 order_items 获取商品名，直接读取 grand_total)
$sql_recent = "
    SELECT 
        o.id,
        o.order_id, 
        o.customer_name, 
        o.grand_total,  -- ✅ 改用 grand_total
        o.status, 
        o.order_date,
        -- 子查询：获取商品名称拼接
        (
            SELECT GROUP_CONCAT(p.product_name SEPARATOR ', ')
            FROM order_items oi
            JOIN products p ON oi.product_id = p.product_id
            WHERE oi.order_id = o.id
        ) as product_summary,
        -- 子查询：获取商品数量
        (
            SELECT SUM(oi.quantity)
            FROM order_items oi
            WHERE oi.order_id = o.id
        ) as item_count
    FROM orders o
    ORDER BY o.order_date DESC 
    LIMIT 5
";
$recent_orders = $conn->query($sql_recent);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - DOMEA</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="admin_style.css">
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
                <a href="admin_dashboard.php" class="list-group-item list-group-item-action active">
                    <i class="bi bi-grid-1x2-fill me-3"></i><span class="sidebar-text">Dashboard</span> 
                </a>
                <a href="admin_orders.php" class="list-group-item list-group-item-action">
                    <i class="bi bi-cart3 me-3"></i><span class="sidebar-text">Orders</span> 
                </a>
                <a href="admin_custom_requests.php" class="list-group-item list-group-item-action">
                    <i class="bi bi-tools me-3"></i><span class="sidebar-text">Custom Requests</span> 
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
                        <h5 class="m-0 d-none d-md-block text-secondary">Admin Overview</h5>
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
                <div class="row g-3 g-md-4 mb-5">
                    <div class="col-12 col-md-6 col-lg-3">
                        <div class="card stat-card bg-custom-primary text-white h-100">
                            <div class="card-body">
                                <div><p class="mb-0 opacity-75">Total Orders</p><h3 class="fw-bold mb-0"><?php echo $total_orders; ?></h3></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6 col-lg-3">
                        <div class="card stat-card bg-custom-success text-white h-100">
                            <div class="card-body">
                                <div><p class="mb-0 opacity-75">Total Revenue</p><h3 class="fw-bold mb-0">RM <?php echo number_format($total_revenue, 2); ?></h3></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6 col-lg-3">
                        <div class="card stat-card bg-custom-warning text-white h-100">
                            <div class="card-body">
                                <div><p class="mb-0 opacity-75">Total Customers</p><h3 class="fw-bold mb-0"><?php echo $total_users; ?></h3></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6 col-lg-3">
                        <div class="card stat-card bg-custom-danger text-white h-100">
                            <div class="card-body">
                                <div><p class="mb-0 opacity-75">Pending Orders</p><h3 class="fw-bold mb-0"><?php echo $pending_orders; ?></h3></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card custom-table-card bg-white">
                    <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold text-dark">Recent Orders</h5>
                        </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">Order ID</th>
                                        <th>Customer</th>
                                        <th>Products (Summary)</th> 
                                        <th>Total Price</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if ($recent_orders && $recent_orders->num_rows > 0) {
                                        while($row = $recent_orders->fetch_assoc()) {
                                            $badge_class = 'bg-secondary';
                                            if ($row['status'] == 'Completed') $badge_class = 'bg-success text-success';
                                            if ($row['status'] == 'Pending') $badge_class = 'bg-warning text-warning';
                                            if ($row['status'] == 'Shipped') $badge_class = 'bg-primary text-primary';
                                            if ($row['status'] == 'Cancelled') $badge_class = 'bg-danger text-danger';
                                            $date = date("M d, Y", strtotime($row['order_date']));

                                            $products = $row['product_summary'];
                                            $item_count = $row['item_count'];
                                            
                                            // 智能显示商品名
                                            if (empty($products)) {
                                                $display_product = '<span class="text-muted small">Checking details...</span>';
                                            } elseif ($item_count > 1) {
                                                $display_product = '<span class="badge bg-light text-dark border me-1">' . $item_count . ' Items</span> ' . mb_strimwidth($products, 0, 30, "...");
                                            } else {
                                                $display_product = mb_strimwidth($products, 0, 40, "...");
                                            }
                                    ?>
                                    <tr>
                                        <td class="ps-4 fw-bold">#<?php echo $row['order_id']; ?></td>
                                        <td><?php echo $row['customer_name']; ?></td>
                                        <td class="text-secondary"><?php echo $display_product; ?></td>
                                        <td class="fw-bold">RM <?php echo number_format($row['grand_total'], 2); ?></td>       
                                        <td class="text-muted small"><?php echo $date; ?></td>
                                        <td><span class="badge <?php echo $badge_class; ?> bg-opacity-10 px-3 py-2 rounded-pill"><?php echo $row['status']; ?></span></td>
                                        <td>
                                            <a href="admin_order_details.php?id=<?php echo $row['order_id']; ?>" class="btn btn-sm btn-outline-primary">View</a>
                                        </td>
                                    </tr>
                                    <?php 
                                        } 
                                    } else {
                                        echo "<tr><td colspan='7' class='text-center py-4 text-muted'>No orders found.</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer bg-white border-0 py-3 text-center">
                        <a href="admin_orders.php" class="text-decoration-none text-muted small hover-link">View All Orders</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="admin_script.js"></script>
</body>
</html>