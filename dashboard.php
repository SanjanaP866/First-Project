<?php
require_once 'header.php';

// Require login for this page
requireLogin();

// Get user details
$user = getUserDetails($conn, $_SESSION['user_id']);

// Get recent tax records
$tax_stmt = $conn->prepare("SELECT * FROM tax_records WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
$tax_stmt->bind_param("i", $_SESSION['user_id']);
$tax_stmt->execute();
$recent_records = $tax_stmt->get_result();
?>

<div class="container content py-5">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2>Welcome, <?php echo htmlspecialchars($user['full_name']); ?>!</h2>
            <p class="lead">Manage your tax calculations and financial planning in one place.</p>
        </div>
        <div class="col-md-4 text-md-end">
            <a href="taxcalc.php" class="btn btn-primary btn-lg">
                <i class="fas fa-calculator me-2"></i>Calculate Tax
            </a>
        </div>
    </div>
    
    <div class="row mb-4">
        <div class="col-lg-6 mb-4 mb-lg-0">
            <div class="card dashboard-card h-100">
                <div class="card-header bg-primary text-white">
                    <h4 class="m-0">Quick Actions</h4>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        <a href="taxcalc.php" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-calculator me-2 text-primary"></i>
                                Calculate Your Tax
                            </div>
                            <span class="badge bg-primary rounded-pill">
                                <i class="fas fa-chevron-right"></i>
                            </span>
                        </a>
                        <a href="history.php" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-history me-2 text-primary"></i>
                                View Tax History
                            </div>
                            <span class="badge bg-primary rounded-pill">
                                <i class="fas fa-chevron-right"></i>
                            </span>
                        </a>
                        <a href="profile.php" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-user-edit me-2 text-primary"></i>
                                Update Profile
                            </div>
                            <span class="badge bg-primary rounded-pill">
                                <i class="fas fa-chevron-right"></i>
                            </span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-6">
            <div class="card dashboard-card h-100">
                <div class="card-header bg-primary text-white">
                    <h4 class="m-0">Account Information</h4>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between">
                            <span><strong>Username:</strong></span>
                            <span><?php echo htmlspecialchars($user['username']); ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span><strong>Email:</strong></span>
                            <span><?php echo htmlspecialchars($user['email']); ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span><strong>Full Name:</strong></span>
                            <span><?php echo htmlspecialchars($user['full_name']); ?></span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-12">
            <div class="card dashboard-card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h4 class="m-0">Recent Tax Calculations</h4>
                    <a href="history.php" class="btn btn-sm btn-light">View All</a>
                </div>
                <div class="card-body">
                    <?php if ($recent_records->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Financial Year</th>
                                        <th>Total Income</th>
                                        <th>Taxable Income</th>
                                        <th>Tax Amount</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($record = $recent_records->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($record['financial_year']); ?></td>
                                            <td>₹<?php echo number_format($record['total_income'], 2); ?></td>
                                            <td>₹<?php echo number_format($record['taxable_income'], 2); ?></td>
                                            <td>₹<?php echo number_format($record['total_tax_payable'], 2); ?></td>
                                            <td><?php echo date('d M Y', strtotime($record['created_at'])); ?></td>
                                            <td>
                                                <a href="view_tax.php?id=<?php echo $record['id']; ?>" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="download_pdf.php?id=<?php echo $record['id']; ?>" class="btn btn-sm btn-secondary">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            You haven't made any tax calculations yet. 
                            <a href="tax_calculator.php" class="alert-link">Calculate your tax now</a>.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once 'footer.php';
?>