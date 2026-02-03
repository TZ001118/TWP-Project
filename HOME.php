<?php 
$current_page = 'home'; 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'db_conn.php'; 

// =========================================================
// 1. 获取沙发 (Sofa) - 第一排
// =========================================================
// 使用 LIMIT 4 限制最多显示4个。如果没有4个，有多少显示多少。
$sql_sofas = "SELECT * FROM products WHERE product_name LIKE '%Sofa%' LIMIT 4";
$result_sofas = $conn->query($sql_sofas);

// =========================================================
// 2. 获取床 (Bed) - 第二排
// =========================================================
$sql_beds = "SELECT * FROM products WHERE product_name LIKE '%Bed%' LIMIT 4";
$result_beds = $conn->query($sql_beds);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="home-style.css">
    <style>
        /* --- 热点原有样式 (保持不变) --- */
        .hotspot-section { position: relative; border-radius: 15px; overflow: hidden; box-shadow: 0 10px 25px rgba(0,0,0,0.05); background: white; }
        .hotspot-container { position: relative; width: 100%; }
        .main-bg { width: 100%; display: block; border-radius: 15px; }
        .hotspot { position: absolute; cursor: pointer; z-index: 10; }
        .hotspot-dot { width: 24px; height: 24px; background-color: #5eb4a1; border: 3px solid white; border-radius: 50%; box-shadow: 0 0 10px rgba(0,0,0,0.2); position: relative; transition: transform 0.3s; }
        .hotspot-dot::after { content: ''; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 100%; height: 100%; border-radius: 50%; border: 2px solid #5eb4a1; animation: pulse 2s infinite; }
        @keyframes pulse { 0% { width: 100%; opacity: 1; } 100% { width: 300%; opacity: 0; } }
        .hotspot:hover .hotspot-dot { transform: scale(1.2); }
        .hotspot-card { position: absolute; bottom: 40px; left: 50%; transform: translateX(-50%) translateY(10px); width: 200px; background: white; padding: 15px; border-radius: 8px; box-shadow: 0 5px 25px rgba(0,0,0,0.2); text-align: center; opacity: 0; visibility: hidden; transition: all 0.3s ease; pointer-events: none; z-index: 100; }
        .hotspot-card::after { content: ''; position: absolute; bottom: -8px; left: 50%; transform: translateX(-50%); border-left: 8px solid transparent; border-right: 8px solid transparent; border-top: 8px solid white; }
        .hotspot:hover .hotspot-card { opacity: 1; visibility: visible; transform: translateX(-50%) translateY(0); pointer-events: auto; }
        .hotspot-card img { width: 100%; height: 120px; object-fit: cover; border-radius: 4px; margin-bottom: 8px; }
        .card-info h4 { font-size: 0.95rem; margin-bottom: 5px; color: #333; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .card-info h4 a { text-decoration: none; color: inherit; }
        .price-box { margin-bottom: 10px; font-size: 0.9rem; }
        .price { color: #5eb4a1; font-weight: bold; }
        .old-price { text-decoration: line-through; color: #999; font-size: 0.8rem; margin-right: 5px; }
        .add-to-cart { background-color: #5eb4a1; color: white; border: none; padding: 8px 12px; border-radius: 20px; font-size: 0.8rem; cursor: pointer; width: 100%; font-weight: bold; transition: 0.2s; }
        .add-to-cart:hover { background-color: #4da08e; }

        /* --- ★★★ 修复后的布局样式 ★★★ --- */
        .promo-wrapper {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .promo-section {
            margin: 60px 0;
            display: flex;
            gap: 20px;
            /* 确保高度一致 */
            align-items: stretch; 
        }

        /* 左侧广告大图 (变窄) */
        .promo-left {
            /* flex: 1 表示占 1 份宽度 (约 20%) */
            flex: 1; 
            position: relative;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
            min-height: 350px; /* 最小高度，防止没内容时塌陷 */
        }
        .promo-left img {
            width: 100%;
            height: 100%;
            object-fit: cover; /* 自动裁剪，填满框框 */
            display: block;
            transition: transform 0.5s ease;
        }
        .promo-left:hover img { transform: scale(1.03); }
        
        /* 标题覆盖在图片上 (可选) */
        .promo-text-overlay {
            position: absolute;
            bottom: 20px; left: 20px;
            color: white;
            text-shadow: 0 2px 5px rgba(0,0,0,0.5);
        }
        .promo-text-overlay h3 { margin: 0; font-size: 1.8rem; }
        .promo-text-overlay p { margin: 5px 0 0; font-size: 0.9rem; opacity: 0.9; }

        /* 右侧商品网格 (变宽) */
        .promo-right {
            /* flex: 4 表示占 4 份宽度 (约 80%) */
            flex: 4; 
            display: grid;
            /* 4列并排，这样比例最好看 */
            grid-template-columns: repeat(4, 1fr); 
            gap: 20px;
        }

        /* 如果商品少于4个，空出来的位置会自动留白，不会撑大 */

        /* 商品卡片样式 */
        .promo-product-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: transform 0.3s ease;
            display: flex;
            flex-direction: column;
            height: 100%; /* 填满高度 */
        }
        .promo-product-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1); }

        .promo-img-box {
            /* 图片比例设定为正方形，防止参差不齐 */
            aspect-ratio: 1 / 1; 
            width: 100%;
            overflow: hidden;
            position: relative;
            background: #f9f9f9;
        }
        .promo-img-box img {
            width: 100%; height: 100%; object-fit: cover;
        }

        .promo-details {
            padding: 15px;
            text-align: center;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .promo-title { 
            font-weight: bold; color: #333; margin-bottom: 5px; font-size: 0.95rem; 
            text-decoration: none; display: block; 
            /* 限制标题最多两行 */
            display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
        }
        .promo-title:hover { color: #5eb4a1; }
        .promo-price { color: #5eb4a1; font-weight: bold; margin-bottom: 10px; display: block; }

        /* 响应式：手机端变成单列 */
        @media (max-width: 900px) {
            .furniture-grid-container, .promo-section { flex-direction: column; }
            .grid-left, .grid-right { min-width: 100%; }
            .promo-left { height: 200px; } /* 手机端图片变矮 */
            .promo-right { grid-template-columns: repeat(2, 1fr); } /* 手机端一行2个 */
        }
        /* 容器适配预留位置 */
        /* 修改后的样式，确保不挤压热点图 */
        /* 更加稳固的布局建议 */
        .furniture-grid-container {
            display: grid;
            grid-template-columns: 2fr 1fr; /* 左边大，右边小 */
            grid-template-rows: auto auto;
            gap: 20px;
            max-width: 1300px;
            margin: 20px auto;
        }

        .mini-hero-wrapper {
            position: relative;
            width: 100%;       /* 填满父容器 grid-right 的宽度 */
            max-width: 420px;  /* ★ 设置一个硬性的最大宽度，防止它横向撑爆 */
            height: 280px;     /* ★ 设置固定高度，确保和左侧图对齐 */
            flex-shrink: 0;    /* 禁止 Flex 布局压缩或放大它 */
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            margin-top: 15px;  /* 与上方热点图留出间距 */
        }

        .mini-carousel-container {
            position: relative;
            width: 100%;
            height: 100%;
        }

        .mini-carousel-inner {
            display: flex;
            transition: transform 0.5s ease-in-out;
            width: 100%;
            height: 100%;
        }

        .mini-slide {
            min-width: 100%;
            height: 100%;
            position: relative;
        }

        .mini-slide img {
            width: 100%;
            height: 100%;
            object-fit: cover; /* 确保图片填满小角落 */
            display: block;
        }

        .mini-slide-title {
            position: absolute;
            bottom: 0;
            width: 100%;
            background: rgba(0,0,0,0.5);
            color: #fff;
            padding: 10px;
            font-size: 0.85rem;
            text-align: center;
        }

        /* 按钮样式微调 */
        .mini-carousel-prev, .mini-carousel-next {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(255,255,255,0.4);
            border: none;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            cursor: pointer;
            z-index: 10;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
        }
        .mini-carousel-prev { left: 10px; }
        .mini-carousel-next { right: 10px; }

        /* 圆点样式 */
        .mini-carousel-dots {
            position: absolute;
            bottom: 35px;
            width: 100%;
            text-align: center;
            z-index: 5;
        }
        .mini-dot {
            height: 8px;
            width: 8px;
            margin: 0 4px;
            background-color: rgba(255,255,255,0.5);
            border-radius: 50%;
            display: inline-block;
            cursor: pointer;
        }
        .mini-dot.active { background-color: #5eb4a1; }
        .mini-carousel-container, 
        .mini-carousel-inner, 
        .mini-slide {
            width: 100%;
            height: 100%;
        }

        /* 图片强制填充，不许撑开容器 */
        .mini-slide img {
            width: 100%;
            height: 100%;
            object-fit: cover; /* 自动裁剪中心部分 */
            display: block;
        }

        /* 按钮微调，防止在小图里太突兀 */
        .mini-carousel-prev, .mini-carousel-next {
            width: 28px;
            height: 28px;
            font-size: 12px;
            background: rgba(255,255,255,0.6);
        }
    </style>
</head>
<body>

    <?php include 'navbar.php'; ?>
    
    <div class="page-content">
        <div class="hero-container">
            <button class="carousel-prev" onclick="move(-1)">&#10094;</button>
            <button class="carousel-next" onclick="move(1)">&#10095;</button>

            <div class="hero-carousel">
                <div class="carousel-inner" id="carouselInner">
                    <div class="slide"><a href="#" class="shop-btn"><img src="img/Home/banner1.png" alt="Furniture 1"></a></div>
                    <div class="slide"><a href="#" class="shop-btn"><img src="img/Home/banner2.png" alt="Furniture 2"></a></div>
                    <div class="slide"><a href="#" class="shop-btn"><img src="img/Home/banner3.png" alt="Furniture 3"></a></div>
                </div>
                <div class="carousel-dots" id="carouselDots">
                    <span class="dot active"></span><span class="dot"></span><span class="dot"></span>
                </div>
            </div>
        </div>

        <div class="furniture-grid-container">
            <div class="grid-left">
                <?php include 'include_hotspot_livingroom.php'; ?>
            </div>
            <div class="grid-right">
                <?php include 'include_hotspot_bedroom.php'; ?>
                <div class="mini-hero-wrapper">
                    <button class="mini-carousel-prev" onclick="moveMini(-1)">&#10094;</button>
                    <button class="mini-carousel-next" onclick="moveMini(1)">&#10095;</button>

                    <div class="mini-carousel-container">
                        <div class="mini-carousel-inner" id="miniCarouselInner">
                            <div class="mini-slide">
                                <a href="PRODUCT_DETAILS.php?id=1">
                                    <img src="img/office.png" alt="Promo 1">
                                    <div class="mini-slide-title">Modern Sofa Set</div>
                                </a>
                            </div>
                            <div class="mini-slide">
                                <a href="PRODUCT_DETAILS.php?id=2">
                                    <img src="img/office.png" alt="Promo 2">
                                    <div class="mini-slide-title">King Size Bed</div>
                                </a>
                            </div>
                            <div class="mini-slide">
                                <a href="PRODUCT_DETAILS.php?id=3">
                                    <img src="img/office.png" alt="Promo 3">
                                    <div class="mini-slide-title">Office Essentials</div>
                                </a>
                            </div>
                        </div>
                        <div class="mini-carousel-dots" id="miniCarouselDots">
                            <span class="mini-dot active"></span>
                            <span class="mini-dot"></span>
                            <span class="mini-dot"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="promo-wrapper">
            
            <div class="section-title" style="text-align:left; margin-top:40px; font-size:1.5rem; font-weight:bold; color:#333;">
                Sofa Collection
            </div>
            
            <div class="promo-section">
                <div class="promo-left">
                    <img src="img/Home/sofa_promo.jpg" alt="Sofa Collection" onerror="this.src='https://via.placeholder.com/400x600?text=Sofa+Collection'">
                    <div class="promo-text-overlay">
                        <h3>Cozy Sofas</h3>
                        <p>Comfort meets style</p>
                    </div>
                </div>

                <div class="promo-right">
                    <?php if ($result_sofas && $result_sofas->num_rows > 0): ?>
                        <?php while($item = $result_sofas->fetch_assoc()): ?>
                            <div class="promo-product-card">
                                <div class="promo-img-box">
                                    <a href="PRODUCT_DETAILS.php?id=<?php echo $item['product_id']; ?>">
                                        <img src="img/<?php echo $item['product_image']; ?>" alt="<?php echo htmlspecialchars($item['product_name']); ?>">
                                    </a>
                                </div>
                                <div class="promo-details">
                                    <a href="PRODUCT_DETAILS.php?id=<?php echo $item['product_id']; ?>" class="promo-title">
                                        <?php echo htmlspecialchars($item['product_name']); ?>
                                    </a>
                                    <span class="promo-price">RM <?php echo number_format($item['price'], 2); ?></span>
                                    
                                    <form action="add_to_cart.php" method="POST">
                                        <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                                        <input type="hidden" name="quantity" value="1">
                                        <button type="submit" class="add-to-cart">ADD TO CART</button>
                                    </form>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p style="text-align:center; width:100%; color:#999; grid-column:1/-1; padding:20px;">
                            No Sofa products found in database. <br> (Please add products with "Sofa" in the name)
                        </p>
                    <?php endif; ?>
                </div>
            </div>


            <div class="section-title" style="text-align:left; margin-top:40px; font-size:1.5rem; font-weight:bold; color:#333;">
                Bed Frames
            </div>

            <div class="promo-section">
                <div class="promo-left">
                    <img src="img/Home/bed_promo.jpg" alt="Bed Collection" onerror="this.src='https://via.placeholder.com/400x600?text=Bed+Collection'">
                    <div class="promo-text-overlay">
                        <h3>Dreamy Beds</h3>
                        <p>Sleep in luxury</p>
                    </div>
                </div>

                <div class="promo-right">
                    <?php if ($result_beds && $result_beds->num_rows > 0): ?>
                        <?php while($item = $result_beds->fetch_assoc()): ?>
                            <div class="promo-product-card">
                                <div class="promo-img-box">
                                    <a href="PRODUCT_DETAILS.php?id=<?php echo $item['product_id']; ?>">
                                        <img src="img/<?php echo $item['product_image']; ?>" alt="<?php echo htmlspecialchars($item['product_name']); ?>">
                                    </a>
                                </div>
                                <div class="promo-details">
                                    <a href="PRODUCT_DETAILS.php?id=<?php echo $item['product_id']; ?>" class="promo-title">
                                        <?php echo htmlspecialchars($item['product_name']); ?>
                                    </a>
                                    <span class="promo-price">RM <?php echo number_format($item['price'], 2); ?></span>
                                    
                                    <form action="add_to_cart.php" method="POST">
                                        <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                                        <input type="hidden" name="quantity" value="1">
                                        <button type="submit" class="add-to-cart">ADD TO CART</button>
                                    </form>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p style="text-align:center; width:100%; color:#999; grid-column:1/-1; padding:20px;">
                            No Bed products found in database. <br> (Please add products with "Bed" in the name)
                        </p>
                    <?php endif; ?>
                </div>
            </div>

        </div> </div>

    <script>
        window.onscroll = function() {
            const nav = document.querySelector('.navbar');
            if(nav) {
                if (window.scrollY > 20) { nav.classList.add('collapsed'); } else { nav.classList.remove('collapsed'); }
            }
        };

        let currentSlide = 0;
        const inner = document.getElementById('carouselInner');
        const dots = document.querySelectorAll('.dot');
        const total = dots ? dots.length : 0;

        function updateSlider() {
            if(inner) {
                inner.style.transform = `translateX(-${currentSlide * 100}%)`;
                dots.forEach((d, i) => d.classList.toggle('active', i === currentSlide));
            }
        }
        function move(step) {
            if(total > 0) {
                currentSlide = (currentSlide + step + total) % total;
                updateSlider();
                resetTimer();
            }
        }
        let timer = setInterval(() => move(1), 5000);
        function resetTimer() { clearInterval(timer); timer = setInterval(() => move(1), 5000); }
        if(dots) { dots.forEach((dot, i) => { dot.onclick = () => { currentSlide = i; updateSlider(); resetTimer(); }; }); }
        let currentMiniSlide = 0;
        const miniInner = document.getElementById('miniCarouselInner');
        const miniDots = document.querySelectorAll('.mini-dot');
        const totalMini = miniDots.length;

        function updateMiniSlider() {
            if(miniInner) {
                miniInner.style.transform = `translateX(-${currentMiniSlide * 100}%)`;
                miniDots.forEach((d, i) => d.classList.toggle('active', i === currentMiniSlide));
            }
        }

        function moveMini(step) {
            if(totalMini > 0) {
                currentMiniSlide = (currentMiniSlide + step + totalMini) % totalMini;
                updateMiniSlider();
            }
        }

        // 自动播放逻辑
        let miniTimer = setInterval(() => moveMini(1), 4000);

        // 点击圆点切换
        miniDots.forEach((dot, i) => {
            dot.onclick = () => {
                currentMiniSlide = i;
                updateMiniSlider();
                clearInterval(miniTimer);
                miniTimer = setInterval(() => moveMini(1), 4000);
            };
        });
    </script>
</body>
</html>