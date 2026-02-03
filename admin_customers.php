<?php
session_start();
include 'db_conn.php';

// --- 1. 安全检查 ---
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: LOGIN-REGISTER.php");
    exit();
}

// --- 2. 处理软删除 (Soft Delete) ---
if (isset($_GET['delete_id'])) {
    $delete_id = mysqli_real_escape_string($conn, $_GET['delete_id']);
    $sql = "UPDATE users SET is_deleted = 1 WHERE user_id = '$delete_id'";
    if ($conn->query($sql)) {
        $_SESSION['swal'] = [
            'type' => 'success',
            'title' => 'Deleted!',
            'text' => 'Customer soft-deleted successfully!'
        ];
    } else {
        $_SESSION['swal'] = [
            'type' => 'error',
            'title' => 'Error!',
            'text' => 'Could not delete customer.'
        ];
    }
    header("Location: admin_customers.php");
    exit();
}

// --- 3. 处理搜索与筛选 ---
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

// 查询条件
$sql = "SELECT * FROM users WHERE role = 'customer' AND is_deleted = 0";

if (!empty($search)) {
    $sql .= " AND (user_id LIKE '%$search%' OR username LIKE '%$search%' OR email LIKE '%$search%' OR phone LIKE '%$search%')";
}
if (!empty($status_filter)) {
    $sql .= " AND account_status = '$status_filter'";
}
$sql .= " ORDER BY created_at DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Customers</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="admin_style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
                <a href="admin_customers.php" class="list-group-item list-group-item-action active">
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
                        <h5 class="m-0 d-none d-md-block text-secondary">Customer Management</h5>
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
                
                <div class="card border-0 shadow-sm p-3 mb-4 rounded-3 bg-white">
                    <form method="GET" class="row g-2 align-items-center">
                        <div class="col-md-4">
                            <input type="text" name="search" class="form-control" placeholder="Search ID, Name, Email, Phone..." value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        <div class="col-md-3">
                            <select name="status" class="form-select">
                                <option value="">All Status</option>
                                <option value="Active" <?php if($status_filter=='Active') echo 'selected'; ?>>Active</option>
                                <option value="Banned" <?php if($status_filter=='Banned') echo 'selected'; ?>>Banned</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-dark w-100"><i class="bi bi-search"></i> Filter</button>
                        </div>
                        <?php if($search || $status_filter): ?>
                        <div class="col-md-1">
                            <a href="admin_customers.php" class="btn btn-light border w-100">Reset</a>
                        </div>
                        <?php endif; ?>
                    </form>
                </div>

                <div class="card custom-table-card bg-white">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">ID</th>
                                        <th>Customer</th>
                                        <th>Contact</th>
                                        <th>Reg Date</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td class="ps-4 fw-bold">#<?php echo $row['user_id']; ?></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="bg-light rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; overflow:hidden;">
                                                    <?php if($row['profile_image'] && $row['profile_image'] != 'default_user.png'): ?>
                                                        <img src="img/<?php echo $row['profile_image']; ?>" style="width:100%; height:100%; object-fit:cover;">
                                                    <?php else: ?>
                                                        <i class="bi bi-person-fill text-secondary fs-5"></i>
                                                    <?php endif; ?>
                                                </div>
                                                <div>
                                                    <div class="fw-bold"><?php echo $row['username']; ?></div>
                                                    <div class="small text-muted">ID: <?php echo $row['user_id']; ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="small"><i class="bi bi-envelope me-1"></i> <?php echo $row['email']; ?></div>
                                            <div class="small text-muted"><i class="bi bi-telephone me-1"></i> <?php echo $row['phone'] ?? '-'; ?></div>
                                        </td>
                                        <td class="small text-muted"><?php echo date("d M Y", strtotime($row['created_at'])); ?></td>
                                        <td>
                                            <?php if($row['account_status'] == 'Active'): ?>
                                                <span class="badge bg-success bg-opacity-10 text-success rounded-pill">Active</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill">Banned</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="admin_customer_details.php?id=<?php echo $row['user_id']; ?>" class="btn btn-sm btn-outline-primary me-1">View Details</a>
                                            
                                            <a href="#" 
                                               class="btn btn-sm btn-outline-danger"
                                               onclick="confirmDelete('admin_customers.php?delete_id=<?php echo $row['user_id']; ?>')">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
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

    <script>
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

        function confirmDelete(url) {
            Swal.fire({
                title: 'Soft Delete User?',
                text: "They will not be able to login.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#343a40',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = url;
                }
            })
        }
    </script>
</body>
</html>