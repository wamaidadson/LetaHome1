<?php
require_once 'config.php';
requireLogin();

$search_term = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$search_results = [];

if (!empty($search_term)) {
    // Search tenants
    $search_results = mysqli_query($conn,
        "SELECT t.*, p.plot_name,
         'Tenant' as result_type
         FROM tenants t
         JOIN plots p ON t.plot_id = p.id
         WHERE t.tenant_name LIKE '%$search_term%'
            OR t.tenant_id LIKE '%$search_term%'
            OR t.house_number LIKE '%$search_term%'
            OR t.phone_number LIKE '%$search_term%'
         UNION
         SELECT t.*, p.plot_name,
         'Payment' as result_type
         FROM rent_payments rp
         JOIN tenants t ON rp.tenant_id = t.id
         JOIN plots p ON t.plot_id = p.id
         WHERE DATE_FORMAT(rp.payment_date, '%d/%m/%Y') LIKE '%$search_term%'
            OR rp.amount_paid LIKE '%$search_term%'
         UNION
         SELECT t.*, p.plot_name,
         'Receipt' as result_type
         FROM receipts r
         JOIN rent_payments rp ON r.payment_id = rp.id
         JOIN tenants t ON rp.tenant_id = t.id
         JOIN plots p ON t.plot_id = p.id
         WHERE r.receipt_number LIKE '%$search_term%'
         ORDER BY result_type"
    );
}

$page_title = 'Search - Leta Homes Agency';
?>
<?php include 'header.php'; ?>
    
    <div class="layout-wrapper">
        <?php include 'sidebar.php'; ?>
        
        <div class="main-content">
            <div class="container-fluid py-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-search"></i> Search</h2>
            </div>
            
            <div class="row">
                <div class="col-md-8 offset-md-2">
                    <div class="table-container">
                        <form method="GET" action="" class="mb-4">
                            <div class="input-group">
                                <input type="text" class="form-control form-control-lg" 
                                       name="search" placeholder="Search by tenant name, ID, house number, phone, receipt number, payment date..." 
                                       value="<?php echo htmlspecialchars($search_term); ?>">
                                <button class="btn btn-primary" type="submit">
                                    <i class="fas fa-search"></i> Search
                                </button>
                            </div>
                            <div class="form-text mt-2">
                                <small>You can search for: Tenant Name, Tenant ID, House Number, Phone Number, Payment Date (DD/MM/YYYY), Receipt Number</small>
                            </div>
                        </form>
                        
                        <?php if (!empty($search_term)): ?>
                            <h5 class="mb-3">Search Results for "<?php echo htmlspecialchars($search_term); ?>"</h5>
                            
                            <?php if (mysqli_num_rows($search_results) > 0): ?>
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Result Type</th>
                                            <th>Tenant ID</th>
                                            <th>Tenant Name</th>
                                            <th>Plot</th>
                                            <th>House No</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($result = mysqli_fetch_assoc($search_results)): ?>
                                        <tr>
                                            <td>
                                                <span class="badge <?php 
                                                    echo $result['result_type'] == 'Tenant' ? 'badge-active' : 
                                                         ($result['result_type'] == 'Payment' ? 'badge-paid' : 'badge-warning'); 
                                                ?>">
                                                    <?php echo $result['result_type']; ?>
                                                </span>
                                            </td>
                                            <td><?php echo $result['tenant_id']; ?></td>
                                            <td><?php echo $result['tenant_name']; ?></td>
                                            <td><?php echo $result['plot_name']; ?></td>
                                            <td><?php echo $result['house_number']; ?></td>
                                            <td>
                                                <a href="tenant_statement.php?id=<?php echo $result['id']; ?>" class="btn btn-sm btn-info">
                                                    <i class="fas fa-file-invoice"></i> View
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <div class="alert alert-info">
                                    No results found for "<?php echo htmlspecialchars($search_term); ?>"
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'footer.php'; ?>
</body>
</html>

