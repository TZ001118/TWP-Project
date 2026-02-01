<?php
session_start();
include 'db_conn.php';

// 1. 安全检查
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: LOGIN-REGISTER.php");
    exit();
}

// 2. 获取要编辑的产品 ID
if (!isset($_GET['id'])) {
    header("Location: admin_products.php");
    exit();
}
$product_id = $_GET['id'];

// 3. 处理更新请求 (Update Product)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_product'])) {
    // A. 接收基础数据
    $name = mysqli_real_escape_string($conn, $_POST['product_name']);
    $desc = mysqli_real_escape_string($conn, $_POST['description']);
    $cat_id = $_POST['category_id'];
    $brand = mysqli_real_escape_string($conn, $_POST['brand']);
    $tags = mysqli_real_escape_string($conn, $_POST['tags']);
    $status = $_POST['status'];
    
    // --- B. Receive Price & Inventory ---
    $price = floatval($_POST['price']);
    $compare_price = !empty($_POST['compare_at_price']) ? floatval($_POST['compare_at_price']) : 0;

    // NEW: Server-side Pricing Validation
    if ($price < 0) {
        $_SESSION['swal'] = [
            'type' => 'error', 
            'title' => 'Invalid Price', 
            'text' => 'Selling price cannot be negative.'
        ];
        header("Location: admin_product_edit.php?id=$product_id");
        exit();
    }

    if ($compare_price > 0 && $compare_price <= $price) {
        $_SESSION['swal'] = [
            'type' => 'error', 
            'title' => 'Pricing Conflict', 
            'text' => 'Compare price must be greater than the selling price.'
        ];
        header("Location: admin_product_edit.php?id=$product_id");
        exit();
    }

    // B. 接收价格与库存
    $price = $_POST['price'];
    $compare_price = !empty($_POST['compare_at_price']) ? $_POST['compare_at_price'] : 0;
    $cost_price = !empty($_POST['cost_price']) ? $_POST['cost_price'] : 0;
    $sku = mysqli_real_escape_string($conn, $_POST['sku']);
    $stock = $_POST['stock'];
    $low_stock = $_POST['low_stock_alert'];
    
    // C. 变体与物流
    $v_color = mysqli_real_escape_string($conn, $_POST['variants_color']);
    $v_size = mysqli_real_escape_string($conn, $_POST['variants_size']);
    $v_material = mysqli_real_escape_string($conn, $_POST['variants_material']);
    $weight = !empty($_POST['weight']) ? $_POST['weight'] : 0;
    $dimensions = mysqli_real_escape_string($conn, $_POST['dimensions']);

    // D. 图片上传处理 (如果没有上传新图，就保持原样)
    function processImageUpdate($fileInputName, $currentImageName) {
        if (isset($_FILES[$fileInputName]) && $_FILES[$fileInputName]['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
            $filename = $_FILES[$fileInputName]['name'];
            $ext = pathinfo($filename, PATHINFO_EXTENSION);
            if (in_array(strtolower($ext), $allowed)) {
                $new_name = "prod_" . uniqid() . "." . $ext;
                move_uploaded_file($_FILES[$fileInputName]['tmp_name'], "img/" . $new_name);
                return $new_name; // 返回新图片名
            }
        }
        return $currentImageName; // 返回旧图片名
    }

    // 获取当前数据库里的旧图片名（隐藏域传过来的）
    $main_img = processImageUpdate('product_image', $_POST['old_main_img']);
    $img1 = processImageUpdate('image_gallery_1', $_POST['old_img1']);
    $img2 = processImageUpdate('image_gallery_2', $_POST['old_img2']);
    $img3 = processImageUpdate('image_gallery_3', $_POST['old_img3']);

    // E. 更新数据库
    $sql = "UPDATE products SET 
            product_name='$name', description='$desc', category_id='$cat_id', brand='$brand', tags='$tags', status='$status',
            price='$price', compare_at_price='$compare_price', cost_price='$cost_price',
            sku='$sku', stock_quantity='$stock', low_stock_alert='$low_stock',
            variants_color='$v_color', variants_size='$v_size', variants_material='$v_material',
            weight='$weight', dimensions='$dimensions',
            product_image='$main_img', image_gallery_1='$img1', image_gallery_2='$img2', image_gallery_3='$img3'
            WHERE product_id='$product_id'";
    
    if ($conn->query($sql)) {
        $_SESSION['swal'] = ['type' => 'success', 'title' => 'Success!', 'text' => 'Product updated successfully!'];
        } else {
            $_SESSION['swal'] = ['type' => 'error', 'title' => 'Error!', 'text' => 'Error updating product: ' . $conn->error];
        }
        
        // 关键修改：保存后留在当前页面 (Stay on Edit Page)
        header("Location: admin_product_edit.php?id=$product_id"); 
        exit();
    }

    // 4. 读取当前产品数据
    $prod_query = $conn->query("SELECT * FROM products WHERE product_id='$product_id'");
    if ($prod_query->num_rows == 0) {
        header("Location: admin_products.php");
        exit();
    }
    $prod = $prod_query->fetch_assoc();

    // 5. 读取分类列表 (用于下拉菜单)
    $categories = $conn->query("SELECT * FROM categories WHERE status='Active'");
    ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Product - #<?php echo $product_id; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
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
                <a href="admin_dashboard.php" class="list-group-item list-group-item-action"><i class="bi bi-grid-1x2-fill me-3"></i><span class="sidebar-text">Dashboard</span></a>
                <a href="admin_orders.php" class="list-group-item list-group-item-action"><i class="bi bi-cart3 me-3"></i><span class="sidebar-text">Orders</span></a>
                <a href="admin_categories.php" class="list-group-item list-group-item-action"><i class="bi bi-tags-fill me-3"></i><span class="sidebar-text">Categories</span></a>
                <a href="admin_products.php" class="list-group-item list-group-item-action active"><i class="bi bi-bag-check-fill me-3"></i><span class="sidebar-text">Products</span></a>
                <a href="admin_customers.php" class="list-group-item list-group-item-action"><i class="bi bi-people-fill me-3"></i><span class="sidebar-text">Customers</span></a>
                <a href="admin_reports.php" class="list-group-item list-group-item-action"><i class="bi bi-graph-up-arrow me-3"></i><span class="sidebar-text">Reports</span></a>
                <a href="admin_profile.php" class="list-group-item list-group-item-action"><i class="bi bi-person-circle me-3"></i><span class="sidebar-text">Admin Profile</span></a>
            </div>
        </div>

        <div id="page-content-wrapper">
            <div class="container-fluid p-4">
                
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="fw-bold m-0">Edit Product: <?php echo $prod['product_name']; ?></h4>
                    <a href="admin_products.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Back to List</a>
                </div>

                <form action="" method="POST" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="card border-0 shadow-sm rounded-3 mb-4">
                                <div class="card-header bg-white py-3 fw-bold">Basic Information</div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold">Product Name</label>
                                        <input type="text" name="product_name" class="form-control" value="<?php echo $prod['product_name']; ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold">Description</label>
                                        <textarea name="description" class="form-control" rows="5"><?php echo $prod['description']; ?></textarea>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label small fw-bold">Brand</label>
                                            <input type="text" name="brand" class="form-control" value="<?php echo $prod['brand']; ?>">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label small fw-bold">Tags</label>
                                            <input type="text" name="tags" class="form-control" value="<?php echo $prod['tags']; ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card border-0 shadow-sm rounded-3 mb-4">
                                <div class="card-header bg-white py-3 fw-bold">Media</div>
                                <div class="card-body">
                                    <div class="row align-items-center mb-3">
                                        <div class="col-md-2">
                                            <img src="img/<?php echo $prod['product_image']; ?>" class="img-thumbnail" width="100%">
                                        </div>
                                        <div class="col-md-10">
                                            <label class="form-label small fw-bold">Main Image (Change?)</label>
                                            <input type="file" name="product_image" class="form-control">
                                            <input type="hidden" name="old_main_img" value="<?php echo $prod['product_image']; ?>">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4 mb-2">
                                            <div class="d-flex align-items-center mb-2">
                                                <?php if($prod['image_gallery_1']) echo '<img src="img/'.$prod['image_gallery_1'].'" width="40" class="me-2 rounded border">'; ?>
                                                <input type="file" name="image_gallery_1" class="form-control form-control-sm">
                                            </div>
                                            <input type="hidden" name="old_img1" value="<?php echo $prod['image_gallery_1']; ?>">
                                        </div>
                                        <div class="col-md-4 mb-2">
                                            <div class="d-flex align-items-center mb-2">
                                                <?php if($prod['image_gallery_2']) echo '<img src="img/'.$prod['image_gallery_2'].'" width="40" class="me-2 rounded border">'; ?>
                                                <input type="file" name="image_gallery_2" class="form-control form-control-sm">
                                            </div>
                                            <input type="hidden" name="old_img2" value="<?php echo $prod['image_gallery_2']; ?>">
                                        </div>
                                        <div class="col-md-4 mb-2">
                                            <div class="d-flex align-items-center mb-2">
                                                <?php if($prod['image_gallery_3']) echo '<img src="img/'.$prod['image_gallery_3'].'" width="40" class="me-2 rounded border">'; ?>
                                                <input type="file" name="image_gallery_3" class="form-control form-control-sm">
                                            </div>
                                            <input type="hidden" name="old_img3" value="<?php echo $prod['image_gallery_3']; ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card border-0 shadow-sm rounded-3 mb-4">
                                <div class="card-header bg-white py-3 fw-bold">Inventory & Variants</div>
                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold">SKU</label>
                                            <input type="text" name="sku" class="form-control" value="<?php echo $prod['sku']; ?>">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label small fw-bold">Stock</label>
                                            <input type="number" name="stock" class="form-control" value="<?php echo $prod['stock_quantity']; ?>">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label small fw-bold text-danger">Low Alert</label>
                                            <input type="number" name="low_stock_alert" class="form-control" value="<?php echo $prod['low_stock_alert']; ?>">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label small fw-bold">Colors</label>
                                            <input type="text" name="variants_color" class="form-control" value="<?php echo $prod['variants_color']; ?>">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label small fw-bold">Sizes</label>
                                            <input type="text" name="variants_size" class="form-control" value="<?php echo $prod['variants_size']; ?>">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label small fw-bold">Material</label>
                                            <input type="text" name="variants_material" class="form-control" value="<?php echo $prod['variants_material']; ?>">
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
                                            <option value="Active" <?php if($prod['status']=='Active') echo 'selected'; ?>>Active</option>
                                            <option value="Draft" <?php if($prod['status']=='Draft') echo 'selected'; ?>>Draft</option>
                                            <option value="Archived" <?php if($prod['status']=='Archived') echo 'selected'; ?>>Archived</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold">Category</label>
                                        <select name="category_id" class="form-select" required>
                                            <option value="">Select Category...</option>
                                            <?php while($cat = $categories->fetch_assoc()): ?>
                                                <option value="<?php echo $cat['category_id']; ?>" 
                                                    <?php if($cat['category_id'] == $prod['category_id']) echo 'selected'; ?>>
                                                    <?php echo $cat['category_name']; ?>
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="card border-0 shadow-sm rounded-3 mb-4">
                                <div class="card-header bg-white py-3 fw-bold">Pricing</div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold">Selling Price ($)</label>
                                        <input type="number" step="0.01" name="price" class="form-control" value="<?php echo $prod['price']; ?>" required min="0">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold">Compare at Price ($)</label>
                                        <input type="number" step="0.01" name="compare_at_price" class="form-control" value="<?php echo $prod['compare_at_price']; ?>" min="0">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold text-muted">Cost Price</label>
                                        <input type="number" step="0.01" name="cost_price" class="form-control bg-light" value="<?php echo $prod['cost_price']; ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card border-0 shadow-sm rounded-3 mb-4">
                                <div class="card-header bg-white py-3 fw-bold">Shipping</div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold">Weight (kg)</label>
                                        <input type="number" step="0.01" name="weight" class="form-control" value="<?php echo $prod['weight']; ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold">Dimensions</label>
                                        <input type="text" name="dimensions" class="form-control" value="<?php echo $prod['dimensions']; ?>">
                                    </div>
                                </div>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" name="update_product" class="btn btn-dark btn-lg">Save Changes</button>
                                <a href="admin_products.php" class="btn btn-light border">Cancel</a>
                            </div>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="admin_script.js"></script>
    <script>
        // 检测 PHP Session 里是否有 SweetAlert 消息
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
            // 弹完后清除 Session，防止刷新页面一直弹
            <?php unset($_SESSION['swal']); ?>
        <?php endif; ?>
        const priceInputEdit = document.querySelector('input[name="price"]');
        const compareInputEdit = document.querySelector('input[name="compare_at_price"]');

        // Sync minimum requirement dynamically
        priceInputEdit.addEventListener('input', function() {
            const currentPrice = this.value;
            if (currentPrice && currentPrice >= 0) {
                compareInputEdit.setAttribute('min', currentPrice);
            }
        });
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
    </script>
</body>
</html>