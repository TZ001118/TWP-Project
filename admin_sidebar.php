<div class="border-end" id="sidebar-wrapper">
    <div class="sidebar-heading border-bottom bg-dark text-white">
        <i class="bi bi-box-seam-fill me-2" style="color: #99d5c5;"></i>
        <span class="sidebar-text">Furniture Direct</span> 
    </div>
    <div class="list-group list-group-flush">
        <a href="admin_dashboard.php" class="list-group-item list-group-item-action <?php echo basename($_SERVER['PHP_SELF']) == 'admin_dashboard.php' ? 'active' : ''; ?>">
            <i class="bi bi-grid-1x2-fill me-3"></i><span class="sidebar-text">Dashboard</span> 
        </a>
        <a href="admin_orders.php" class="list-group-item list-group-item-action <?php echo basename($_SERVER['PHP_SELF']) == 'admin_orders.php' ? 'active' : ''; ?>">
            <i class="bi bi-cart3 me-3"></i><span class="sidebar-text">Orders</span> 
        </a>
        <a href="admin_customers.php" class="list-group-item list-group-item-action <?php echo basename($_SERVER['PHP_SELF']) == 'admin_customers.php' ? 'active' : ''; ?>">
            <i class="bi bi-people-fill me-3"></i><span class="sidebar-text">Customers</span> 
        </a>
        <a href="admin_profile.php" class="list-group-item list-group-item-action mt-5 border-top border-secondary pt-3 <?php echo basename($_SERVER['PHP_SELF']) == 'admin_profile.php' ? 'active' : ''; ?>">
            <i class="bi bi-person-circle me-3"></i><span class="sidebar-text">Admin Profile</span> 
        </a>
    </div>
</div>