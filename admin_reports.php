<?php
session_start();
include 'db_conn.php';

// 1. 安全检查
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: LOGIN-REGISTER.php");
    exit();
}

// --- 2. 数据统计 (Data Aggregation) ---

// A. 关键指标卡片 (Key Metrics)
// ✅ 修正：统计总收入要用 grand_total
$revenue_res = $conn->query("SELECT SUM(grand_total) as total FROM orders WHERE status != 'Cancelled'");
$total_revenue = $revenue_res->fetch_assoc()['total'] ?? 0;

// 总订单数
$orders_res = $conn->query("SELECT COUNT(*) as total FROM orders");
$total_orders = $orders_res->fetch_assoc()['total'] ?? 0;

// 待处理订单
$pending_res = $conn->query("SELECT COUNT(*) as total FROM orders WHERE status = 'Pending'");
$pending_orders = $pending_res->fetch_assoc()['total'] ?? 0;

// B. 图表数据 1：订单状态分布 (Pie Chart)
$status_query = $conn->query("SELECT status, COUNT(*) as count FROM orders GROUP BY status");
$status_labels = [];
$status_data = [];
while ($row = $status_query->fetch_assoc()) {
    $status_labels[] = $row['status'];
    $status_data[] = $row['count'];
}

// C. 图表数据 2：最近 7 天销售趋势 (Line Chart)
// ✅ 修正：趋势图也要用 grand_total
$trend_query = $conn->query("
    SELECT DATE(order_date) as date, SUM(grand_total) as daily_total 
    FROM orders 
    WHERE status != 'Cancelled' 
    GROUP BY DATE(order_date) 
    ORDER BY date DESC 
    LIMIT 7
");

$trend_dates = [];
$trend_sales = [];
// 因为查出来是倒序的（最新的在前），我们需要反转数组让图表从左到右显示
$rows = [];
while($r = $trend_query->fetch_assoc()) { $rows[] = $r; }
$rows = array_reverse($rows);

foreach ($rows as $row) {
    $trend_dates[] = date("M d", strtotime($row['date'])); // 例如: Feb 01
    $trend_sales[] = $row['daily_total'];
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

                <div class="row g-4 mb-4">
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm rounded-3 bg-custom-primary text-white h-100">
                            <div class="card-body p-4">
                                <h6 class="text-uppercase mb-2 text-white-50 small fw-bold">Total Revenue</h6>
                                <h2 class="fw-bold mb-0">RM <?php echo number_format($total_revenue, 2); ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
                            <div class="card-body p-4">
                                <h6 class="text-uppercase mb-2 text-muted small fw-bold">Total Orders</h6>
                                <h2 class="fw-bold mb-0"><?php echo $total_orders; ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
                            <div class="card-body p-4">
                                <h6 class="text-uppercase mb-2 text-muted small fw-bold">Pending Action</h6>
                                <div class="d-flex align-items-center justify-content-between">
                                    <h2 class="fw-bold mb-0 text-warning"><?php echo $pending_orders; ?></h2>
                                    <span class="small text-muted">Orders to ship</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-lg-8">
                        <div class="card border-0 shadow-sm rounded-3 h-100">
                            <div class="card-header bg-white py-3 fw-bold">
                                Sales Trend (Last 7 Active Days)
                            </div>
                            <div class="card-body">
                                <canvas id="salesChart" height="120"></canvas>
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
        // 1. 准备数据
        const trendLabels = <?php echo json_encode($trend_dates); ?>;
        const trendData = <?php echo json_encode($trend_sales); ?>;
        
        const statusLabels = <?php echo json_encode($status_labels); ?>;
        const statusData = <?php echo json_encode($status_data); ?>;

        const statusColors = statusLabels.map(label => {
            if (label === 'Pending') return '#ffc107';   
            if (label === 'Shipped') return '#0d6efd';   
            if (label === 'Completed') return '#198754'; 
            if (label === 'Cancelled') return '#dc3545'; 
            return '#6c757d'; 
        });

        // 2. 绘制销售趋势折线图
        const ctx1 = document.getElementById('salesChart').getContext('2d');
        new Chart(ctx1, {
            type: 'line',
            data: {
                labels: trendLabels,
                datasets: [{
                    label: 'Sales (RM)', // 修改标签单位
                    data: trendData,
                    borderColor: '#99d5c5',
                    backgroundColor: 'rgba(153, 213, 197, 0.2)',
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true } }
            }
        });

        // 3. 绘制状态甜甜圈图
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