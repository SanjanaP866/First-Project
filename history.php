<?php
require_once 'header.php';

// Require login for this page
requireLogin();

$user_id = $_SESSION['user_id'];

// Get all tax records for the current user
$stmt = $conn->prepare("SELECT * FROM tax_records WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="container content py-5">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2>Tax Calculation History</h2>
            <p class="lead">View your previous tax calculations</p>
        </div>
        <div class="col-md-4 text-md-end">
            <a href="dashboard.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
            </a>
        </div>
    </div>

    <?php if ($result->num_rows > 0): ?>
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Financial Year</th>
                                <th>Total Income</th>
                                <th>Total Deductions</th>
                                <th>Taxable Income</th>
                                <th>Total Tax Payable</th>
                                <th>Date Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($record = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($record['financial_year']); ?></td>
                                    <td>₹<?php echo number_format($record['total_income'], 2); ?></td>
                                    <td>₹<?php echo number_format($record['total_deductions'], 2); ?></td>
                                    <td>₹<?php echo number_format($record['taxable_income'], 2); ?></td>
                                    <td>₹<?php echo number_format($record['total_tax_payable'], 2); ?></td>
                                    <td><?php echo date('d M Y', strtotime($record['created_at'])); ?></td>
                                    <td>
                                        <a href="view_tax.php?id=<?php echo $record['id']; ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                        <a href="download_pdf.php?id=<?php echo $record['id']; ?>" class="btn btn-sm btn-success" target="_blank">
                                            <i class="fas fa-file-pdf"></i> PDF
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>You haven't created any tax calculations yet.
            <a href="calculate_tax.php" class="alert-link">Calculate your tax now</a>.
        </div>
    <?php endif; ?>
</div>

<?php require_once 'footer.php'; ?> 