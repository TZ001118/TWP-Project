<?php
session_start();
include 'db_conn.php';

// --- 1. 安全检查 ---
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: LOGIN-REGISTER.php");
    exit();
}

// --- 2. 分页与搜索配置 ---
$limit = 10; // ⭐ 每页显示 10 条
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

// --- 3. 构建查询条件 (Where Clause) ---
$where_sql = "WHERE 1=1";

if (!empty($search)) {
    // 搜索订单号或客户名
    $where_sql .= " AND (customer_name LIKE '%$search%' OR order_id LIKE '%$search%') ";
}
if (!empty($status_filter)) {
    $where_sql .= " AND status = '$status_filter' ";
}

// --- 4. 计算总条数 (用于分页按钮) ---
$count_sql = "SELECT COUNT(*) as total FROM orders $where_sql";
$count_result = $conn->query($count_sql);
$total_records = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_records / $limit);

// --- 5. ★★★ 修正后的查询逻辑 ★★★ ---
// 我们直接查 orders 表，然后用子查询去关联商品名，这样最稳
$sql = "
    SELECT 
        o.id,           -- 数据库自增ID (用于关联)
        o.order_id,     -- 显示用的订单号 (ORD-XXX)
        o.customer_name,
        o.grand_total,  -- ✅ 直接读取总价，不用 SUM
        o.status,
        o.order_date,
        -- 子查询：获取该订单下的所有商品名拼接
        (
            SELECT GROUP_CONCAT(p.product_name SEPARATOR ', ')
            FROM order_items oi
            JOIN products p ON oi.product_id = p.product_id
            WHERE oi.order_id = o.id
        ) as product_summary,
        -- 子查询：获取该订单有多少件商品
        (
            SELECT SUM(oi.quantity)
            FROM order_items oi
            WHERE oi.order_id = o.id
        ) as item_count
    FROM orders o
    $where_sql
    ORDER BY o.order_date DESC 
    LIMIT $offset, $limit
";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
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
                <a href="admin_dashboard.php" class="list-group-item list-group-item-action"><i class="bi bi-grid-1x2-fill me-3"></i><span class="sidebar-text">Dashboard</span></a>
                <a href="admin_orders.php" class="list-group-item list-group-item-action active"><i class="bi bi-cart3 me-3"></i><span class="sidebar-text">Orders</span></a>
                <a href="admin_categories.php" class="list-group-item list-group-item-action"><i class="bi bi-tags-fill me-3"></i><span class="sidebar-text">Categories</span></a>
                <a href="admin_products.php" class="list-group-item list-group-item-action"><i class="bi bi-bag-check-fill me-3"></i><span class="sidebar-text">Products</span></a>
                <a href="admin_customers.php" class="list-group-item list-group-item-action"><i class="bi bi-people-fill me-3"></i><span class="sidebar-text">Customers</span></a>
                <a href="admin_reports.php" class="list-group-item list-group-item-action"><i class="bi bi-graph-up-arrow me-3"></i><span class="sidebar-text">Reports</span></a>
                <a href="admin_profile.php" class="list-group-item list-group-item-action"><i class="bi bi-person-circle me-3"></i><span class="sidebar-text">Admin Profile</span></a>
            </div>
        </div>

        <div id="page-content-wrapper">
            <nav class="navbar navbar-light border-bottom px-4 py-3 bg-white">
                <div class="container-fluid"><button class="btn btn-light btn-sm me-3 border" id="sidebarToggle"><i class="bi bi-list fs-5"></i></button><h5 class="m-0 text-secondary">Manage Orders</h5></div>
            </nav>

            <div class="container-fluid p-4">
                
                <div class="card border-0 shadow-sm p-3 mb-4 rounded-3 bg-white">
                    <form method="GET" class="row g-2 align-items-center">
                        <div class="col-md-4">
                            <input type="text" name="search" class="form-control" placeholder="Search Order ID or Customer..." value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        <div class="col-md-3">
                            <select name="status" class="form-select">
                                <option value="">All Status</option>
                                <option value="Pending" <?php if($status_filter == 'Pending') echo 'selected'; ?>>Pending</option>
                                <option value="Completed" <?php if($status_filter == 'Completed') echo 'selected'; ?>>Completed</option>
                                <option value="Shipped" <?php if($status_filter == 'Shipped') echo 'selected'; ?>>Shipped</option>
                                <option value="Cancelled" <?php if($status_filter == 'Cancelled') echo 'selected'; ?>>Cancelled</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-dark w-100"><i class="bi bi-search"></i> Filter</button>
                        </div>
                        <?php if($search || $status_filter): ?>
                        <div class="col-md-1">
                            <a href="admin_orders.php" class="btn btn-light border w-100">Reset</a>
                        </div>
                        <?php endif; ?>
                    </form>
                </div>

                <div class="card custom-table-card bg-white">
                    <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold text-dark">Order List <span class="badge bg-light text-muted border"><?php echo $total_records; ?> Total</span></h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">Order ID</th>
                                        <th>Customer</th>
                                        <th>Products</th>
                                        <th>Total Price</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($result && $result->num_rows > 0): ?>
                                        <?php while($row = $result->fetch_assoc()): ?>
                                        <tr>
                                            <td class="ps-4 fw-bold">#<?php echo $row['order_id']; ?></td>
                                            <td><?php echo $row['customer_name']; ?></td>
                                            <td class="text-secondary">
                                                <?php 
                                                    // 显示商品摘要
                                                    if (!empty($row['product_summary'])) {
                                                        echo ($row['item_count'] > 1) 
                                                        ? '<span class="badge bg-light text-dark border me-1">' . $row['item_count'] . ' Items</span> ' . mb_strimwidth($row['product_summary'], 0, 30, "...")
                                                        : $row['product_summary'];
                                                    } else {
                                                        echo '<span class="text-muted small">No items</span>';
                                                    }
                                                ?>
                                            </td>
                                            <td class="fw-bold">RM <?php echo number_format($row['grand_total'], 2); ?></td>       
                                            <td class="text-muted small"><?php echo date("M d, Y", strtotime($row['order_date'])); ?></td>
                                            <td>
                                                <?php 
                                                    $s = $row['status'];
                                                    $cls = 'bg-secondary';
                                                    if ($s=='Completed') $cls='bg-success text-success';
                                                    if ($s=='Pending') $cls='bg-warning text-warning';
                                                    if ($s=='Shipped') $cls='bg-primary text-primary';
                                                    if ($s=='Cancelled') $cls='bg-danger text-danger';
                                                    echo "<span class='badge $cls bg-opacity-10 px-3 py-2 rounded-pill'>$s</span>";
                                                ?>
                                            </td>
                                            <td>
                                                <a href="admin_order_details.php?id=<?php echo $row['order_id']; ?>" class="btn btn-sm btn-outline-primary">View</a>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr><td colspan="7" class="text-center py-5 text-muted">No orders found.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <?php if($total_pages > 1): ?>
                    <div class="card-footer bg-white border-0 py-3">
                        <nav>
                            <ul class="pagination justify-content-center mb-0">
                                <li class="page-item <?php if($page <= 1) echo 'disabled'; ?>">
                                    <a class="page-link" href="?page=<?php echo $page-1; ?>&search=<?php echo $search; ?>&status=<?php echo $status_filter; ?>">Previous</a>
                                </li>

                                <?php for($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php if($page == $i) echo 'active'; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo $search; ?>&status=<?php echo $status_filter; ?>"><?php echo $i; ?></a>
                                </li>
                                <?php endfor; ?>

                                <li class="page-item <?php if($page >= $total_pages) echo 'disabled'; ?>">
                                    <a class="page-link" href="?page=<?php echo $page+1; ?>&search=<?php echo $search; ?>&status=<?php echo $status_filter; ?>">Next</a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                    <?php endif; ?>
                    </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="admin_script.js"></script>
</body>
</html>