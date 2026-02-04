<?php
session_start();
include 'db_conn.php';

// 1. 安全检查
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: LOGIN-REGISTER.php");
    exit();
}

// --- 2. 数据统计 (Data Aggregation) ---

// A. 关键指标卡片 (Top Cards)

// 1. Total Product Sales (纯销售额 - 不含运费/税)
$revenue_sql = "
    SELECT SUM(oi.quantity * oi.price) as total_revenue
    FROM order_items oi
    JOIN orders o ON oi.order_id = o.id
    WHERE o.status != 'Cancelled'
";
$revenue_res = $conn->query($revenue_sql);
$total_revenue = $revenue_res->fetch_assoc()['total_revenue'] ?? 0;

// 2. Total Cost of Sales (实际销售成本)
// 逻辑：卖出的商品数量 * 进货价
$cogs_sql = "
    SELECT SUM(oi.quantity * p.cost_price) as total_cost
    FROM order_items oi
    JOIN orders o ON oi.order_id = o.id
    JOIN products p ON oi.product_id = p.product_id
    WHERE o.status != 'Cancelled'
";
$cogs_res = $conn->query($cogs_sql);
$total_cost = $cogs_res->fetch_assoc()['total_cost'] ?? 0;

// 3. Total Net Profit (实际净利润)
$total_profit = $total_revenue - $total_cost;

// 4. Total Orders & Pending
$orders_res = $conn->query("SELECT COUNT(*) as total FROM orders");
$total_orders = $orders_res->fetch_assoc()['total'] ?? 0;
$pending_res = $conn->query("SELECT COUNT(*) as total FROM orders WHERE status = 'Pending'");
$pending_orders = $pending_res->fetch_assoc()['total'] ?? 0;


// B. 图表数据 1：订单状态分布 (右侧 Donut Chart - 保持不变)
$status_query = $conn->query("SELECT status, COUNT(*) as count FROM orders GROUP BY status");
$status_labels = [];
$status_data = [];
while ($row = $status_query->fetch_assoc()) {
    $status_labels[] = $row['status'];
    $status_data[] = $row['count'];
}

// C. 图表数据 2：财务趋势 (左侧 Line Chart - 改为 3条线)
// 按日期分组，计算每天的 Sales, Cost, Profit
$trend_query = $conn->query("
    SELECT 
        DATE(o.order_date) as date,
        SUM(oi.quantity * oi.price) as daily_revenue,
        SUM(oi.quantity * p.cost_price) as daily_cost
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    JOIN products p ON oi.product_id = p.product_id
    WHERE o.status != 'Cancelled'
    GROUP BY DATE(o.order_date)
    ORDER BY date DESC
    LIMIT 7
");

$trend_dates = [];
$trend_revenue_data = [];
$trend_cost_data = [];
$trend_profit_data = [];

$rows = [];
while($r = $trend_query->fetch_assoc()) { $rows[] = $r; }
$rows = array_reverse($rows); // 反转数组，日期从左到右

foreach ($rows as $row) {
    $trend_dates[] = date("M d", strtotime($row['date'])); 
    $rev = $row['daily_revenue'];
    $cost = $row['daily_cost'];
    $prof = $rev - $cost;

    $trend_revenue_data[] = $rev;
    $trend_cost_data[] = $cost;
    $trend_profit_data[] = $prof;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Business Reports</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="admin_style.css">
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                <a href="admin_orders.php" class="list-group-item list-group-item-action"><i class="bi bi-cart3 me-3"></i><span class="sidebar-text">Orders</span></a>
                <a href="admin_custom_requests.php" class="list-group-item list-group-item-action"><i class="bi bi-tools me-3"></i><span class="sidebar-text">Custom Requests</span> </a>
                <a href="admin_categories.php" class="list-group-item list-group-item-action"><i class="bi bi-tags-fill me-3"></i><span class="sidebar-text">Categories</span></a>
                <a href="admin_products.php" class="list-group-item list-group-item-action"><i class="bi bi-bag-check-fill me-3"></i><span class="sidebar-text">Products</span></a>
                <a href="admin_customers.php" class="list-group-item list-group-item-action"><i class="bi bi-people-fill me-3"></i><span class="sidebar-text">Customers</span></a>
                <a href="admin_reports.php" class="list-group-item list-group-item-action active"><i class="bi bi-graph-up-arrow me-3"></i><span class="sidebar-text">Reports</span></a>
                <a href="admin_profile.php" class="list-group-item list-group-item-action"><i class="bi bi-person-circle me-3"></i><span class="sidebar-text">Admin Profile</span></a>
            </div>
        </div>

        <div id="page-content-wrapper">
            <nav class="navbar navbar-light border-bottom px-4 py-3 bg-white">
                <div class="container-fluid p-0 d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <button class="btn btn-light btn-sm me-3 border" id="sidebarToggle"><i class="bi bi-list fs-5"></i></button>
                        <h5 class="m-0 d-none d-md-block text-secondary">Analytics & Reports</h5>
                    </div>
                    <ul class="navbar-nav ms-auto flex-row align-items-center">
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown">
                                <div class="bg-secondary rounded-circle text-white d-flex justify-content-center align-items-center me-2" style="width: 35px; height: 35px;">AD</div>
                                <span class="fw-bold d-none d-sm-block">Admin</span>
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
                
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4 mb-4">
                    
                    <div class="col">
                        <div class="card border-0 shadow-sm rounded-3 bg-custom-primary text-white h-100">
                            <div class="card-body p-3"> 
                                <h6 class="text-uppercase mb-2 text-white-50 small fw-bold">Total Product Sales</h6>
                                <h3 class="fw-bold mb-0">RM <?php echo number_format($total_revenue, 2); ?></h3>
                                <small class="text-white-50" style="font-size: 0.75rem;">(Excl. Tax & Shipping)</small>
                            </div>
                        </div>
                    </div>

                    <div class="col">
                        <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
                            <div class="card-body p-3">
                                <h6 class="text-uppercase mb-2 text-muted small fw-bold">Total Net Profit</h6>
                                <h3 class="fw-bold mb-0 text-success">RM <?php echo number_format($total_profit, 2); ?></h3>
                                <small class="text-muted" style="font-size: 0.75rem;">(Sales - Cost)</small>
                            </div>
                        </div>
                    </div>

                    <div class="col">
                        <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
                            <div class="card-body p-3">
                                <h6 class="text-uppercase mb-2 text-muted small fw-bold">Cost of Sales</h6>
                                <h3 class="fw-bold mb-0 text-danger">RM <?php echo number_format($total_cost, 2); ?></h3>
                                <small class="text-muted" style="font-size: 0.75rem;">Total Cost of Sold Items</small>
                            </div>
                        </div>
                    </div>

                    <div class="col">
                        <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
                            <div class="card-body p-3">
                                <h6 class="text-uppercase mb-2 text-muted small fw-bold">Total Orders</h6>
                                <div class="d-flex align-items-center justify-content-between">
                                    <h3 class="fw-bold mb-0 text-dark"><?php echo $total_orders; ?></h3>
                                    <?php if($pending_orders > 0): ?>
                                        <span class="badge bg-warning text-dark"><?php echo $pending_orders; ?> Pending</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="row g-4">
                    
                    <div class="col-lg-8">
                        <div class="card border-0 shadow-sm rounded-3 h-100">
                            <div class="card-header bg-white py-3 fw-bold">
                                Financial Trend (Sales vs Cost vs Profit)
                            </div>
                            <div class="card-body">
                                <canvas id="financeChart" height="120"></canvas>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm rounded-3 h-100">
                            <div class="card-header bg-white py-3 fw-bold">
                                Order Status Distribution
                            </div>
                            <div class="card-body">
                                <canvas id="statusChart" height="200"></canvas>
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
        // --- 1. 准备数据 (PHP -> JS) ---
        const trendLabels = <?php echo json_encode($trend_dates); ?>;
        
        // 折线图数据 (3条线)
        const dataSales = <?php echo json_encode($trend_revenue_data); ?>;
        const dataCost = <?php echo json_encode($trend_cost_data); ?>;
        const dataProfit = <?php echo json_encode($trend_profit_data); ?>;

        // 订单状态数据 (右侧图表)
        const statusLabels = <?php echo json_encode($status_labels); ?>;
        const statusData = <?php echo json_encode($status_data); ?>;
        const statusColors = statusLabels.map(label => {
            if (label === 'Pending') return '#ffc107';   
            if (label === 'Shipped') return '#0d6efd';   
            if (label === 'Completed') return '#198754'; 
            if (label === 'Cancelled') return '#dc3545'; 
            return '#6c757d'; 
        });

        // --- 2. 绘制左侧财务趋势图 (3 Lines) ---
        const ctx1 = document.getElementById('financeChart').getContext('2d');
        new Chart(ctx1, {
            type: 'line',
            data: {
                labels: trendLabels,
                datasets: [
                    {
                        label: 'Sales',
                        data: dataSales,
                        borderColor: '#20c997', // Teal (销售额)
                        backgroundColor: 'rgba(32, 201, 151, 0.1)',
                        borderWidth: 2,
                        tension: 0.3,
                        fill: false
                    },
                    {
                        label: 'Cost',
                        data: dataCost,
                        borderColor: '#dc3545', // Red (成本)
                        backgroundColor: 'rgba(220, 53, 69, 0.1)',
                        borderWidth: 2,
                        tension: 0.3,
                        fill: false
                    },
                    {
                        label: 'Net Profit',
                        data: dataProfit,
                        borderColor: '#198754', // Dark Green (净利润)
                        backgroundColor: 'rgba(25, 135, 84, 0.1)',
                        borderWidth: 2,
                        borderDash: [5, 5], // 虚线显示，区分度更高
                        tension: 0.3,
                        fill: false
                    }
                ]
            },
            options: {
                responsive: true,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                scales: {
                    y: { beginAtZero: true, grid: { color: '#f3f3f3' } },
                    x: { grid: { display: false } }
                }
            }
        });

        // --- 3. 绘制右侧订单状态图 (Pie Chart - 保持原样) ---
        const ctx2 = document.getElementById('statusChart').getContext('2d');
        new Chart(ctx2, {
            type: 'doughnut',
            data: {
                labels: statusLabels,
                datasets: [{
                    data: statusData,
                    backgroundColor: statusColors,
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                cutout: '70%',
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    </script>
</body>
</html>