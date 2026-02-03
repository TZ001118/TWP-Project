<?php 
$current_page = 'custom'; 
session_start();
include 'db_conn.php';

// 处理表单提交
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_inquiry'])) {
    
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
    
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $subject = mysqli_real_escape_string($conn, $_POST['subject']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);
    
    // 处理图片上传
    $image_name = null;
    if (isset($_FILES['ref_image']) && $_FILES['ref_image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $filename = $_FILES['ref_image']['name'];
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        if (in_array(strtolower($ext), $allowed)) {
            $new_name = "custom_" . time() . "_" . uniqid() . "." . $ext;
            if (!is_dir('img/custom_uploads')) mkdir('img/custom_uploads', 0777, true);
            move_uploaded_file($_FILES['ref_image']['tmp_name'], "img/custom_uploads/" . $new_name);
            $image_name = $new_name;
        }
    }

    $sql = "INSERT INTO custom_inquiries (user_id, name, email, phone, subject, message, reference_image) 
            VALUES ('$user_id', '$name', '$email', '$phone', '$subject', '$message', '$image_name')";

    if ($conn->query($sql)) {
        $_SESSION['swal'] = ['type' => 'success', 'title' => 'Request Sent!', 'text' => 'We will contact you shortly regarding your custom furniture.'];
    } else {
        $_SESSION['swal'] = ['type' => 'error', 'title' => 'Error', 'text' => 'Something went wrong. Please try again.'];
    }
    
    header("Location: CUSTOM-MADE.php");
    exit();
}
?> 

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Custom Made Furniture | FurnitureDirect</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { background-color: #f9fbfd; }
        
        /* ★★★ 关键修复 ★★★ 
           1. 这里必须设为 0 !important，因为 Banner 已经占了位置，
              如果不设为 0，全局 CSS 的 205px margin 会导致 Banner 和表单之间有巨大空隙。
        */
        .page-content { 
            margin-top: 0 !important; 
            padding-top: 0;
        } 

        /* --- Hero Banner --- */
        .custom-hero {
            /* ★★★ 关键修复：给 Banner 加上顶部边距，给导航栏留出空间 ★★★ */
            margin-top: 205px; 
            
            background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('img/custom_banner.jpg'); 
            background-color: #2c3e50; /* 图片加载失败时的底色 */
            background-size: cover;
            background-position: center;
            color: white;
            text-align: center;
            padding: 100px 20px;
            margin-bottom: 50px;
        }
        .custom-hero h1 { font-size: 3rem; margin-bottom: 15px; font-weight: 700; }
        .custom-hero p { font-size: 1.2rem; max-width: 700px; margin: 0 auto; opacity: 0.9; }

        /* --- Process Icons --- */
        .process-section {
            max-width: 1000px; margin: 0 auto 60px auto;
            display: flex; justify-content: space-around; flex-wrap: wrap; gap: 30px; text-align: center;
        }
        .process-item { flex: 1; min-width: 250px; }
        .p-icon { font-size: 3rem; color: #20c997; margin-bottom: 15px; display: block; }
        .p-title { font-weight: bold; font-size: 1.2rem; color: #2c3e50; margin-bottom: 10px; }
        .p-desc { color: #666; font-size: 0.9rem; line-height: 1.6; }

        /* --- Form Container --- */
        .inquiry-container {
            max-width: 800px;
            margin: 0 auto 80px auto;
            background: white;
            padding: 50px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        }

        .form-header { text-align: center; margin-bottom: 40px; }
        .form-header h2 { color: #20c997; font-size: 2rem; margin-bottom: 10px; }
        .form-header p { color: #777; }

        .form-group { margin-bottom: 25px; }
        .form-label { display: block; font-weight: 600; color: #444; margin-bottom: 8px; }
        
        .form-control {
            width: 100%;
            padding: 15px;
            border: 1px solid #e0e0e0;
            border-radius: 50px; 
            font-size: 1rem;
            outline: none;
            transition: 0.3s;
            background: #fcfcfc;
        }
        .form-control:focus { border-color: #20c997; background: white; box-shadow: 0 0 0 4px rgba(32, 201, 151, 0.1); }
        
        textarea.form-control { border-radius: 20px; resize: vertical; } 

        .btn-submit {
            width: 100%;
            padding: 18px;
            background-color: #20c997;
            color: white;
            border: none;
            border-radius: 50px;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
            box-shadow: 0 5px 15px rgba(32, 201, 151, 0.3);
        }
        .btn-submit:hover { background-color: #17a589; transform: translateY(-2px); }

        .file-upload-wrapper {
            position: relative;
            border: 2px dashed #ddd;
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            background: #fafafa;
            transition: 0.3s;
            cursor: pointer;
        }
        .file-upload-wrapper:hover { border-color: #20c997; background: #f0fdf9; }
        .file-upload-wrapper input[type="file"] {
            position: absolute; left: 0; top: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer;
        }
    </style>
</head>
<body>

    <?php include 'navbar.php'; ?> 

    <div class="custom-hero">
        <h1>Customize Your Dream Furniture</h1>
        <p>Can't find exactly what you're looking for? Let us build it for you. From unique dimensions to specific materials, we bring your vision to reality.</p>
    </div>

    <div class="page-content">
        
        <div class="process-section">
            <div class="process-item">
                <span class="p-icon">💡</span>
                <div class="p-title">1. Share Your Idea</div>
                <div class="p-desc">Fill out the form below. Tell us about your style, dimensions, and upload a sketch or reference photo.</div>
            </div>
            <div class="process-item">
                <span class="p-icon">💬</span>
                <div class="p-title">2. Get a Consultation</div>
                <div class="p-desc">Our design experts will contact you to discuss materials, pricing, and finalize the design details.</div>
            </div>
            <div class="process-item">
                <span class="p-icon">🔨</span>
                <div class="p-title">3. Production</div>
                <div class="p-desc">Once confirmed, our craftsmen will start building your unique piece with premium quality.</div>
            </div>
        </div>

        <div class="inquiry-container">
            <div class="form-header">
                <h2>Get a Free Quote</h2>
                <p>Please fill in the details below</p>
            </div>

            <form action="" method="POST" enctype="multipart/form-data">
                
                <div class="form-group">
                    <label class="form-label">Your Name *</label>
                    <input type="text" name="name" class="form-control" placeholder="Enter your full name" required 
                           value="<?php echo isset($_SESSION['username']) ? $_SESSION['username'] : ''; ?>">
                </div>

                <div class="row" style="display:flex; gap:20px; flex-wrap:wrap;">
                    <div class="form-group" style="flex:1; min-width:250px;">
                        <label class="form-label">Email Address *</label>
                        <input type="email" name="email" class="form-control" placeholder="name@example.com" required>
                    </div>
                    <div class="form-group" style="flex:1; min-width:250px;">
                        <label class="form-label">Contact Number *</label>
                        <input type="text" name="phone" class="form-control" placeholder="+60 12-345 6789" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Subject / Furniture Type *</label>
                    <input type="text" name="subject" class="form-control" placeholder="e.g. Custom Wardrobe, L-Shape Sofa" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Reference Image / Sketch (Optional)</label>
                    <div class="file-upload-wrapper">
                        <input type="file" name="ref_image" accept="image/*" onchange="previewFile(this)">
                        <div id="upload-text">
                            <span style="font-size:2rem; display:block; margin-bottom:10px;">📷</span>
                            <span style="color:#20c997; font-weight:bold;">Click to Upload Image</span>
                            <p style="color:#999; font-size:0.8rem; margin-top:5px;">JPG, PNG, WEBP (Max 5MB)</p>
                        </div>
                        <div id="preview-container" style="display:none; margin-top:10px;">
                            <p id="file-name" style="font-weight:bold; color:#333;"></p>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Message / Specifications *</label>
                    <textarea name="message" class="form-control" rows="6" placeholder="Tell us about the dimensions, materials, colors, or any specific requirements..." required></textarea>
                </div>

                <button type="submit" name="submit_inquiry" class="btn-submit">ENQUIRY NOW</button>

            </form>
        </div>

    </div>

    <script>
        window.onscroll = function() {
            const nav = document.querySelector('.navbar');
            if (nav) {
                if (window.scrollY > 20) { nav.classList.add('collapsed'); } 
                else { nav.classList.remove('collapsed'); }
            }
        };

        // 图片上传预览文字逻辑
        function previewFile(input) {
            const file = input.files[0];
            if (file) {
                document.getElementById('upload-text').style.display = 'none';
                document.getElementById('preview-container').style.display = 'block';
                document.getElementById('file-name').innerText = "Selected: " + file.name;
            }
        }

        // SweetAlert
        <?php if(isset($_SESSION['swal'])): ?>
            Swal.fire({
                icon: '<?php echo $_SESSION['swal']['type']; ?>',
                title: '<?php echo $_SESSION['swal']['title']; ?>',
                text: '<?php echo $_SESSION['swal']['text']; ?>',
                confirmButtonColor: '#20c997'
            });
            <?php unset($_SESSION['swal']); ?>
        <?php endif; ?>
    </script>
</body>
</html>