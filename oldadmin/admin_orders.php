<?php
session_start();
include 'db_conn.php';

// --- 1. 安全检查 ---
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: LOGIN-REGISTER.php");
    exit();
}

// --- 2. 处理搜索和筛选 ---
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

// 构建 SQL 查询 (支持搜索 + 筛选 + 多商品合并)
$sql = "
    SELECT 
        order_id, 
        customer_name, 
        SUM(price) as total_price, 
        status, 
        MAX(order_date) as order_date,
        GROUP_CONCAT(product_name SEPARATOR ', ') as product_summary,
        COUNT(product_name) as item_count
    FROM orders 
    WHERE 1=1
";

// 如果有搜索关键词 (搜名字 或 订单ID)
if (!empty($search)) {
    $sql .= " AND (customer_name LIKE '%$search%' OR order_id LIKE '%$search%') ";
}

// 如果有状态筛选
if (!empty($status_filter)) {
    $sql .= " AND status = '$status_filter' ";
}

// 必须分组 (GROUP BY) 才能配合 GROUP_CONCAT 使用
$sql .= " GROUP BY order_id ORDER BY order_date DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders - Furniture Direct</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="admin_style.css">
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
                    <i class="bi bi-grid-1x2-fill me-3"></i>
                    <span class="sidebar-text">Dashboard</span> 
                </a>
                <a href="admin_orders.php" class="list-group-item list-group-item-action active">
                    <i class="bi bi-cart3 me-3"></i>
                    <span class="sidebar-text">Orders</span> 
                </a>
                <a href="#" class="list-group-item list-group-item-action">
                    <i class="bi bi-bag-check me-3"></i>
                    <span class="sidebar-text">Products</span> 
                </a>
                <a href="admin_customers.php" class="list-group-item list-group-item-action">
                    <i class="bi bi-people-fill me-3"></i>
                    <span class="sidebar-text">Customers</span> 
                </a>
                <a href="#" class="list-group-item list-group-item-action">
                    <i class="bi bi-graph-up-arrow me-3"></i>
                    <span class="sidebar-text">Reports</span> 
                </a>
                <a href="admin_profile.php" class="list-group-item list-group-item-action mt-5 border-top border-secondary pt-3">
                    <i class="bi bi-person-circle me-3"></i>
                    <span class="sidebar-text">Admin Profile</span> 
                </a>
            </div>
        </div>

        <div id="page-content-wrapper">
            <nav class="navbar navbar-light border-bottom px-4 py-3 bg-white">
                <div class="container-fluid p-0 d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <button class="btn btn-light btn-sm me-3 border" id="sidebarToggle"><i class="bi bi-list fs-5"></i></button>
                        <h5 class="m-0 d-none d-md-block text-secondary">Manage Orders</h5>
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

                <div class="row mb-4">
                    <div class="col-md-12">
                        <form method="GET" action="admin_orders.php" class="d-md-flex gap-2">
                            <div class="input-group mb-2 mb-md-0" style="max-width: 300px;">
                                <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-secondary"></i></span>
                                <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Search ID or Customer..." value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                            
                            <select name="status" class="form-select mb-2 mb-md-0" style="max-width: 150px;">
                                <option value="">All Status</option>
                                <option value="Pending" <?php if($status_filter == 'Pending') echo 'selected'; ?>>Pending</option>
                                <option value="Completed" <?php if($status_filter == 'Completed') echo 'selected'; ?>>Completed</option>
                                <option value="Cancelled" <?php if($status_filter == 'Cancelled') echo 'selected'; ?>>Cancelled</option>
                            </select>

                            <button type="submit" class="btn text-white" style="background-color: #343a40;">Filter</button>
                            
                            <?php if(!empty($search) || !empty($status_filter)): ?>
                                <a href="admin_orders.php" class="btn btn-light border">Reset</a>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>

                <div class="card custom-table-card bg-white">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="mb-0 fw-bold text-dark">Order List</h5>
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
                                    if ($result && $result->num_rows > 0) {
                                        while($row = $result->fetch_assoc()) {
                                            $badge_class = 'bg-secondary';
                                            if ($row['status'] == 'Completed') $badge_class = 'bg-success text-success';
                                            if ($row['status'] == 'Pending') $badge_class = 'bg-warning text-warning';
                                            if ($row['status'] == 'Shipped') $badge_class = 'bg-primary text-primary';
                                            if ($row['status'] == 'Cancelled') $badge_class = 'bg-danger text-danger';
                                            $date = date("M d, Y", strtotime($row['order_date']));

                                            $products = $row['product_summary'];
                                            $item_count = $row['item_count'];
                                            
                                            if ($item_count > 1) {
                                                $display_product = '<span class="badge bg-light text-dark border me-1">' . $item_count . ' Items</span> ' . mb_strimwidth($products, 0, 30, "...");
                                            } else {
                                                $display_product = $products;
                                            }
                                    ?>
                                    <tr>
                                        <td class="ps-4 fw-bold">#<?php echo str_pad($row['order_id'], 3, '0', STR_PAD_LEFT); ?></td>
                                        <td><?php echo $row['customer_name']; ?></td>
                                        <td class="text-secondary"><?php echo $display_product; ?></td>
                                        <td class="fw-bold">$<?php echo number_format($row['total_price'], 2); ?></td>       
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
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="admin_script.js"></script>
</body>
</html>