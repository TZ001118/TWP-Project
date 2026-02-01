<?php
session_start();
include 'db_conn.php';

// 1. 安全检查
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: LOGIN-REGISTER.php");
    exit();
}

// 2. 处理添加分类 (Add Category)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_category'])) {
    $cat_name = mysqli_real_escape_string($conn, $_POST['category_name']);
    $cat_status = $_POST['status'];
    
    // 处理图片上传
    $image_name = 'default_category.png'; // 默认图
    if (isset($_FILES['category_image']) && $_FILES['category_image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['category_image']['name'];
        $filetype = pathinfo($filename, PATHINFO_EXTENSION);
        if (in_array(strtolower($filetype), $allowed)) {
            $new_filename = "cat_" . time() . "." . $filetype;
            move_uploaded_file($_FILES['category_image']['tmp_name'], "img/" . $new_filename);
            $image_name = $new_filename;
        }
    }

    $sql = "INSERT INTO categories (category_name, category_image, status) VALUES ('$cat_name', '$image_name', '$cat_status')";
    
    if ($conn->query($sql)) {
        $_SESSION['swal'] = [
            'type' => 'success',
            'title' => 'Success!',
            'text' => 'Category added successfully!'
        ];
        header("Location: admin_categories.php");
        exit();
    } else {
        $_SESSION['swal'] = [
            'type' => 'error',
            'title' => 'Error!',
            'text' => 'Error adding category.'
        ];
        header("Location: admin_categories.php");
        exit();
    }
}

// 3. 处理删除分类 (Delete Category)
if (isset($_GET['delete_id'])) {
    $del_id = $_GET['delete_id'];
    if ($conn->query("DELETE FROM categories WHERE category_id='$del_id'")) {
        $_SESSION['swal'] = [
            'type' => 'success',
            'title' => 'Deleted!',
            'text' => 'Category has been deleted.'
        ];
    } else {
        $_SESSION['swal'] = [
            'type' => 'error',
            'title' => 'Error!',
            'text' => 'Could not delete category.'
        ];
    }
    header("Location: admin_categories.php");
    exit();
}

// 4. 获取所有分类
$result = $conn->query("SELECT * FROM categories ORDER BY category_id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Categories</title>
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
                <a href="admin_categories.php" class="list-group-item list-group-item-action active">
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
                        <h5 class="m-0 d-none d-md-block text-secondary">Manage Categories</h5>
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

                <div class="row">
                    <div class="col-md-4 mb-4">
                        <div class="card border-0 shadow-sm rounded-3">
                            <div class="card-header bg-white py-3 border-bottom">
                                <h5 class="m-0 fw-bold">Add New Category</h5>
                            </div>
                            <div class="card-body">
                                <form action="" method="POST" enctype="multipart/form-data">
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold">Category Name</label>
                                        <input type="text" name="category_name" class="form-control" placeholder="e.g. Sofa, Bed" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold">Status</label>
                                        <select name="status" class="form-select">
                                            <option value="Active">Active (Show)</option>
                                            <option value="Hidden">Hidden (Hide)</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold">Cover Image</label>
                                        <input type="file" name="category_image" class="form-control">
                                        <div class="form-text">Recommended size: 400x400px</div>
                                    </div>
                                    <button type="submit" name="add_category" class="btn btn-dark w-100">Add Category</button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-8">
                        <div class="card border-0 shadow-sm rounded-3">
                            <div class="card-header bg-white py-3 border-bottom">
                                <h5 class="m-0 fw-bold">Category List</h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="ps-4">Image</th>
                                                <th>Name</th>
                                                <th>Status</th>
                                                <th>Created At</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if ($result->num_rows > 0): ?>
                                                <?php while($row = $result->fetch_assoc()): ?>
                                                <tr>
                                                    <td class="ps-4">
                                                        <img src="img/<?php echo $row['category_image']; ?>" class="rounded border" style="width: 50px; height: 50px; object-fit: cover;" alt="img">
                                                    </td>
                                                    <td class="fw-bold"><?php echo $row['category_name']; ?></td>
                                                    <td>
                                                        <?php if($row['status'] == 'Active'): ?>
                                                            <span class="badge bg-success bg-opacity-10 text-success rounded-pill">Active</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill">Hidden</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="small text-muted"><?php echo date("d M Y", strtotime($row['created_at'])); ?></td>
                                                    <td>
                                                        <a href="#" 
                                                           class="btn btn-sm btn-outline-danger"
                                                           onclick="confirmDelete('admin_categories.php?delete_id=<?php echo $row['category_id']; ?>')">
                                                            <i class="bi bi-trash"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                                <?php endwhile; ?>
                                            <?php else: ?>
                                                <tr><td colspan="5" class="text-center py-4 text-muted">No categories yet.</td></tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
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
        // 显示 PHP Session 中的消息
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

        // 删除确认函数
        function confirmDelete(url) {
            Swal.fire({
                title: 'Are you sure?',
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