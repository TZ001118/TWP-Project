<?php
session_start();
include 'db_conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user_id'])) {
        header("Location: LOGIN-REGISTER.php");
        exit;
    }

    $u_id = $_SESSION['user_id'];
    $product_id = intval($_POST['product_id']);
    $rating = intval($_POST['rating']);
    $comment = $conn->real_escape_string($_POST['comment']);
    $image_url = NULL;

    // 检查是否已经评论过该商品 (防止重复刷评)
    $check = $conn->query("SELECT review_id FROM reviews WHERE user_id = $u_id AND product_id = $product_id");
    if ($check->num_rows > 0) {
        echo "<script>alert('You have already reviewed this product.'); window.location.href='USER-DASHBOARD.php';</script>";
        exit;
    }

    // 处理图片上传
    if (isset($_FILES['review_image']) && $_FILES['review_image']['error'] == 0) {
        $target_dir = "img/uploads/";
        if (!file_exists($target_dir)) { mkdir($target_dir, 0777, true); }
        $file_name = time() . "_" . basename($_FILES["review_image"]["name"]);
        $target_file = $target_dir . $file_name;
        
        if (move_uploaded_file($_FILES["review_image"]["tmp_name"], $target_file)) {
            $image_url = $target_file;
        }
    }

    if ($rating < 1 || $rating > 5) {
        echo "<script>alert('Please select a star rating.'); window.history.back();</script>";
        exit;
    }

    // 插入数据库
    $stmt = $conn->prepare("INSERT INTO reviews (user_id, product_id, rating, comment, image_url) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iisss", $u_id, $product_id, $rating, $comment, $image_url);
    
    if ($stmt->execute()) {
        $_SESSION['swal'] = [
            'type' => 'success',
            'title' => 'Thank You!',
            'text' => 'Your review has been submitted successfully.'
        ];
        
        header("Location: USER-DASHBOARD.php");
        exit;
    } else {
        $_SESSION['swal'] = [
            'type' => 'error',
            'title' => 'Error',
            'text' => 'Something went wrong: ' . $conn->error
        ];
        header("Location: USER-DASHBOARD.php");
        exit;
    }
    $stmt->close();
}
?>