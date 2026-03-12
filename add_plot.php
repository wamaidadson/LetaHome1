<?php
require_once 'config.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $plot_name = mysqli_real_escape_string($conn, $_POST['plot_name']);
    $location = mysqli_real_escape_string($conn, $_POST['location']);
    
    $query = "INSERT INTO plots (plot_name, location) VALUES ('$plot_name', '$location')";
    
    if (mysqli_query($conn, $query)) {
        header('Location: plots.php?msg=added');
        exit();
    } else {
        $error = "Error adding plot: " . mysqli_error($conn);
    }
}

$page_title = 'Add Plot - Leta Homes Agency';
?>
<?php include 'header.php'; ?>
    
    <div class="layout-wrapper">
        <?php include 'sidebar.php'; ?>
        
        <div class="main-content">
            <div class="container-fluid py-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-plus-circle"></i> Add New Plot</h2>
                <a href="plots.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Plots
                </a>
            </div>
            
            <div class="row">
                <div class="col-md-8 offset-md-2">
                    <div class="table-container">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="plot_name" class="form-label">Plot Name *</label>
                                <input type="text" class="form-control" id="plot_name" name="plot_name" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="location" class="form-label">Location</label>
                                <input type="text" class="form-control" id="location" name="location">
                                <div class="form-text">Optional: Enter the physical location of the plot</div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Plot
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'footer.php'; ?>
</body>
</html>

