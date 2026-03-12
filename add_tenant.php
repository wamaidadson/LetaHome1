<?php
require_once 'config.php';
requireLogin();

$plot_id = isset($_GET['plot_id']) ? mysqli_real_escape_string($conn, $_GET['plot_id']) : '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tenant_id = generateTenantID($conn);
    $plot_id = mysqli_real_escape_string($conn, $_POST['plot_id']);
    $tenant_name = mysqli_real_escape_string($conn, $_POST['tenant_name']);
    $phone_number = mysqli_real_escape_string($conn, $_POST['phone_number']);
    $house_number = mysqli_real_escape_string($conn, $_POST['house_number']);
    $house_type = mysqli_real_escape_string($conn, $_POST['house_type']);
    $rent_amount = mysqli_real_escape_string($conn, $_POST['rent_amount']);
    $commission_percentage = mysqli_real_escape_string($conn, $_POST['commission_percentage']);
    $move_in_date = mysqli_real_escape_string($conn, $_POST['move_in_date']);
    
    $query = "INSERT INTO tenants (tenant_id, plot_id, tenant_name, phone_number, house_number, house_type, 
                                   rent_amount, commission_percentage, move_in_date, status) 
              VALUES ('$tenant_id', '$plot_id', '$tenant_name', '$phone_number', '$house_number', '$house_type', 
                      '$rent_amount', '$commission_percentage', '$move_in_date', 'Active')";
    
    if (mysqli_query($conn, $query)) {
        header('Location: plot_details.php?id=' . $plot_id . '&msg=tenant_added');
        exit();
    } else {
        $error = "Error adding tenant: " . mysqli_error($conn);
    }
}

// Get all plots for dropdown
$plots = mysqli_query($conn, "SELECT id, plot_name FROM plots ORDER BY plot_name");

$page_title = 'Add Tenant - Leta Homes Agency';
?>
<?php include 'header.php'; ?>
    
    <div class="layout-wrapper">
        <?php include 'sidebar.php'; ?>
        
        <div class="main-content">
            <div class="container-fluid py-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-user-plus"></i> Add New Tenant</h2>
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
                                <label for="plot_id" class="form-label">Select Plot *</label>
                                <select class="form-control" id="plot_id" name="plot_id" required>
                                    <option value="">-- Select Plot --</option>
                                    <?php while ($plot = mysqli_fetch_assoc($plots)): ?>
                                    <option value="<?php echo $plot['id']; ?>" <?php echo $plot_id == $plot['id'] ? 'selected' : ''; ?>>
                                        <?php echo $plot['plot_name']; ?>
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="tenant_name" class="form-label">Tenant Name *</label>
                                    <input type="text" class="form-control" id="tenant_name" name="tenant_name" required>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="phone_number" class="form-label">Phone Number</label>
                                    <input type="text" class="form-control" id="phone_number" name="phone_number">
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="house_number" class="form-label">House Number *</label>
                                    <input type="text" class="form-control" id="house_number" name="house_number" required>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="house_type" class="form-label">House Type *</label>
                                    <select class="form-control" id="house_type" name="house_type" required>
                                        <option value="Bedsitter">Bedsitter</option>
                                        <option value="One Bedroom">One Bedroom</option>
                                        <option value="Two Bedroom">Two Bedroom</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="rent_amount" class="form-label">Rent Amount (KSh) *</label>
                                    <input type="number" class="form-control" id="rent_amount" name="rent_amount" required>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="commission_percentage" class="form-label">Commission Percentage (%) *</label>
                                    <input type="number" class="form-control" id="commission_percentage" name="commission_percentage" value="10" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="move_in_date" class="form-label">Move In Date *</label>
                                <input type="date" class="form-control" id="move_in_date" name="move_in_date" 
                                       value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Add Tenant
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

