<?php
session_start();
include 'db_conn.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: LOGIN-REGISTER.php");
    exit();
}

$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$sql = "SELECT * FROM users WHERE role = 'customer'";

if (!empty($search)) {
    $sql .= " AND (username LIKE '%$search%' OR email LIKE '%$search%')";
}
$sql .= " ORDER BY created_at DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Customers</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="admin_style.css">
</head>
<body>
    <div class="d-flex" id="wrapper">
        <?php include 'admin_sidebar.php'; ?>

        <div id="page-content-wrapper">
            <div class="container-fluid p-4">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body">
                        <form method="GET" class="d-flex gap-2">
                            <input type="text" name="search" class="form-control" placeholder="Search Name or Email..." value="<?php echo htmlspecialchars($search); ?>">
                            <button type="submit" class="btn btn-dark">Search</button>
                            <a href="admin_customers.php" class="btn btn-light border">Reset</a>
                        </form>
                    </div>
                </div>

                <div class="card shadow-sm border-0">
                    <div class="card-body p-0">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">ID</th>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Joined Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td class="ps-4">#<?php echo $row['user_id']; ?></td>
                                    <td><?php echo $row['username']; ?></td>
                                    <td><?php echo $row['email']; ?></td>
                                    <td><?php echo date("d M Y", strtotime($row['created_at'])); ?></td>
                                    <td>
                                        <a href="admin_customer_details.php?id=<?php echo $row['user_id']; ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="admin_script.js"></script>
</body>
</html>