<?php
session_start();
include 'db_conn.php';

// 1. 安全检查
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: LOGIN-REGISTER.php");
    exit();
}

// 2. 处理状态更新
if (isset($_GET['mark_replied'])) {
    $id = intval($_GET['mark_replied']);
    $conn->query("UPDATE custom_inquiries SET status = 'Replied' WHERE inquiry_id = $id");
    $_SESSION['swal'] = ['type'=>'success', 'title'=>'Updated', 'text'=>'Marked as Replied'];
    header("Location: admin_custom_requests.php");
    exit();
}

// 3. 处理删除
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    $conn->query("DELETE FROM custom_inquiries WHERE inquiry_id = $id");
    $_SESSION['swal'] = ['type'=>'success', 'title'=>'Deleted', 'text'=>'Inquiry deleted'];
    header("Location: admin_custom_requests.php");
    exit();
}

$result = $conn->query("SELECT * FROM custom_inquiries ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Custom Requests | Admin</title>
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
                <a href="admin_custom_requests.php" class="list-group-item list-group-item-action active">
                    <i class="bi bi-tools me-3"></i><span class="sidebar-text">Custom Requests</span> 
                </a>
                <a href="admin_categories.php" class="list-group-item list-group-item-action">
                    <i class="bi bi-tags-fill me-3"></i><span class="sidebar-text">Categories</span> 
                </a>
                <a href="admin_products.php" class="list-group-item list-group-item-action">
                    <i class="bi bi-bag-check-fill me-3"></i><span class="sidebar-text">Products</span> 
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
                        <h5 class="m-0 d-none d-md-block text-secondary">Custom Requests</h5>
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
                
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h5 class="m-0 fw-bold">Request List</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">Date</th>
                                        <th>Customer</th>
                                        <th>Subject</th>
                                        <th>Image</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if($result->num_rows > 0): while($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td class="ps-4 small text-muted"><?php echo date("d M Y", strtotime($row['created_at'])); ?></td>
                                        <td>
                                            <div class="fw-bold"><?php echo htmlspecialchars($row['name']); ?></div>
                                            <div class="small text-muted"><?php echo htmlspecialchars($row['phone']); ?></div>
                                        </td>
                                        <td><?php echo htmlspecialchars($row['subject']); ?></td>
                                        <td>
                                            <?php if($row['reference_image']): ?>
                                                <a href="img/custom_uploads/<?php echo $row['reference_image']; ?>" target="_blank">
                                                    <img src="img/custom_uploads/<?php echo $row['reference_image']; ?>" class="rounded border" style="width:50px; height:50px; object-fit:cover;">
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted small">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if($row['status'] == 'Pending'): ?>
                                                <span class="badge bg-warning text-dark rounded-pill">Pending</span>
                                            <?php else: ?>
                                                <span class="badge bg-success bg-opacity-10 text-success rounded-pill">Replied</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary me-1" data-bs-toggle="modal" data-bs-target="#viewModal<?php echo $row['inquiry_id']; ?>">
                                                View
                                            </button>
                                            
                                            <a href="#" 
                                               class="btn btn-sm btn-outline-danger"
                                               onclick="confirmDelete('admin_custom_requests.php?delete_id=<?php echo $row['inquiry_id']; ?>')">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </td>
                                    </tr>

                                    <div class="modal fade" id="viewModal<?php echo $row['inquiry_id']; ?>" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Inquiry from <?php echo htmlspecialchars($row['name']); ?></h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="mb-3">
                                                                <label class="small fw-bold text-muted">Subject</label>
                                                                <div><?php echo htmlspecialchars($row['subject']); ?></div>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="small fw-bold text-muted">Message</label>
                                                                <div class="bg-light p-3 rounded"><?php echo nl2br(htmlspecialchars($row['message'])); ?></div>
                                                            </div>
                                                            <hr>
                                                            <p><i class="bi bi-envelope me-2"></i> <?php echo $row['email']; ?></p>
                                                            <p><i class="bi bi-telephone me-2"></i> <?php echo $row['phone']; ?></p>
                                                        </div>
                                                        <div class="col-md-6 text-center">
                                                            <label class="small fw-bold text-muted d-block mb-2">Reference Image</label>
                                                            <?php if($row['reference_image']): ?>
                                                                <img src="img/custom_uploads/<?php echo $row['reference_image']; ?>" class="img-fluid rounded border shadow-sm">
                                                            <?php else: ?>
                                                                <div class="alert alert-secondary py-5">No image uploaded</div>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer bg-light">
                                                    <a href="mailto:<?php echo $row['email']; ?>?subject=Re: Custom Order - <?php echo urlencode($row['subject']); ?>" class="btn btn-dark">
                                                        <i class="bi bi-envelope-fill me-1"></i> Reply via Email
                                                    </a>
                                                    
                                                    <?php if($row['status'] == 'Pending'): ?>
                                                        <a href="?mark_replied=<?php echo $row['inquiry_id']; ?>" class="btn btn-success text-white">Mark as Replied</a>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <?php endwhile; else: ?>
                                        <tr><td colspan="6" class="text-center py-5 text-muted">No custom requests yet.</td></tr>
                                    <?php endif; ?>
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
                toast: true, position: 'top-end', showConfirmButton: false, timer: 3000
            });
            <?php unset($_SESSION['swal']); ?>
        <?php endif; ?>

        function confirmDelete(url) {
            Swal.fire({
                title: 'Delete Inquiry?',
                text: "You won't be able to revert this!",
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