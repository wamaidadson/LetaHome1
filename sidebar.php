<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>

<div class="sidebar">
    <div class="sidebar-header">
        <h3>Leta Homes</h3>
        <p>Agency Management</p>
    </div>
    
    <div class="sidebar-menu">
        <a href="dashboard.php" class="<?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">
            <i class="fas fa-dashboard"></i> Dashboard
        </a>
        
        <a href="plots.php" class="<?php echo $current_page == 'plots.php' ? 'active' : ''; ?>">
            <i class="fas fa-building"></i> Plots
        </a>
        
        <a href="tenants.php" class="<?php echo $current_page == 'tenants.php' ? 'active' : ''; ?>">
            <i class="fas fa-users"></i> Tenants
        </a>
        
        <a href="payments.php" class="<?php echo $current_page == 'payments.php' ? 'active' : ''; ?>">
            <i class="fas fa-money-bill"></i> Payments
        </a>
        
        <a href="receipts.php" class="<?php echo $current_page == 'receipts.php' ? 'active' : ''; ?>">
            <i class="fas fa-receipt"></i> Receipts
        </a>
        
        <a href="statements.php" class="<?php echo $current_page == 'statements.php' ? 'active' : ''; ?>">
            <i class="fas fa-file-invoice"></i> Statements
        </a>
        
        <a href="arrears.php" class="<?php echo $current_page == 'arrears.php' ? 'active' : ''; ?>">
            <i class="fas fa-exclamation-triangle"></i> Arrears
        </a>
        
        <a href="reports.php" class="<?php echo $current_page == 'reports.php' ? 'active' : ''; ?>">
            <i class="fas fa-chart-bar"></i> Reports
        </a>
        
        <a href="search.php" class="<?php echo $current_page == 'search.php' ? 'active' : ''; ?>">
            <i class="fas fa-search"></i> Search
        </a>
        
        <a href="logout.php" class="text-danger">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
    
    <div class="sidebar-footer">
        <p>&copy; 2026 Leta Homes</p>
    </div>
</div>

