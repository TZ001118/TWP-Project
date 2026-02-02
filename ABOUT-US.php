<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us | FurnitureDirect</title>
    <link rel="stylesheet" href="style.css">

    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
            color: #333;
        }

        .story-section {
            text-align: center;
            padding: 60px 20px;
            background-color: #fff;
            margin-bottom: 30px;
        }
        .story-section h1 { color: #2c3e50; margin-bottom: 20px; }
        .story-section p { max-width: 800px; margin: 0 auto; line-height: 1.6; color: #666; font-size: 1.1rem; }
        .team-section {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px 60px;
        }
        .section-title {
            text-align: center;
            margin-bottom: 40px;
            font-size: 2rem;
            color: #333;
        }
        .team-container {
            display: flex;
            justify-content: center;
            gap: 30px;
            flex-wrap: wrap;
        }
        .member-card {
            background: white;
            width: 300px;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            text-align: center;
            transition: transform 0.3s ease;
        }
        .member-card:hover { transform: translateY(-5px); }

        .member-img-box {
            height: 250px;
            background-color: #eee;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        .member-img-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: top; 
        }

        .member-info { padding: 20px; }
        .member-info h3 { margin: 10px 0 5px; color: #2c3e50; }
        .member-info .role { color: #20c997; font-weight: bold; font-size: 0.9rem; margin-bottom: 10px; display: block; }
        .member-info .student-id { color: #999; font-size: 0.85rem; margin-bottom: 15px; }
        .member-info p { font-size: 0.9rem; color: #666; line-height: 1.4; }

        footer {
            background-color: #333;
            color: white;
            text-align: center;
            padding: 20px;
            margin-top: 50px;
        }
    </style>
</head>
<body>

    <?php include 'navbar.php'; ?>

    <div class="page-content">
        
        <section class="story-section">
            <h1>Our Story</h1>
            <p>
                Welcome to FurnitureDirect. We believe that a home is more than just a place to live—it's a reflection of who you are. 
                Since 2026, our team has been dedicated to providing high-quality, stylish, and affordable furniture to help you create your dream space.
            </p>
        </section>

        <section class="team-section">
            <h2 class="section-title">Meet Our Team</h2>
            
            <div class="team-container">
                
                <div class="member-card">
                    <div class="member-img-box">
                        <img src="img/Teo Rui Ze.png" alt="Teo Rui Ze">
                    </div>
                    <div class="member-info">
                        <h3>Teo Rui Ze</h3>
                        <span class="role">Project Leader / Backend</span>
                        <div class="student-id">ID: 242DT243VK</div>
                        <p>Responsible for Admin Dashboard, Database Management, and Product Logic.</p>
                    </div>
                </div>

                <div class="member-card">
                    <div class="member-img-box">
                        <img src="img/Tan You Hong 02.jpg" alt="Tan You Hong">
                    </div>
                    <div class="member-info">
                        <h3>Tan You Hong</h3>
                        <span class="role">Frontend Developer</span>
                        <div class="student-id">ID: 242DT243BV</div>
                        <p>Responsible for UI/UX Design, Home Page, Cart, and Catalogue pages.</p>
                    </div>
                </div>

                <div class="member-card">
                    <div class="member-img-box">
                        <img src="img/Toh Jun Heng.png" alt="Toh Jun Heng">
                    </div>
                    <div class="member-info">
                        <h3>Toh Jun Heng</h3>
                        <span class="role">Full Stack Developer</span>
                        <div class="student-id">ID: 242DT242HG</div>
                        <p>Responsible for User Authentication, Checkout Process, and Order Management.</p>
                    </div>
                </div>

                <div class="member-card">
                    <div class="member-img-box">
                        <img src="img/Sir Suhaimi.png" alt="Sir Suhaimi">
                    </div>
                    <div class="member-info">
                        <h3>Sir Suhaimi</h3>
                        <span class="role">Supervisor</span>
                        <p style="font-style: italic; color: #777;">"Special thanks for the guidance, supervision, and valuable feedback throughout the project development."</p>
                    </div>
                </div>

            </div>
        </section>

        <footer>
            <p>&copy; 2026 FurnitureDirect. All Rights Reserved.</p>
        </footer>

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
    </script>
</body>
</html>