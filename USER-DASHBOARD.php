<?php
session_start();
include 'db_conn.php';

// 1. 检查是否登录
if (!isset($_SESSION['user_id'])) {
    header("Location: LOGIN-REGISTER.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// 2. 从数据库获取【用户个人资料】
$sql_user = "SELECT * FROM users WHERE user_id = '$user_id'";
$result_user = $conn->query($sql_user);

if ($result_user->num_rows > 0) {
    $user = $result_user->fetch_assoc();
} else {
    echo "User not found!";
    exit;
}

// 3. 从数据库获取【订单历史】
$sql_orders = "SELECT * FROM orders WHERE user_id = '$user_id' ORDER BY order_date DESC";
$result_orders = $conn->query($sql_orders);

// 4. 获取购买过的商品 (用于写评论)
$sql_items = "SELECT DISTINCT p.product_id, p.product_name, p.product_image, p.price, r.review_id 
              FROM order_items oi
              JOIN orders o ON oi.order_id = o.id
              JOIN products p ON oi.product_id = p.product_id
              LEFT JOIN reviews r ON (r.product_id = p.product_id AND r.user_id = $user_id)
              WHERE o.user_id = $user_id
              ORDER BY o.order_date DESC";
$result_items = $conn->query($sql_items);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard | FurnitureDirect</title>
    <link rel="stylesheet" href="style.css">

    <style>
        /* --- 仪表板专属样式 --- */
        .dashboard-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
            display: flex;
            gap: 30px;
            flex-wrap: wrap;
        }

        /* 左侧：用户资料卡 */
        .profile-sidebar {
            flex: 1;
            min-width: 300px;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            height: fit-content;
            text-align: center;
        }

        .profile-img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 15px;
            border: 3px solid #f0f0f0;
        }

        .user-name { font-size: 1.5rem; color: #2c3e50; margin-bottom: 5px; font-weight: bold; }
        .user-email { color: #777; margin-bottom: 20px; font-size: 0.9rem; }

        .info-list { text-align: left; margin-top: 20px; border-top: 1px solid #eee; padding-top: 20px; }
        .info-item { margin-bottom: 15px; }
        .info-label { font-weight: bold; color: #333; display: block; font-size: 0.85rem; }
        .info-value { color: #555; word-wrap: break-word; }

        .btn-edit {
            display: inline-block; width: 100%; padding: 10px 0;
            background-color: #2c3e50; color: white; text-decoration: none;
            border-radius: 5px; margin-top: 10px; transition: 0.3s;
            border: none; cursor: pointer; text-align: center;
        }
        .btn-edit:hover { background-color: #20c997; }

        /* 右侧：主内容区域 */
        .main-content {
            flex: 2;
            display: flex;
            flex-direction: column;
            gap: 30px; 
        }

        .content-card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }

        .section-title {
            font-size: 1.5rem; color: #2c3e50; margin-bottom: 20px;
            padding-bottom: 10px; border-bottom: 2px solid #f0f0f0;
        }

        /* 订单表格样式 */
        .order-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .order-table th { text-align: left; padding: 15px; background-color: #f8f9fa; color: #555; font-weight: 600; }
        .order-table td { padding: 15px; border-bottom: 1px solid #eee; color: #666; }

        .status-badge {
            padding: 5px 10px; border-radius: 20px; font-size: 0.8rem; font-weight: bold; display: inline-block;
        }
        .status-Pending { background-color: #fff3cd; color: #856404; }
        .status-Paid { background-color: #d1e7dd; color: #0f5132; }
        .status-Shipped { background-color: #cff4fc; color: #055160; }
        .status-Cancelled { background-color: #f8d7da; color: #721c24; }
        .status-Completed { background-color: #d1e7dd; color: #0f5132; }

        .btn-view { color: #20c997; text-decoration: none; font-weight: bold; font-size: 0.9rem; }

        /* --- 待评价商品列表样式 --- */
        .item-row { display: flex; align-items: center; justify-content: space-between; padding: 15px 0; border-bottom: 1px solid #eee; }
        .item-info { display: flex; align-items: center; gap: 15px; }
        .item-img { width: 60px; height: 60px; border-radius: 5px; object-fit: cover; border: 1px solid #eee; }
        .btn-review { padding: 8px 15px; border: 1px solid #20c997; color: #20c997; background: white; border-radius: 4px; cursor: pointer; transition: 0.2s; text-decoration: none; font-size: 0.9rem; }
        .btn-review:hover { background: #20c997; color: white; }
        .btn-reviewed { background: #eee; color: #999; border-color: #eee; cursor: default; }
        .btn-reviewed:hover { background: #eee; color: #999; }

        /* Modal Styles */
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); align-items: center; justify-content: center; }
        .modal-content { 
            background-color: white; 
            padding: 30px; 
            border-radius: 10px; 
            width: 500px; 
            position: relative; 
            animation: fadeIn 0.3s;
            /* 修复弹窗过高问题 */
            max-height: 90vh; 
            overflow-y: auto; 
        }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(-20px); } to { opacity: 1; transform: translateY(0); } }
        .close-btn { position: absolute; top: 15px; right: 20px; font-size: 24px; cursor: pointer; color: #aaa; }
        
        /* Form Elements inside Modal */
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; color: #333; font-weight: bold; }
        .form-group input, .form-group textarea, .form-group select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; font-family: inherit;}
        .btn-save { width: 100%; padding: 12px; background-color: #20c997; color: white; border: none; border-radius: 5px; font-size: 1rem; cursor: pointer; }

        /* Star Rating in Modal */
        .star-rating { display: inline-flex; flex-direction: row-reverse; gap: 5px; margin: 10px 0 20px; }
        .star-rating input { display: none; }
        .star-rating label { font-size: 30px; color: #ddd; cursor: pointer; }
        .star-rating input:checked ~ label, .star-rating label:hover, .star-rating label:hover ~ label { color: #f1c40f; }
    </style>
</head>
<body>

    <?php include 'navbar.php'; ?> 

    <div class="page-content">
        
        <div id="editProfileModal" class="modal">
            <div class="modal-content">
                <span class="close-btn" onclick="closeModal('editProfileModal')">&times;</span>
                <h2 style="text-align:center; margin-bottom:20px; color:#2c3e50;">Edit Profile</h2>
                
                <form action="update_profile.php" method="POST">
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled style="background:#f9f9f9; color:#999;">
                    </div>
                    
                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="text" name="phone" value="<?php echo isset($user['phone']) ? htmlspecialchars($user['phone']) : ''; ?>" placeholder="Enter phone number">
                    </div>

                    <hr style="margin: 20px 0; border: 0; border-top: 1px solid #eee;">
                    <p style="margin-bottom: 15px; font-weight: bold; color: #2c3e50;">Shipping Address Details</p>

                    <div class="form-group">
                        <label>Street Address 1 <span style="color:red">*</span></label>
                        <input type="text" name="street_1" value="<?php echo htmlspecialchars($user['street_1'] ?? ''); ?>" placeholder="House number and street name" required>
                    </div>

                    <div class="form-group">
                        <label>Street Address 2</label>
                        <input type="text" name="street_2" value="<?php echo htmlspecialchars($user['street_2'] ?? ''); ?>" placeholder="Apartment, suite, unit, etc.">
                    </div>

                    <div class="form-group">
                        <label>Town / City <span style="color:red">*</span></label>
                        <input type="text" name="city" value="<?php echo htmlspecialchars($user['city'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label>State <span style="color:red">*</span></label>
                        <select name="state" required>
                            <option value="">Select State...</option>
                            <?php 
                            $states = ["Johor", "Kedah", "Kelantan", "Melaka", "Negeri Sembilan", "Pahang", "Penang", "Perak", "Perlis", "Sabah", "Sarawak", "Selangor", "Terengganu", "Kuala Lumpur", "Putrajaya", "Labuan"];
                            foreach($states as $st) {
                                $selected = ($user['state'] ?? '') === $st ? 'selected' : '';
                                echo "<option value=\"$st\" $selected>$st</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Postcode <span style="color:red">*</span></label>
                        <input type="text" name="postcode" value="<?php echo htmlspecialchars($user['postcode'] ?? ''); ?>" required>
                    </div>

                    <button type="submit" class="btn-save" style="margin-top:20px;">Save Changes</button>
                </form>
            </div>
        </div>

        <div id="reviewModal" class="modal">
            <div class="modal-content">
                <span class="close-btn" onclick="closeModal('reviewModal')">&times;</span>
                <h2 style="text-align:center; margin-bottom:5px;">Write a Review</h2>
                <p id="review-product-name" style="text-align:center; color:#777; margin-bottom:20px;">Product Name</p>
                
                <form action="submit_review.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="product_id" id="review-product-id">
                    
                    <div style="text-align:center;">
                        <div class="star-rating">
                            <input type="radio" id="star5" name="rating" value="5" required/><label for="star5">★</label>
                            <input type="radio" id="star4" name="rating" value="4" /><label for="star4">★</label>
                            <input type="radio" id="star3" name="rating" value="3" /><label for="star3">★</label>
                            <input type="radio" id="star2" name="rating" value="2" /><label for="star2">★</label>
                            <input type="radio" id="star1" name="rating" value="1" /><label for="star1">★</label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Your Review:</label>
                        <textarea name="comment" rows="4" placeholder="How was the quality?" required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Upload Photo (Optional):</label>
                        <input type="file" name="review_image" accept="image/*">
                    </div>
                    
                    <button type="submit" class="btn-save">Submit Review</button>
                </form>
            </div>
        </div>
        
        <div class="dashboard-container">

            <aside class="profile-sidebar">
                <img src="img/default-user.png" alt="User" class="profile-img" onerror="this.src='https://cdn-icons-png.flaticon.com/512/149/149071.png'">
                <div class="user-name"><?php echo htmlspecialchars($user['username']); ?></div>
                <div class="user-email"><?php echo htmlspecialchars($user['email']); ?></div>
                
                <div class="info-list">
                    <div class="info-item">
                        <span class="info-label">User ID</span>
                        <span class="info-value">#<?php echo $user['user_id']; ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Phone Number</span>
                        <span class="info-value"><?php echo !empty($user['phone']) ? htmlspecialchars($user['phone']) : '<span style="color:#ccc;">Not set</span>'; ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Shipping Address</span>
                        <span class="info-value">
                            <?php 
                            if (!empty($user['street_1'])) {
                                echo htmlspecialchars($user['street_1']);
                                if(!empty($user['street_2'])) echo ", " . htmlspecialchars($user['street_2']);
                                echo "<br>" . htmlspecialchars($user['postcode']) . " " . htmlspecialchars($user['city']);
                                echo "<br>" . htmlspecialchars($user['state']);
                            } else {
                                echo !empty($user['address']) ? nl2br(htmlspecialchars($user['address'])) : '<span style="color:#ccc;">Not set</span>'; 
                            }
                            ?>
                        </span>
                    </div>
                </div>

                <button onclick="openModal('editProfileModal')" class="btn-edit">Edit Profile</button>
                <a href="logout.php" class="btn-edit" style="background-color: #e74c3c; margin-top: 10px; display:block;">Logout</a>
            </aside>

            <div class="main-content">

                <div class="content-card">
                    <h2 class="section-title">Order History</h2>
                    <table class="order-table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Date</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result_orders->num_rows > 0): ?>
                                <?php while($order = $result_orders->fetch_assoc()): ?>
                                    <tr>
                                        <td>#<?php echo htmlspecialchars($order['order_id']); ?></td>
                                        <td><?php echo date('d M Y', strtotime($order['order_date'])); ?></td>
                                        <td style="font-weight:bold;">RM <?php echo number_format($order['grand_total'], 2); ?></td>
                                        <td>
                                            <?php 
                                                $status = isset($order['status']) ? $order['status'] : 'Pending'; 
                                                $class = 'status-' . $status; 
                                            ?>
                                            <span class="status-badge <?php echo $class; ?>"><?php echo $status; ?></span>
                                        </td>
                                        <td><a href="ORDER-DETAILS.php?id=<?php echo $order['id']; ?>" class="btn-view">View</a></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" style="text-align:center; padding:30px; color:#999;">
                                        You haven't placed any orders yet.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="content-card">
                    <h2 class="section-title">Purchased Items & Reviews</h2>
                    
                    <?php if ($result_items && $result_items->num_rows > 0): ?>
                        <?php while($item = $result_items->fetch_assoc()): ?>
                            <div class="item-row">
                                <div class="item-info">
                                    <img src="img/<?php echo $item['product_image']; ?>" class="item-img" alt="Product">
                                    <div>
                                        <div style="font-weight:bold; color:#333;"><?php echo htmlspecialchars($item['product_name']); ?></div>
                                        <div style="font-size:0.9rem; color:#999;">RM <?php echo number_format($item['price'], 2); ?></div>
                                    </div>
                                </div>
                                <div>
                                    <?php if ($item['review_id']): ?>
                                        <span class="btn-review btn-reviewed">✔ Reviewed</span>
                                    <?php else: ?>
                                        <button class="btn-review" onclick="openReviewModal(<?php echo $item['product_id']; ?>, '<?php echo addslashes($item['product_name']); ?>')">Write Review</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p style="color:#999; padding:20px 0;">No purchased items found. Place an order to write reviews!</p>
                    <?php endif; ?>
                </div>

            </div>

        </div>

    </div>

    <script>
        function openModal(id) { document.getElementById(id).style.display = 'flex'; }
        function closeModal(id) { document.getElementById(id).style.display = 'none'; }
        
        function openReviewModal(productId, productName) {
            document.getElementById('review-product-id').value = productId;
            document.getElementById('review-product-name').innerText = productName;
            openModal('reviewModal');
        }

        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        }

        window.onscroll = function() {
            const nav = document.querySelector('.navbar');
            if (nav) {
                if (window.scrollY > 20) { 
                    nav.classList.add('collapsed'); 
                } else { 
                    nav.classList.remove('collapsed'); 
                }
            }
        };
    </script>
</body>
</html>