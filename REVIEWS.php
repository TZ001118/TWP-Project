<?php 
$current_page = 'reviews';
session_start();
include 'db_conn.php';

// 获取当前用户 ID (用于判断是否点赞过)
$current_user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;

// --- 筛选逻辑 ---
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$where_clause = "WHERE r.is_visible = 1"; // 默认条件

if ($filter == 'images') {
    $where_clause .= " AND r.image_url IS NOT NULL AND r.image_url != ''";
} elseif ($filter == 'verified') {
    // 简单筛选
}
?> 

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Reviews | FurnitureDirect</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body { background-color: #f8f9fa; font-family: 'Segoe UI', sans-serif; }
        
        .page-content { 
            padding-top: 40px; 
            padding-bottom: 60px; 
        }

        .page-header { text-align: center; margin-bottom: 30px; }
        .page-header h1 { color: #2c3e50; font-size: 2.2rem; font-weight: 700; margin-bottom: 10px; }

        /* 筛选栏 */
        .filter-bar { display: flex; justify-content: center; gap: 15px; margin-bottom: 40px; flex-wrap: wrap; }
        
        .filter-btn {
            background: #fff; border: 1px solid #ddd; padding: 10px 25px; border-radius: 20px;
            color: #555; cursor: pointer; font-weight: 600; text-decoration: none;
            display: flex; align-items: center; gap: 8px; transition: 0.2s; font-size: 0.9rem;
        }
        .filter-btn:hover { background: #f1f1f1; transform: translateY(-2px); }
        .filter-btn.active { background: #2c3e50; color: white; border-color: #2c3e50; box-shadow: 0 4px 10px rgba(44,62,80,0.3); }

        /* --- ★★★ 布局容器 ★★★ --- */
        .reviews-grid {
            max-width: 1300px;
            margin: 0 auto;
            /* Masonry.js 需要 relative 定位 */
            position: relative; 
        }

        /* --- ★★★ 卡片样式 (Masonry Item) ★★★ --- */
        .review-card {
            background: white; border-radius: 12px; overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05); transition: transform 0.2s;
            
            /* 关键：宽度设置为 23.5% (约等于4列)，Masonry 会自动计算位置 */
            width: 23.5%; 
            margin-bottom: 20px; /* 底部间距 */
            float: left; /* 必须浮动 */
        }
        
        /* 响应式宽度调整 */
        @media (max-width: 1200px) { .review-card { width: 31.5%; } } /* 3列 */
        @media (max-width: 900px) { .review-card { width: 48%; } }    /* 2列 */
        @media (max-width: 600px) { .review-card { width: 100%; } }   /* 1列 */

        .review-card:hover { transform: translateY(-5px); box-shadow: 0 10px 30px rgba(0,0,0,0.1); z-index: 2; }

        /* 图片样式：高度自适应，完整显示 */
        .card-img-top {
            width: 100%; 
            height: auto; /* 自动高度，不裁切 */
            display: block;
            background-color: #f9f9f9; 
            border-bottom: 1px solid #eee; 
            cursor: zoom-in;
        }
        
        .no-img-placeholder { height: 0; }

        .card-body { padding: 20px; display: flex; flex-direction: column; }

        .user-info { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
        .user-name { font-weight: 700; color: #333; font-size: 0.95rem; }
        .verified-badge { 
            background: #e6fffa; color: #20c997; padding: 3px 8px; 
            border-radius: 4px; font-size: 0.75rem; font-weight: bold; 
        }

        .stars { color: #f1c40f; margin-bottom: 10px; font-size: 0.9rem; letter-spacing: 2px; }
        
        .review-text {
            color: #555; font-size: 0.95rem; line-height: 1.5; margin-bottom: 15px;
            word-wrap: break-word; /* 防止长文字撑开 */
        }

        .review-footer {
            display: flex; justify-content: space-between; align-items: flex-end;
            margin-top: 15px; padding-top: 15px; border-top: 1px solid #f0f0f0;
        }

        .product-meta { font-size: 0.8rem; color: #999; line-height: 1.4; }
        
        .like-section { display: flex; align-items: center; gap: 5px; cursor: pointer; }
        .heart-icon { font-size: 1.4rem; color: #ccc; transition: 0.3s; }
        .heart-icon.liked { color: #ff5a5f; transform: scale(1.1); }
        .like-count { font-size: 0.9rem; color: #666; font-weight: 600; min-width: 15px; text-align: center; }

        /* Masonry Gutter 占位符 (如果不用百分比 margin，可以用这个) */
        .grid-sizer { width: 23.5%; }
        .gutter-sizer { width: 2%; } /* 列间距 */
        
        @media (max-width: 1200px) { .grid-sizer { width: 31.5%; } }
        @media (max-width: 900px) { .grid-sizer { width: 48%; } }
        @media (max-width: 600px) { .grid-sizer { width: 100%; } }

    </style>
</head>
<body>

    <?php include 'navbar.php'; ?> 

    <div class="page-content">
        <div class="container">
            
            <div class="page-header">
                <h1>Customer Reviews</h1>
                <p style="color:#777;">See what our customers are saying</p>
            </div>

            <div class="filter-bar">
                <a href="?filter=all" class="filter-btn <?php echo $filter=='all'?'active':''; ?>">
                    <span>★</span> All Reviews
                </a>
                <a href="?filter=images" class="filter-btn <?php echo $filter=='images'?'active':''; ?>">
                    <span>📷</span> With Images
                </a>
                <a href="?filter=verified" class="filter-btn <?php echo $filter=='verified'?'active':''; ?>">
                    <span>🏆</span> Verified Purchase
                </a>
            </div>

            <div class="reviews-grid">
                <div class="grid-sizer"></div>
                <div class="gutter-sizer"></div>

                <?php
                $sql = "SELECT r.*, u.username, p.product_name, 
                               (SELECT COUNT(*) FROM review_likes WHERE review_id = r.review_id) as like_count,
                               (SELECT COUNT(*) FROM review_likes WHERE review_id = r.review_id AND user_id = $current_user_id) as is_liked
                        FROM reviews r
                        JOIN users u ON r.user_id = u.user_id 
                        JOIN products p ON r.product_id = p.product_id
                        $where_clause 
                        ORDER BY r.created_at DESC";
                
                $result = $conn->query($sql);

                if ($result && $result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        $star_str = str_repeat("★", $row['rating']) . str_repeat("☆", 5 - $row['rating']);
                        $liked_class = ($row['is_liked'] > 0) ? 'liked' : '';
                        ?>
                        
                        <div class="review-card">
                            <?php if (!empty($row['image_url'])): ?>
                                <img src="<?php echo $row['image_url']; ?>" class="card-img-top" alt="Review Image">
                            <?php endif; ?>

                            <div class="card-body">
                                <div class="user-info">
                                    <span class="user-name"><?php echo htmlspecialchars($row['username']); ?></span>
                                    <span class="verified-badge">✔ Verified</span>
                                </div>
                                
                                <div class="stars"><?php echo $star_str; ?></div>

                                <div class="review-text">
                                    <?php echo nl2br(htmlspecialchars($row['comment'])); ?>
                                </div>

                                <div class="review-footer">
                                    <div class="product-meta">
                                        <?php echo htmlspecialchars($row['product_name']); ?> <br>
                                        <?php echo date('d M Y', strtotime($row['created_at'])); ?>
                                    </div>
                                    
                                    <div class="like-section" onclick="toggleLike(this, <?php echo $row['review_id']; ?>)">
                                        <div class="heart-icon <?php echo $liked_class; ?>">♥</div>
                                        <div class="like-count"><?php echo $row['like_count']; ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php
                    }
                } else {
                    echo "<p style='text-align:center; color:#999; width: 100%; padding: 50px;'>No reviews found.</p>";
                }
                ?>
            </div>
            
            <div style="text-align:center; margin-top:60px; color:#666; clear:both;">
                <p>Want to write a review? Go to <a href="USER-DASHBOARD.php" style="color:#20c997; font-weight:bold;">My Profile > Purchase History</a></p>
            </div>

        </div>
    </div>

    <script src="https://unpkg.com/imagesloaded@4/imagesloaded.pkgd.min.js"></script>
    <script src="https://unpkg.com/masonry-layout@4/dist/masonry.pkgd.min.js"></script>

    <script>
        // 初始化 Masonry
        var grid = document.querySelector('.reviews-grid');
        var msnry;

        // 必须等待图片加载完成后再布局，否则卡片会重叠
        imagesLoaded( grid, function() {
            msnry = new Masonry( grid, {
                itemSelector: '.review-card',
                columnWidth: '.grid-sizer',
                gutter: '.gutter-sizer',
                percentPosition: true
            });
        });

        // 点赞功能
        function toggleLike(element, reviewId) {
            <?php if(!$current_user_id): ?>
                alert("Please login to like reviews.");
                window.location.href = "LOGIN-REGISTER.php";
                return;
            <?php endif; ?>

            const heart = element.querySelector('.heart-icon');
            const countSpan = element.querySelector('.like-count');

            let formData = new FormData();
            formData.append('review_id', reviewId);

            fetch('like_review.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    countSpan.innerText = data.new_count;
                    if (data.action === 'liked') {
                        heart.classList.add('liked');
                    } else {
                        heart.classList.remove('liked');
                    }
                } else {
                    alert(data.message || "Error");
                }
            })
            .catch(error => console.error('Error:', error));
        }

        window.onscroll = function() {
            const nav = document.querySelector('.navbar');
            if (window.scrollY > 20) { nav.classList.add('collapsed'); } 
            else { nav.classList.remove('collapsed'); }
        };
    </script>
</body>
</html>