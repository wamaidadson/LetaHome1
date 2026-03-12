<?php
require_once 'config.php';
requireLogin();

// Handle plot deletion
if (isset($_GET['delete'])) {
    $id = mysqli_real_escape_string($conn, $_GET['delete']);
    mysqli_query($conn, "DELETE FROM plots WHERE id = $id");
    header('Location: plots.php?msg=deleted');
    exit();
}

// Get all plots
$plots = mysqli_query($conn, "SELECT p.*, 
                              (SELECT COUNT(*) FROM tenants WHERE plot_id = p.id AND status='Active') as total_tenants,
                              (SELECT COALESCE(SUM(rent_amount), 0) FROM tenants WHERE plot_id = p.id AND status='Active') as total_rent
                              FROM plots p
                              ORDER BY p.created_at DESC");

$page_title = 'Plots Management - Leta Homes Agency';
?>
<?php include 'header.php'; ?>
    
    <div class="layout-wrapper">
        <?php include 'sidebar.php'; ?>
        
        <div class="main-content">
            <div class="container-fluid py-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-building"></i> Plots Management</h2>
                <a href="add_plot.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add New Plot
                </a>
            </div>
            
            <?php if (isset($_GET['msg'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php 
                    if ($_GET['msg'] == 'added') echo 'Plot added successfully!';
                    if ($_GET['msg'] == 'updated') echo 'Plot updated successfully!';
                    if ($_GET['msg'] == 'deleted') echo 'Plot deleted successfully!';
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <div class="table-container">
                <table class="table table-hover datatable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Plot Name</th>
                            <th>Location</th>
                            <th>Total Tenants</th>
                            <th>Monthly Rent</th>
                            <th>Created Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($plots)): ?>
                        <tr>
                            <td>#<?php echo $row['id']; ?></td>
                            <td><?php echo $row['plot_name']; ?></td>
                            <td><?php echo $row['location'] ?: 'N/A'; ?></td>
                            <td><?php echo $row['total_tenants']; ?></td>
                            <td><?php echo formatMoney($row['total_rent']); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($row['created_at'])); ?></td>
                            <td>
                                <a href="plot_details.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-info btn-action" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="edit_plot.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-warning btn-action" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="?delete=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger btn-action" title="Delete" 
                                   onclick="return confirm('Are you sure you want to delete this plot? This will also delete all associated tenants and payments.')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <?php include 'footer.php'; ?>
</body>
</html>

