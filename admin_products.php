<?php
session_start();
include 'db_conn.php';

// 1. 安全检查
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: LOGIN-REGISTER.php");
    exit();
}

// 2. 处理添加产品 (Add Product)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_product'])) {
    // --- A. 接收基础数据 ---
    $name = mysqli_real_escape_string($conn, $_POST['product_name']);
    $desc = mysqli_real_escape_string($conn, $_POST['description']);
    $cat_id = $_POST['category_id'];
    $brand = mysqli_real_escape_string($conn, $_POST['brand']);
    $tags = mysqli_real_escape_string($conn, $_POST['tags']);
    $status = $_POST['status'];

    // --- B. 接收价格数据 ---
    $price = $_POST['price']; 
    $compare_price = !empty($_POST['compare_at_price']) ? $_POST['compare_at_price'] : 0;
    $cost_price = !empty($_POST['cost_price']) ? $_POST['cost_price'] : 0;

    // --- C. 接收库存与变体 ---
    $sku = mysqli_real_escape_string($conn, $_POST['sku']);
    $stock = $_POST['stock'];
    $low_stock = $_POST['low_stock_alert'];
    $v_color = mysqli_real_escape_string($conn, $_POST['variants_color']);
    $v_size = mysqli_real_escape_string($conn, $_POST['variants_size']);
    $v_material = mysqli_real_escape_string($conn, $_POST['variants_material']);

    // --- D. 接收物流数据 ---
    $weight = !empty($_POST['weight']) ? $_POST['weight'] : 0;
    $dimensions = mysqli_real_escape_string($conn, $_POST['dimensions']);

    // --- E. 图片上传处理 ---
    function uploadImage($fileInputName) {
        if (isset($_FILES[$fileInputName]) && $_FILES[$fileInputName]['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
            $filename = $_FILES[$fileInputName]['name'];
            $ext = pathinfo($filename, PATHINFO_EXTENSION);
            if (in_array(strtolower($ext), $allowed)) {
                $new_name = "prod_" . uniqid() . "." . $ext;
                move_uploaded_file($_FILES[$fileInputName]['tmp_name'], "img/" . $new_name);
                return $new_name;
            }
        }
        return null;
    }

    $main_img = uploadImage('product_image') ?? 'default_product.png';
    $img1 = uploadImage('image_gallery_1');
    $img2 = uploadImage('image_gallery_2');
    $img3 = uploadImage('image_gallery_3');

    // --- F. 写入数据库 ---
    $sql = "INSERT INTO products (
        product_name, description, category_id, brand, tags, status,
        price, compare_at_price, cost_price,
        sku, stock_quantity, low_stock_alert, variants_color, variants_size, variants_material,
        weight, dimensions,
        product_image, image_gallery_1, image_gallery_2, image_gallery_3
    ) VALUES (
        '$name', '$desc', '$cat_id', '$brand', '$tags', '$status',
        '$price', '$compare_price', '$cost_price',
        '$sku', '$stock', '$low_stock', '$v_color', '$v_size', '$v_material',
        '$weight', '$dimensions',
        '$main_img', '$img1', '$img2', '$img3'
    )";
    
    // 🔥 修改点：使用 Session + Header 跳转，而不是 echo script
    if ($conn->query($sql)) {
        $_SESSION['swal'] = [
            'type' => 'success',
            'title' => 'Success!',
            'text' => 'Product added successfully!'
        ];
    } else {
        $_SESSION['swal'] = [
            'type' => 'error',
            'title' => 'Error!',
            'text' => 'Database Error: ' . $conn->error
        ];
    }
    header("Location: admin_products.php");
    exit();
}

// 3. 处理删除
if (isset($_GET['delete_id'])) {
    $del_id = $_GET['delete_id'];
    
    // 🔥 修改点：删除也是用 Session + Header
    if($conn->query("DELETE FROM products WHERE product_id='$del_id'")) {
        $_SESSION['swal'] = [
            'type' => 'success',
            'title' => 'Deleted!',
            'text' => 'Product has been deleted.'
        ];
    } else {
        $_SESSION['swal'] = [
            'type' => 'error',
            'title' => 'Error!',
            'text' => 'Could not delete product.'
        ];
    }
    header("Location: admin_products.php");
    exit();
}

$limit = 10; // 每页显示 10 个产品
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// --- 4. 计算总产品数 ---
$total_result = $conn->query("SELECT COUNT(*) as total FROM products");
$total_products = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_products / $limit);

// --- 5. 获取分类 (用于下拉菜单) ---
$categories = $conn->query("SELECT * FROM categories WHERE status='Active'");

// --- 6. 获取当前页产品 (带 LIMIT) ---
$products_sql = "SELECT p.*, c.category_name 
                 FROM products p 
                 LEFT JOIN categories c ON p.category_id = c.category_id 
                 ORDER BY p.product_id DESC 
                 LIMIT $offset, $limit";
$products_result = $conn->query($products_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Products</title>
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
                <a href="admin_products.php" class="list-group-item list-group-item-action active">
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
                        <h5 class="m-0 d-none d-md-block text-secondary">Manage Products</h5>
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

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="fw-bold m-0">Products</h4>
                    <button class="btn btn-dark" type="button" data-bs-toggle="collapse" data-bs-target="#addProductForm">
                        <i class="bi bi-plus-lg me-1"></i> Add New Product
                    </button>
                </div>

                <div class="collapse mb-4" id="addProductForm">
                    <form action="" method="POST" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-lg-8">
                                <div class="card border-0 shadow-sm rounded-3 mb-4">
                                    <div class="card-header bg-white py-3 fw-bold"><i class="bi bi-info-circle me-2"></i>Basic Information</div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label class="form-label small fw-bold">Product Name</label>
                                            <input type="text" name="product_name" class="form-control" required placeholder="e.g. Luxury King Size Bed">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label small fw-bold">Description (Rich Text)</label>
                                            <textarea name="description" class="form-control" rows="5" placeholder="Describe material, design concept..."></textarea>
                                            <div class="form-text">Supports HTML or basic text formatting.</div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label small fw-bold">Brand / Collection</label>
                                                <input type="text" name="brand" class="form-control" placeholder="e.g. IKEA Series">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label small fw-bold">Tags</label>
                                                <input type="text" name="tags" class="form-control" placeholder="e.g. New Arrival, Best Seller">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="card border-0 shadow-sm rounded-3 mb-4">
                                    <div class="card-header bg-white py-3 fw-bold"><i class="bi bi-images me-2"></i>Media & 3D</div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label class="form-label small fw-bold">Main Image (Thumbnail)</label>
                                            <input type="file" name="product_image" class="form-control">
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4 mb-2">
                                                <label class="form-label small text-muted">Gallery 1 (Detail)</label>
                                                <input type="file" name="image_gallery_1" class="form-control form-control-sm">
                                            </div>
                                            <div class="col-md-4 mb-2">
                                                <label class="form-label small text-muted">Gallery 2 (Scene)</label>
                                                <input type="file" name="image_gallery_2" class="form-control form-control-sm">
                                            </div>
                                            <div class="col-md-4 mb-2">
                                                <label class="form-label small text-muted">Gallery 3 (3D/Other)</label>
                                                <input type="file" name="image_gallery_3" class="form-control form-control-sm">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="card border-0 shadow-sm rounded-3 mb-4">
                                    <div class="card-header bg-white py-3 fw-bold"><i class="bi bi-box-seam me-2"></i>Inventory & Variants</div>
                                    <div class="card-body">
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label class="form-label small fw-bold">SKU (Stock Keeping Unit)</label>
                                                <input type="text" name="sku" class="form-control" placeholder="e.g. BED-K-001">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label small fw-bold">Stock Qty</label>
                                                <input type="number" name="stock" class="form-control" required value="0">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label small fw-bold text-danger">Low Stock Alert</label>
                                                <input type="number" name="low_stock_alert" class="form-control" value="5">
                                            </div>
                                        </div>
                                        <hr>
                                        <p class="small text-muted mb-2">Variants Options (Separate by comma)</p>
                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label small fw-bold">Colors</label>
                                                <input type="text" name="variants_color" class="form-control" placeholder="Grey, Blue, Red">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label small fw-bold">Sizes</label>
                                                <input type="text" name="variants_size" class="form-control" placeholder="King, Queen, Single">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label small fw-bold">Material</label>
                                                <input type="text" name="variants_material" class="form-control" placeholder="Leather, Fabric">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="card border-0 shadow-sm rounded-3 mb-4">
                                    <div class="card-header bg-white py-3 fw-bold"><i class="bi bi-truck me-2"></i>Shipping</div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label small fw-bold">Weight (kg)</label>
                                                <input type="number" step="0.01" name="weight" class="form-control" placeholder="0.00">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label small fw-bold">Dimensions (L x W x H)</label>
                                                <input type="text" name="dimensions" class="form-control" placeholder="e.g. 200 x 180 x 50 cm">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-4">
                                <div class="card border-0 shadow-sm rounded-3 mb-4">
                                    <div class="card-header bg-white py-3 fw-bold">Organization</div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label class="form-label small fw-bold">Status</label>
                                            <select name="status" class="form-select">
                                                <option value="Active">Active (Publish)</option>
                                                <option value="Draft" selected>Draft (Hidden)</option>
                                                <option value="Archived">Archived</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label small fw-bold">Category</label>
                                            <select name="category_id" class="form-select" required>
                                                <option value="">Select Category...</option>
                                                <?php while($cat = $categories->fetch_assoc()): ?>
                                                    <option value="<?php echo $cat['category_id']; ?>"><?php echo $cat['category_name']; ?></option>
                                                <?php endwhile; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="card border-0 shadow-sm rounded-3 mb-4">
                                    <div class="card-header bg-white py-3 fw-bold"><i class="bi bi-tag me-2"></i>Pricing</div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label class="form-label small fw-bold">Selling Price ($)</label>
                                            <input type="number" step="0.01" name="price" class="form-control" required placeholder="0.00" min="0">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label small fw-bold">Compare at Price ($)</label>
                                            <input type="number" step="0.01" name="compare_at_price" class="form-control" placeholder="0.00" min="0">
                                            <div class="form-text">Original price (for discount display).</div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label small fw-bold text-muted">Cost Price ($) <span class="badge bg-secondary">Admin Only</span></label>
                                            <input type="number" step="0.01" name="cost_price" class="form-control bg-light" placeholder="0.00">
                                        </div>
                                    </div>
                                </div>

                                <div class="d-grid gap-2">
                                    <button type="submit" name="add_product" class="btn btn-dark btn-lg">Save Product</button>
                                    <button type="button" class="btn btn-light border" data-bs-toggle="collapse" data-bs-target="#addProductForm">Cancel</button>
                                </div>

                            </div>
                        </div>
                    </form>
                </div>

                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h5 class="m-0 fw-bold">All Products</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">Product</th>
                                        <th>SKU / Category</th>
                                        <th>Status</th>
                                        <th>Inventory</th>
                                        <th>Price</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($products_result->num_rows > 0): ?>
                                        <?php while($prod = $products_result->fetch_assoc()): ?>
                                        <tr>
                                            <td class="ps-4">
                                                <div class="d-flex align-items-center">
                                                    <img src="img/<?php echo $prod['product_image']; ?>" class="rounded border me-3" style="width: 50px; height: 50px; object-fit: cover;">
                                                    <div>
                                                        <div class="fw-bold text-truncate" style="max-width: 200px;"><?php echo $prod['product_name']; ?></div>
                                                        <div class="small text-muted"><?php echo $prod['brand']; ?></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="small fw-bold"><?php echo $prod['sku'] ? $prod['sku'] : '-'; ?></div>
                                                <div class="small text-muted"><?php echo $prod['category_name']; ?></div>
                                            </td>
                                            <td>
                                                <?php 
                                                $status_class = 'bg-secondary';
                                                if($prod['status'] == 'Active') $status_class = 'bg-success text-success';
                                                if($prod['status'] == 'Draft') $status_class = 'bg-warning text-warning';
                                                ?>
                                                <span class="badge <?php echo $status_class; ?> bg-opacity-10 rounded-pill"><?php echo $prod['status']; ?></span>
                                            </td>
                                            <td>
                                                <div class="<?php echo ($prod['stock_quantity'] <= $prod['low_stock_alert']) ? 'text-danger fw-bold' : ''; ?>">
                                                    <?php echo $prod['stock_quantity']; ?> in stock
                                                </div>
                                                <?php if(!empty($prod['variants_color'])): ?>
                                                    <div class="small text-muted text-truncate" style="max-width: 150px;">
                                                        <i class="bi bi-palette"></i> <?php echo $prod['variants_color']; ?>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="fw-bold">$<?php echo number_format($prod['price'], 2); ?></div>
                                                <?php if($prod['compare_at_price'] > 0): ?>
                                                    <div class="small text-decoration-line-through text-muted">$<?php echo number_format($prod['compare_at_price'], 2); ?></div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="admin_product_edit.php?id=<?php echo $prod['product_id']; ?>" 
                                                   class="btn btn-sm btn-light border me-1">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                
                                                <button onclick="confirmDelete('admin_products.php?delete_id=<?php echo $prod['product_id']; ?>')" 
                                                   class="btn btn-sm btn-outline-danger">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr><td colspan="6" class="text-center py-5 text-muted">No products found. Click "Add New Product" to start.</td></tr>
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
        // 检测 Session 消息并显示 Toast
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
        document.querySelector('form').addEventListener('submit', function(e) {
            const priceInput = this.querySelector('input[name="price"]');
            const compareInput = this.querySelector('input[name="compare_at_price"]');
            
            const price = parseFloat(priceInput.value) || 0;
            const comparePrice = parseFloat(compareInput.value) || 0;

            if (price < 0) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Price',
                    text: 'Selling price cannot be negative.',
                    confirmButtonColor: '#343a40'
                });
                priceInput.focus();
            } 
            else if (comparePrice > 0 && comparePrice <= price) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Pricing Conflict',
                    text: 'The original price (Compare at) should be higher than the selling price to show a discount.',
                    confirmButtonColor: '#343a40'
                });
                compareInput.focus();
            }
        });

        // Get both input elements
        const priceInput = document.querySelector('input[name="price"]');
        const compareInput = document.querySelector('input[name="compare_at_price"]');

        // Update the min attribute of compare_at_price whenever price changes
        priceInput.addEventListener('input', function() {
            const currentPrice = this.value;
            if (currentPrice && currentPrice >= 0) {
                // Dynamically set the min attribute
                compareInput.setAttribute('min', currentPrice);
            }
        });

        // 删除确认弹窗
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