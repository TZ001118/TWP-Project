<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Store Locator | Furniture Direct</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="style.css">

    <style>
        body {
            background-color: #f9f9f9;
        }

        .page-content {
            margin-top: 160px; 
            padding-bottom: 80px;
        }

        .page-header {
            background-color: #f1f1f1;
            padding: 15px 0;
            margin-bottom: 40px;
            border-bottom: 1px solid #e0e0e0;
        }

        .section-title {
            text-align: center;
            margin-bottom: 10px;
            font-weight: 700;
            font-size: 2rem;
            color: #333;
        }

        .section-subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 50px;
            font-size: 1.1rem;
        }

        .store-grid {
            display: flex;
            gap: 40px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .store-column {
            flex: 1;
            min-width: 350px;
            max-width: 600px;
            background: #fff;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }

        .custom-slider {
            position: relative;
            width: 100%;
            height: 350px;
            border-radius: 15px;
            overflow: hidden;
            margin-bottom: 25px;
        }

        .slide-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            position: absolute;
            top: 0;
            left: 0;
            opacity: 0;
            transition: opacity 0.8s ease-in-out;
        }

        .slide-img.active {
            opacity: 1;
        }

        .slider-btn {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(0, 0, 0, 0.3);
            color: white;
            border: none;
            cursor: pointer;
            padding: 10px 15px;
            font-size: 18px;
            border-radius: 50%;
            transition: 0.3s;
            z-index: 10;
        }
        .slider-btn:hover { background: rgba(0,0,0,0.6); }
        .prev-btn { left: 10px; }
        .next-btn { right: 10px; }

        .store-info-text {
            text-align: center;
        }

        .store-name {
            font-weight: 700;
            font-size: 1.5rem;
            margin-bottom: 5px;
            color: #333;
        }

        .appointment-text {
            color: #666;
            font-size: 0.95rem;
            margin-bottom: 20px;
        }

        .address-text {
            color: #5eb4a1;
            font-size: 1rem;
            margin-bottom: 30px;
            line-height: 1.6;
            padding: 0 20px;
        }

        .pin-icon {
            color: #e91e63;
            margin-right: 5px;
        }

        .hours-container {
            max-width: 400px;
            margin: 0 auto 30px auto;
            text-align: left;
        }

        .hours-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #eee;
            font-size: 0.95rem;
            color: #555;
        }

        .hours-row.closed {
            color: #dc3545;
            font-weight: bold;
        }

        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 20px;
        }

        .btn-custom {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 30px;
            border-radius: 30px;
            font-weight: 700;
            font-size: 0.9rem;
            text-decoration: none;
            transition: all 0.3s;
            color: white;
        }

        .btn-direction {
            background-color: #62c2e6;
        }
        .btn-direction:hover {
            background-color: #4db3d9;
            transform: translateY(-2px);
        }

        .btn-contact {
            background-color: #5eb4a1;
        }
        .btn-contact:hover {
            background-color: #4ca390;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>

    <?php include 'navbar.php'; ?>

    <div class="page-content">
        
        <div class="page-header">
            <div class="container">
                <span style="color: #333; font-weight: 500;">Furniture Direct Outlets / Our Campuses</span>
            </div>
        </div>

        <div class="container">
            <h2 class="section-title">Explore our furniture showcases</h2>
            <p class="section-subtitle">You can decide which experience store to visit based on the following product showcase.</p>

            <div class="store-grid">
                
                <div class="store-column">
                    <div class="custom-slider" id="slider-melaka">
                        <img src="img\Locator\M1.png" class="slide-img active">
                        <img src="img\Locator\M2.png" class="slide-img">
                        <img src="img\Locator\M3.png" class="slide-img">
                        <img src="img\Locator\M4.png" class="slide-img">
                        <img src="img\Locator\M5.png" class="slide-img">
                        <img src="img\Locator\M6.png" class="slide-img">
                        
                        <button class="slider-btn prev-btn" onclick="changeSlide('slider-melaka', -1)">❮</button>
                        <button class="slider-btn next-btn" onclick="changeSlide('slider-melaka', 1)">❯</button>
                    </div>

                    <div class="store-info-text">
                        <h3 class="store-name">MMU Melaka Campus</h3>
                        <p class="appointment-text">(Walk-in allowed)</p>
                        
                        <p class="address-text">
                            <i class="fas fa-map-marker-alt pin-icon"></i> 
                            Jalan Ayer Keroh Lama, 75450 Bukit Beruang, Melaka.
                        </p>

                        <div class="hours-container">
                            <div class="hours-row">
                                <span>Monday-Friday</span>
                                <span>9.00am-6.00pm</span>
                            </div>
                            <div class="hours-row">
                                <span>Saturday</span>
                                <span>9.00am-1.00pm</span>
                            </div>
                            <div class="hours-row closed">
                                <span>Sunday & Public Holiday</span>
                                <span>CLOSED</span>
                            </div>
                        </div>

                        <div class="action-buttons">
                            <a href="https://maps.google.com/?q=MMU+Melaka" target="_blank" class="btn-custom btn-direction">
                                <i class="fas fa-location-arrow"></i> DIRECTION
                            </a>
                            <a href="https://wa.me/601120900533" target="_blank" class="btn-custom btn-contact">
                                <i class="fab fa-whatsapp"></i> CONTACT NOW
                            </a>
                        </div>
                    </div>
                </div>

                <div class="store-column">
                    <div class="custom-slider" id="slider-cyber">
                        <img src="img\Locator\C1.png" class="slide-img active">
                        <img src="img\Locator\C2.png" class="slide-img">
                        <img src="img\Locator\C3.png" class="slide-img">
                        <img src="img\Locator\C4.png" class="slide-img">
                        <img src="img\Locator\C5.png" class="slide-img">
                        <img src="img\Locator\C6.png" class="slide-img">

                        <button class="slider-btn prev-btn" onclick="changeSlide('slider-cyber', -1)">❮</button>
                        <button class="slider-btn next-btn" onclick="changeSlide('slider-cyber', 1)">❯</button>
                    </div>

                    <div class="store-info-text">
                        <h3 class="store-name">MMU Cyberjaya Campus</h3>
                        <p class="appointment-text">(By appointment only)</p>
                        
                        <p class="address-text">
                            <i class="fas fa-map-marker-alt pin-icon"></i> 
                            Persiaran Multimedia, 63100 Cyberjaya, Selangor.
                        </p>

                        <div class="hours-container">
                            <div class="hours-row">
                                <span>Monday-Friday</span>
                                <span>9.00am-6.00pm</span>
                            </div>
                            <div class="hours-row">
                                <span>Saturday</span>
                                <span>10.00am-2.00pm</span>
                            </div>
                            <div class="hours-row closed">
                                <span>Sunday & Public Holiday</span>
                                <span>CLOSED</span>
                            </div>
                        </div>

                        <div class="action-buttons">
                            <a href="https://maps.google.com/?q=MMU+Cyberjaya" target="_blank" class="btn-custom btn-direction">
                                <i class="fas fa-location-arrow"></i> DIRECTION
                            </a>
                            <a href="https://wa.me/601120900533" target="_blank" class="btn-custom btn-contact">
                                <i class="fab fa-whatsapp"></i> CONTACT NOW
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script>
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

        const sliderIndices = {
            'slider-melaka': 0,
            'slider-cyber': 0
        };

        function startAutoPlay() {
            setInterval(() => {
                changeSlide('slider-melaka', 1);
                changeSlide('slider-cyber', 1);
            }, 3000);
        }

        function changeSlide(sliderId, n) {
            const slider = document.getElementById(sliderId);
            const slides = slider.getElementsByClassName('slide-img');
            let index = sliderIndices[sliderId];

            slides[index].classList.remove('active');

            index += n;
            if (index >= slides.length) { index = 0; }
            if (index < 0) { index = slides.length - 1; }

            sliderIndices[sliderId] = index;
            slides[index].classList.add('active');
        }

        document.addEventListener('DOMContentLoaded', startAutoPlay);

    </script>

</body>
</html>