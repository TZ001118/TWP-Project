<?php $current_page = 'home'; ?> 
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="home-style.css">
</head>
<body>

    <?php include 'navbar.php'; ?>
    
    <div class="page-content">
        <div class="hero-container">
            <button class="carousel-prev" onclick="move(-1)">&#10094;</button>
            <button class="carousel-next" onclick="move(1)">&#10095;</button>

            <div class="hero-carousel">
                <div class="carousel-inner" id="carouselInner">
                    <div class="slide">
                        <a href="#" class="shop-btn"><img src="img/Home/banner1.png" alt="Furniture 1"></a>
                    </div>
                    <div class="slide">
                        <a href="#" class="shop-btn"><img src="img/Home/banner2.png" alt="Furniture 2"></a>
                    </div>
                    <div class="slide">
                        <a href="#" class="shop-btn"><img src="img/Home/banner3.png" alt="Furniture 3"></a>
                    </div>
                </div>
                
                <div class="carousel-dots" id="carouselDots">
                    <span class="dot active"></span>
                    <span class="dot"></span>
                    <span class="dot"></span>
                </div>
            </div>
        </div>

        <div class="furniture-grid-container">
            <div class="grid-left">
                <div class="hotspot-section">
                    <div class="hotspot-container">
                        <img src="img/Home/AA1.png" alt="Shop the look" class="main-bg">

                        <div class="hotspot" style="top: 53%; left: 28%;">
                            <div class="hotspot-dot"></div>
                            <div class="hotspot-card">
                                <img src="img/Home/A1.png" alt="Product 1">
                                <div class="card-info">
                                    <h4>Fluted Sliding Door Sideboard</h4>
                                    <p class="price"><span class="old-price">RM 1,782</span> RM 998</p>
                                    <button class="add-to-cart">ADD TO CART</button>
                                </div>
                            </div>
                        </div>

                        <div class="hotspot" style="top: 40%; left: 79%;">
                            <div class="hotspot-dot"></div>
                            <div class="hotspot-card">
                                <img src="img/Home/A3.png" alt="Product 2">
                                <div class="card-info">
                                    <h4>Modern Oak Sideboard</h4>
                                    <p class="price">RM 1,200</p>
                                    <button class="add-to-cart">ADD TO CART</button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="hotspot" style="top: 65%; left: 48%;">
                            <div class="hotspot-dot"></div>
                            <div class="hotspot-card">
                                <img src="img/Home/A2.png" alt="Product 3">
                                <div class="card-info">
                                    <h4>Cozy Lounge Chair</h4>
                                    <p class="price">RM 450</p>
                                    <button class="add-to-cart">ADD TO CART</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <div class="grid-right">
                <div class="hotspot-section">
                    <div class="hotspot-container">
                        <img src="img\Home\BB1.png" alt="Shop the look" class="main-bg">

                        <div class="hotspot" style="top: 45%; left: 32%;">
                            <div class="hotspot-dot"></div>
                            <div class="hotspot-card">
                                <img src="img/Home/B3.png" alt="Product 1">
                                <div class="card-info">
                                    <h4>Fluted Sliding Door Sideboard</h4>
                                    <p class="price"><span class="old-price">RM 1,782</span> RM 998</p>
                                    <button class="add-to-cart">ADD TO CART</button>
                                </div>
                            </div>
                        </div>

                        <div class="hotspot" style="top: 70%; left: 79%;">
                            <div class="hotspot-dot"></div>
                            <div class="hotspot-card">
                                <img src="img/Home/B1.png" alt="Product 2">
                                <div class="card-info">
                                    <h4>Modern Oak Sideboard</h4>
                                    <p class="price">RM 1,200</p>
                                    <button class="add-to-cart">ADD TO CART</button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="hotspot" style="top: 55%; left: 58%;">
                            <div class="hotspot-dot"></div>
                            <div class="hotspot-card">
                                <img src="img/Home/B2.png" alt="Product 3">
                                <div class="card-info">
                                    <h4>Cozy Lounge Chair</h4>
                                    <p class="price">RM 450</p>
                                    <button class="add-to-cart">ADD TO CART</button>
                                </div>
                            </div>
                        </div>

                        <div class="hotspot" style="top: 72%; left: 23%;">
                            <div class="hotspot-dot"></div>
                            <div class="hotspot-card">
                                <img src="img/Home/B4.png" alt="Product 3">
                                <div class="card-info">
                                    <h4>Cozy Lounge Chair</h4>
                                    <p class="price">RM 450</p>
                                    <button class="add-to-cart">ADD TO CART</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.onscroll = function() {
            const nav = document.querySelector('.navbar');
            if(nav) {
                if (window.scrollY > 20) {
                    nav.classList.add('collapsed');
                } else {
                    nav.classList.remove('collapsed');
                }
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

        function resetTimer() {
            clearInterval(timer);
            timer = setInterval(() => move(1), 5000);
        }

        if(dots) {
            dots.forEach((dot, i) => {
                dot.onclick = () => {
                    currentSlide = i;
                    updateSlider();
                    resetTimer();
                };
            });
        }
    </script>
</body>
</html>