<?php
session_start();
include 'db_conn.php';

// 1. 安全检查
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') { header("Location: LOGIN-REGISTER.php"); exit(); }
if (!isset($_GET['id'])) { header("Location: admin_customers.php"); exit(); }

$user_id = mysqli_real_escape_string($conn, $_GET['id']);

// --- 处理表单提交 (更新资料 & 密码) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // A. 更新基本资料
    if (isset($_POST['update_profile'])) {
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $phone = mysqli_real_escape_string($conn, $_POST['phone']);
        $status = mysqli_real_escape_string($conn, $_POST['account_status']);
        
        $sql = "UPDATE users SET email='$email', phone='$phone', account_status='$status' WHERE user_id='$user_id'";
        if($conn->query($sql)) {
            $_SESSION['swal'] = ['type' => 'success', 'title' => 'Success!', 'text' => 'Profile updated successfully!'];
        } else {
            $_SESSION['swal'] = ['type' => 'error', 'title' => 'Error!', 'text' => 'Error updating profile.'];
        }
        header("Location: admin_customer_details.php?id=$user_id");
        exit();
    }

    // B. 重置密码
    if (isset($_POST['reset_password'])) {
        $new_pass = $_POST['new_password'];
        
        // --- 🔒 后端强制规则验证 ---
        if (strlen($new_pass) < 12) {
            $_SESSION['swal'] = ['type' => 'error', 'title' => 'Weak Password', 'text' => 'Password must be at least 12 characters long!'];
        } elseif (!preg_match("/[0-9]/", $new_pass)) {
            $_SESSION['swal'] = ['type' => 'error', 'title' => 'Weak Password', 'text' => 'Password must include at least one number!'];
        } elseif (!preg_match("/[A-Z]/", $new_pass)) {
            $_SESSION['swal'] = ['type' => 'error', 'title' => 'Weak Password', 'text' => 'Password must include at least one UPPERCASE letter!'];
        } elseif (!preg_match("/[!@#$%^&*(),.?\":{}|<>]/", $new_pass)) {
            $_SESSION['swal'] = ['type' => 'error', 'title' => 'Weak Password', 'text' => 'Password must include at least one Special symbol!'];
        } else {
            // 规则通过，加密并保存
            $hashed = password_hash($new_pass, PASSWORD_DEFAULT);
            $conn->query("UPDATE users SET password='$hashed' WHERE user_id='$user_id'");
            $_SESSION['swal'] = ['type' => 'success', 'title' => 'Success!', 'text' => 'Password reset successfully!'];
        }
        header("Location: admin_customer_details.php?id=$user_id");
        exit();
    }

    // C. 删除地址
    if (isset($_POST['delete_address'])) {
        $addr_id = mysqli_real_escape_string($conn, $_POST['address_id']);
        if ($conn->query("DELETE FROM user_addresses WHERE address_id='$addr_id'")) {
            $_SESSION['swal'] = ['type' => 'success', 'title' => 'Deleted', 'text' => 'Address deleted successfully.'];
        }
        header("Location: admin_customer_details.php?id=$user_id");
        exit();
    }
}

// 2. 获取数据
$user = $conn->query("SELECT * FROM users WHERE user_id='$user_id'")->fetch_assoc();
$orders = $conn->query("SELECT * FROM orders WHERE user_id='$user_id' ORDER BY order_date DESC");
$cart = $conn->query("SELECT * FROM cart WHERE user_id='$user_id'");
$addresses = $conn->query("SELECT * FROM user_addresses WHERE user_id='$user_id'");

// 计算统计数据
$total_orders = $orders->num_rows;
// ✅ 修正：统计消费金额使用 grand_total
$total_spent_query = $conn->query("SELECT SUM(grand_total) as total FROM orders WHERE user_id='$user_id' AND status != 'Cancelled'");
$total_spent = $total_spent_query->fetch_assoc()['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Customer Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="admin_style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        .password-rule { font-size: 0.85rem; color: #6c757d; transition: all 0.3s; }
        .password-rule.valid { color: #198754; font-weight: 600; } 
        .password-rule.invalid { color: #dc3545; } 
        .password-rule i { margin-right: 5px; }
    </style>
</head>
<body class="p-4">
    
    <div class="container">
        <div class="mb-4">
            <a href="admin_customers.php" class="text-decoration-none text-secondary d-inline-block mb-3">
                <i class="bi bi-arrow-left"></i> Back to List
            </a>
            
            <div class="d-flex align-items-center border-bottom pb-3">
                <h3 class="fw-bold m-0">Customer Profile: <span class="text-primary"><?php echo $user['username']; ?></span></h3>
            </div>
        </div>
    <script>
        if (localStorage.getItem('sb|sidebar-toggle') === 'true') {
            document.body.classList.add('sb-sidenav-toggled');
        }
    </script>
    
        <div class="row">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm text-center p-4 mb-3">
                    <div class="bg-light rounded-circle mx-auto d-flex align-items-center justify-content-center mb-3" style="width: 100px; height: 100px;">
                        <?php if($user['profile_image'] && $user['profile_image'] != 'default_user.png'): ?>
                            <img src="img/<?php echo $user['profile_image']; ?>" class="rounded-circle" style="width: 100px; height: 100px; object-fit: cover;">
                        <?php else: ?>
                            <i class="bi bi-person-fill text-secondary" style="font-size: 3rem;"></i>
                        <?php endif; ?>
                    </div>
                    <h5 class="fw-bold"><?php echo $user['username']; ?></h5>
                    <p class="text-muted small">User ID: #<?php echo $user['user_id']; ?></p>
                    
                    <?php if($user['account_status'] == 'Active'): ?>
                        <div class="badge bg-success bg-opacity-10 text-success mb-3">Active</div>
                    <?php else: ?>
                        <div class="badge bg-danger bg-opacity-10 text-danger mb-3">Banned</div>
                    <?php endif; ?>
                    
                    <hr>
                    <div class="d-flex justify-content-between px-3">
                        <span class="text-muted">Orders</span>
                        <span class="fw-bold"><?php echo $total_orders; ?></span>
                    </div>
                    <div class="d-flex justify-content-between px-3 mt-2">
                        <span class="text-muted">Spent</span>
                        <span class="fw-bold text-success">RM <?php echo number_format($total_spent, 2); ?></span>
                    </div>
                </div>
            </div>

            <div class="col-md-9">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom-0 pt-3 ps-3">
                        <ul class="nav nav-tabs card-header-tabs" id="myTab" role="tablist">
                            <li class="nav-item"><a class="nav-link active" id="profile-tab" data-bs-toggle="tab" href="#profile" role="tab">Edit Profile</a></li>
                            <li class="nav-item"><a class="nav-link" id="orders-tab" data-bs-toggle="tab" href="#orders" role="tab">Order History</a></li>
                            <li class="nav-item"><a class="nav-link" id="cart-tab" data-bs-toggle="tab" href="#cart" role="tab">View Cart <span class="badge bg-danger rounded-pill"><?php echo $cart->num_rows; ?></span></a></li>
                            <li class="nav-item"><a class="nav-link" id="address-tab" data-bs-toggle="tab" href="#address" role="tab">Address Book</a></li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content" id="myTabContent">
                            
                            <div class="tab-pane fade show active" id="profile" role="tabpanel">
                                <form method="POST" class="row g-3">
                                    <h6 class="fw-bold text-muted">Account Information</h6>
                                    <div class="col-md-6">
                                        <label class="form-label">Email</label>
                                        <input type="email" name="email" class="form-control" value="<?php echo $user['email']; ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Phone</label>
                                        <input type="text" name="phone" class="form-control" value="<?php echo $user['phone']; ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Account Status</label>
                                        <select name="account_status" class="form-select">
                                            <option value="Active" <?php if($user['account_status']=='Active') echo 'selected'; ?>>Active</option>
                                            <option value="Banned" <?php if($user['account_status']=='Banned') echo 'selected'; ?>>Banned</option>
                                        </select>
                                    </div>
                                    <div class="col-12 text-end">
                                        <button type="submit" name="update_profile" class="btn btn-dark">Save Changes</button>
                                    </div>
                                </form>
                                <hr class="my-4">
                                
                                <form method="POST" class="row g-3">
                                    <h6 class="fw-bold text-danger">Security: Reset Password</h6>
                                    <div class="col-md-6">
                                        <input type="text" name="new_password" id="newPassInput" class="form-control" placeholder="Enter new password">
                                        <div class="mt-2 bg-light p-2 rounded border">
                                            <div class="password-rule" id="rule-length"><i class="bi bi-circle"></i> At least 12 characters</div>
                                            <div class="password-rule" id="rule-number"><i class="bi bi-circle"></i> At least 1 number</div>
                                            <div class="password-rule" id="rule-upper"><i class="bi bi-circle"></i> At least 1 uppercase letter</div>
                                            <div class="password-rule" id="rule-special"><i class="bi bi-circle"></i> At least 1 special symbol</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 align-self-start">
                                        <button type="submit" name="reset_password" class="btn btn-outline-danger w-100">Reset Password</button>
                                        <div class="form-text mt-2">
                                            Note: Entering a new password here will immediately log the user out of all devices.
                                        </div>
                                    </div>
                                </form>
                            </div>

                            <div class="tab-pane fade" id="orders" role="tabpanel">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr><th>Order ID</th><th>Date</th><th>Amount</th><th>Status</th><th>Action</th></tr>
                                    </thead>
                                    <tbody>
                                        <?php if($orders->num_rows > 0): while($o = $orders->fetch_assoc()): ?>
                                        <tr>
                                            <td>#<?php echo $o['order_id']; ?></td>
                                            <td><?php echo date("d M Y", strtotime($o['order_date'])); ?></td>
                                            <td>RM <?php echo number_format($o['grand_total'], 2); ?></td>
                                            <td><span class="badge bg-secondary"><?php echo $o['status']; ?></span></td>
                                            <td><a href="admin_order_details.php?id=<?php echo $o['order_id']; ?>" class="btn btn-sm btn-link">View</a></td>
                                        </tr>
                                        <?php endwhile; else: echo "<tr><td colspan='5'>No orders found.</td></tr>"; endif; ?>
                                    </tbody>
                                </table>
                            </div>

                            <div class="tab-pane fade" id="cart" role="tabpanel">
                                <div class="alert alert-info small"><i class="bi bi-info-circle"></i> Items currently in customer's cart (Abandoned Cart Analysis).</div>
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr><th>Product</th><th>Price</th><th>Qty</th><th>Subtotal</th></tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        // 购物车查询也需要关联 products 表获取 name 和 price
                                        // 这里的查询之前可能不完整，我稍微补全一下
                                        $cart_sql = "SELECT c.*, p.product_name, p.price FROM cart c JOIN products p ON c.product_id = p.product_id WHERE c.user_id='$user_id'";
                                        $cart_res = $conn->query($cart_sql);
                                        
                                        if($cart_res->num_rows > 0): while($c = $cart_res->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo $c['product_name']; ?></td>
                                            <td>RM <?php echo number_format($c['price'], 2); ?></td>
                                            <td>x <?php echo $c['quantity']; ?></td>
                                            <td class="fw-bold">RM <?php echo number_format($c['price'] * $c['quantity'], 2); ?></td>
                                        </tr>
                                        <?php endwhile; else: echo "<tr><td colspan='4'>Cart is empty.</td></tr>"; endif; ?>
                                    </tbody>
                                </table>
                            </div>

                            <div class="tab-pane fade" id="address" role="tabpanel">
                                <div class="d-flex justify-content-between mb-3">
                                    <h6 class="fw-bold">Saved Addresses</h6>
                                </div>
                                <div class="row g-3">
                                    <?php if($addresses->num_rows > 0): while($addr = $addresses->fetch_assoc()): ?>
                                    <div class="col-md-6">
                                        <div class="card h-100 bg-light border-0">
                                            <div class="card-body position-relative">
                                                <form method="POST" class="position-absolute top-0 end-0 mt-2 me-2">
                                                    <input type="hidden" name="address_id" value="<?php echo $addr['address_id']; ?>">
                                                    <button type="submit" name="delete_address" class="btn btn-sm text-danger" onclick="return confirm('Delete this address?');"><i class="bi bi-trash"></i></button>
                                                </form>
                                                <h6 class="fw-bold"><?php echo $addr['recipient_name']; ?></h6>
                                                <p class="small text-muted mb-1"><?php echo $addr['phone']; ?></p>
                                                <p class="small mb-0">
                                                    <?php echo $addr['address_line']; ?>,<br>
                                                    <?php echo $addr['city']; ?>, <?php echo $addr['postcode']; ?>, <?php echo $addr['state']; ?>
                                                </p>
                                                <?php if($addr['is_default']): ?>
                                                    <span class="badge bg-dark mt-2">Default</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endwhile; else: echo "<p class='text-muted'>No addresses saved.</p>"; endif; ?>
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

    document.addEventListener('DOMContentLoaded', function() {
        const passInput = document.getElementById('newPassInput');
        const ruleLength = document.getElementById('rule-length');
        const ruleNumber = document.getElementById('rule-number');
        const ruleUpper = document.getElementById('rule-upper');
        const ruleSpecial = document.getElementById('rule-special'); 

        if(passInput) {
            passInput.addEventListener('input', function() {
                const val = this.value;
                updateRule(ruleLength, val.length >= 12);
                updateRule(ruleNumber, /\d/.test(val));
                updateRule(ruleUpper, /[A-Z]/.test(val));
                const specialChars = /[!@#$%^&*(),.?":{}|<>]/;
                updateRule(ruleSpecial, specialChars.test(val));
            });
        }

        function updateRule(element, isValid) {
            if (!element) return; 
            const icon = element.querySelector('i');
            if (isValid) {
                element.classList.add('valid');
                element.classList.remove('invalid');
                icon.className = 'bi bi-check-circle-fill'; 
            } else {
                element.classList.remove('valid');
                if(document.getElementById('newPassInput').value.length > 0) {
                     element.classList.add('invalid');
                     icon.className = 'bi bi-x-circle-fill'; 
                } else {
                     element.classList.remove('invalid');
                     icon.className = 'bi bi-circle'; 
                }
            }
        }
    });
    </script>
</body>
</html>