<?php 
$current_page = 'virtual'; 
session_start();
include 'db_conn.php';
?> 

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Virtual Showroom | FurnitureDirect</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body { background-color: #f8f9fa; }
        .page-content { margin-top: 0 !important; padding-top: 0; }

        .showroom-hero {
            margin-top: 160px;
            background-color: #2c3e50; 
            background-image: url('img/pattern-bg.png');
            color: white;
            text-align: center;
            padding: 80px 20px;
            margin-bottom: 50px;
        }
        .showroom-hero h1 { font-size: 3rem; margin-bottom: 15px; font-weight: 700; color: #20c997; }
        .showroom-hero p { font-size: 1.1rem; max-width: 700px; margin: 0 auto; opacity: 0.9; line-height: 1.6; }

        .showroom-container { max-width: 1200px; margin: 0 auto; padding: 0 20px 80px 20px; }
        .section-title { text-align: center; margin: 0 0 30px; font-size: 1.8rem; color: #2c3e50; font-weight: 700; position: relative; }
        .section-title::after { content: ''; display: block; width: 60px; height: 3px; background: #20c997; margin: 10px auto; }

        /* Grid 布局 */
        .furniture-grid-container {
            display: flex;
            flex-wrap: wrap;
            gap: 40px; 
            padding: 20px;
            max-width: 1400px;
            margin: 0 auto;
            justify-content: center;
        }

        .grid-left, .grid-right { flex: 1; min-width: 450px; }

        /* Inspiration Grid */
        .inspiration-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 25px; }

        /* 图片容器 */
        .hotspot-section { 
            position: relative; 
            border-radius: 15px; 
            /* overflow: hidden; <-- 删除 */
            overflow: visible; /* ★★★ 修改点：改成 visible ★★★ */
            box-shadow: 0 10px 25px rgba(0,0,0,0.05); 
            background: white; 
            width: 100%;
            aspect-ratio: 3 / 2; 
        }

        /* Showroom 小图强制正方形 */
        .inspiration-grid .hotspot-section { aspect-ratio: 1 / 1; }

        .hotspot-container { position: relative; width: 100%; height: 100%; }
        .main-bg { width: 100%; height: 100%; display: block; border-radius: 15px; object-fit: cover; object-position: center; }

        /* --- ★★★ Showroom 专属热点样式优化 ★★★ --- */
        .hotspot { 
            position: absolute; 
            cursor: pointer; 
            z-index: 10; 
            /* Showroom 的触控区比 Home 更大 */
            width: 60px; 
            height: 60px; 
            display: flex;
            align-items: center;
            justify-content: center;
            transform: translate(-50%, -50%);
        }

        .hotspot-dot { 
            width: 30px; /* 稍微大一点点，更显眼 */
            height: 30px; 
            background-color: #5eb4a1; 
            border: 3px solid white; 
            border-radius: 50%; 
            box-shadow: 0 0 10px rgba(0,0,0,0.2); 
            position: relative; 
            transition: transform 0.3s; 
            pointer-events: none;
        }
        
        .hotspot-dot::after { content: ''; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 100%; height: 100%; border-radius: 50%; border: 2px solid #5eb4a1; animation: pulse 2s infinite; }
        @keyframes pulse { 0% { width: 100%; opacity: 1; } 100% { width: 300%; opacity: 0; } }
        .hotspot:hover .hotspot-dot { transform: scale(1.2); }
        
        .hotspot-card { 
            position: absolute; 
            bottom: 45px; 
            left: 50%; 
            transform: translateX(-50%) translateY(10px); 
            width: 200px; 
            background: white; 
            padding: 15px; 
            border-radius: 8px; 
            box-shadow: 0 5px 25px rgba(0,0,0,0.2); 
            text-align: center; 
            opacity: 0; 
            visibility: hidden; 
            transition: all 0.3s ease; 
            pointer-events: none; 
            
            z-index: 9999; /* ★★★ 修改点：改大层级，防止被下面的图片挡住 ★★★ */
            
            padding-bottom: 25px; 
            margin-bottom: -15px; 
        }
        
        .hotspot-card::after { 
            content: ''; 
            position: absolute; 
            bottom: 18px; /* 调整箭头位置 */
            left: 50%; 
            transform: translateX(-50%); 
            border-left: 8px solid transparent; 
            border-right: 8px solid transparent; 
            border-top: 8px solid white; 
        }
        
        .hotspot:hover .hotspot-card { opacity: 1; visibility: visible; transform: translateX(-50%) translateY(0); pointer-events: auto; }
        .hotspot-card img { width: 100%; height: 120px; object-fit: cover; border-radius: 4px; margin-bottom: 8px; }
        .card-info h4 { font-size: 0.95rem; margin-bottom: 5px; color: #333; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .card-info h4 a { text-decoration: none; color: inherit; }
        .price-box { margin-bottom: 10px; font-size: 0.9rem; }
        .price { color: #5eb4a1; font-weight: bold; }
        .old-price { text-decoration: line-through; color: #999; font-size: 0.8rem; margin-right: 5px; }
        .add-to-cart { background-color: #5eb4a1; color: white; border: none; padding: 8px 12px; border-radius: 20px; font-size: 0.8rem; cursor: pointer; width: 100%; font-weight: bold; transition: 0.2s; }
        .add-to-cart:hover { background-color: #4da08e; }
        
        @media (max-width: 900px) { 
            .inspiration-grid { grid-template-columns: 1fr; } 
            .furniture-grid-container { flex-direction: column; } 
        }
    </style>
</head>
<body>

    <?php include 'navbar.php'; ?> 

    <div class="showroom-hero">
        <h1>Virtual Showroom</h1>
        <p>Experience our furniture in real-life settings</p>
    </div>

    <div class="page-content">
        <div class="showroom-container">

            <div class="furniture-grid-container">
                
                <div class="grid-left">
                    <div class="section-title" style="margin-bottom: 20px; font-size: 1.5rem;">Modern Living Room</div>
                    <?php include 'include_hotspot_livingroom.php'; ?>
                </div>

                <div class="grid-right">
                    <div class="section-title" style="margin-bottom: 20px; font-size: 1.5rem;">Study Room</div>
                    <?php include 'include_hotspot_bedroom.php'; ?>
                </div>

            </div>

            <div class="section-title" style="margin-top: 60px;">More Inspiration</div>
            
            <div class="inspiration-grid">
                
                <?php include 'include_hotspot_dining.php'; ?>

                <?php include 'include_hotspot_office.php'; ?>

                <?php include 'include_hotspot_decor.php'; ?>

            </div>

        </div>
    </div>

    <script>
        // --- 1. 导航栏滚动逻辑 ---
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

        // --- 2. 无刷新购物车逻辑 (AJAX) ---
        document.addEventListener('DOMContentLoaded', function() {
            const forms = document.querySelectorAll('form[action="add_to_cart.php"]');

            forms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    e.preventDefault(); // ★★★ 阻止页面刷新 ★★★

                    const formData = new FormData(this);

                    fetch('add_to_cart.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => {
                        // 检查是否被踢到登录页
                        if (response.redirected && response.url.includes('LOGIN-REGISTER.php')) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Please Login',
                                text: 'You need to login to add items to cart.',
                                showConfirmButton: true,
                                confirmButtonText: 'Go to Login',
                                confirmButtonColor: '#333'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    window.location.href = 'LOGIN-REGISTER.php';
                                }
                            });
                            return;
                        }

                        // 成功添加
                        if (response.ok) {
                            // ★★★ 直接使用 navbar.php 里定义的 Toast ★★★
                            Toast.fire({
                                icon: 'success',
                                title: 'Added to Cart'
                            });

                            // 更新购物车数字
                            const cartBadge = document.querySelector('.cart-box span');
                            if(cartBadge) {
                                let currentCount = parseInt(cartBadge.innerText);
                                if(isNaN(currentCount)) currentCount = 0;
                                cartBadge.innerText = currentCount + 1;
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    });
                });
            });
        });
    </script>
</body>
</html>