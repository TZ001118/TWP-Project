<?php
session_start();
// 简单的安全检查：如果没有 ID 传过来，就踢回首页
if (!isset($_GET['id'])) {
    header("Location: HOME.php");
    exit;
}
$order_id = htmlspecialchars($_GET['id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Success | FurnitureDirect</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body { background-color: #f8f9fa; text-align: center; }
        
        .success-container {
            max-width: 600px;
            /* ❌ 原来是 100px，太小了，被导航栏挡住了 */
            /* margin: 100px auto; */
            
            /* ✅ 修正：改成 220px，避开固定导航栏 */
            margin: 220px auto 100px auto;
            
            background: white;
            padding: 50px;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            position: relative; /* 确保层级正常 */
            z-index: 1; 
        }

        .check-icon {
            font-size: 80px; color: #20c997; margin-bottom: 20px;
        }
        h1 { color: #2c3e50; margin-bottom: 15px; }
        p { color: #666; font-size: 1.1rem; line-height: 1.6; margin-bottom: 10px; }
        
        .btn-home {
            display: inline-block;
            margin-top: 30px;
            padding: 12px 30px;
            background-color: #2c3e50;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            transition: 0.3s;
        }
        .btn-home:hover { background-color: #20c997; }
    </style>
</head>
<body>

    <?php include 'navbar.php'; ?>

    <div class="success-container">
        <div class="check-icon">✓</div>
        <h1>Thank You!</h1>
        <p>Your order <strong>#<?php echo $order_id; ?></strong> has been placed successfully.</p>
        <p>We will contact you shortly to confirm the delivery details.</p>
        
        <a href="HOME.php" class="btn-home">Back to Home</a>
    </div>

</body>
</html>