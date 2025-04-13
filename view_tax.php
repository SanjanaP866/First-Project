<?php
require_once 'header.php';

// Require login for this page
requireLogin();

// Check if tax record ID is provided
if (!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$record_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];

// Get tax record details from database
$stmt = $conn->prepare("SELECT * FROM tax_records WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $record_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Record not found or doesn't belong to this user
    header("Location: dashboard.php");
    exit();
}

$tax_record = $result->fetch_assoc();
?>

<div class="container content py-5">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2>Tax Calculation Details</h2>
            <p class="lead">Financial Year: <?php echo htmlspecialchars($tax_record['financial_year']); ?></p>
        </div>
        <div class="col-md-4 text-md-end">
            <a href="download_pdf.php?id=<?php echo $record_id; ?>" class="btn btn-primary" target="_blank">
                <i class="fas fa-file-pdf me-2"></i>Download PDF
            </a>
            <a href="dashboard.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Income Details</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tbody>
                            <tr>
                                <td>Basic Salary</td>
                                <td class="text-end">₹<?php echo number_format($tax_record['basic_salary'], 2); ?></td>
                            </tr>
                            <tr>
                                <td>HRA</td>
                                <td class="text-end">₹<?php echo number_format($tax_record['hra'], 2); ?></td>
                            </tr>
                            <tr>
                                <td>Special Allowance</td>
                                <td class="text-end">₹<?php echo number_format($tax_record['special_allowance'], 2); ?></td>
                            </tr>
                            <tr>
                                <td>Bonus</td>
                                <td class="text-end">₹<?php echo number_format($tax_record['bonus'], 2); ?></td>
                            </tr>
                            <tr>
                                <td>Other Income</td>
                                <td class="text-end">₹<?php echo number_format($tax_record['other_income'], 2); ?></td>
                            </tr>
                            <tr class="table-primary">
                                <th>Total Income</th>
                                <th class="text-end">₹<?php echo number_format($tax_record['total_income'], 2); ?></th>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Deductions</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tbody>
                            <tr>
                                <td>Standard Deduction</td>
                                <td class="text-end">₹<?php echo number_format($tax_record['standard_deduction'], 2); ?></td>
                            </tr>
                            <tr>
                                <td>Section 80C</td>
                                <td class="text-end">₹<?php echo number_format($tax_record['section_80c'], 2); ?></td>
                            </tr>
                            <tr>
                                <td>Section 80D</td>
                                <td class="text-end">₹<?php echo number_format($tax_record['section_80d'], 2); ?></td>
                            </tr>
                            <tr>
                                <td>Section 80G</td>
                                <td class="text-end">₹<?php echo number_format($tax_record['section_80g'], 2); ?></td>
                            </tr>
                            <tr>
                                <td>Other Deductions</td>
                                <td class="text-end">₹<?php echo number_format($tax_record['other_deductions'], 2); ?></td>
                            </tr>
                            <tr class="table-primary">
                                <th>Total Deductions</th>
                                <th class="text-end">₹<?php echo number_format($tax_record['total_deductions'], 2); ?></th>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Tax Computation</h5>
        </div>
        <div class="card-body">
            <table class="table">
                <tbody>
                    <tr>
                        <td>Taxable Income</td>
                        <td class="text-end">₹<?php echo number_format($tax_record['taxable_income'], 2); ?></td>
                    </tr>
                    <tr>
                        <td>Tax Amount</td>
                        <td class="text-end">₹<?php echo number_format($tax_record['tax_amount'], 2); ?></td>
                    </tr>
                    <tr>
                        <td>Health & Education Cess (4%)</td>
                        <td class="text-end">₹<?php echo number_format($tax_record['cess_amount'], 2); ?></td>
                    </tr>
                    <tr class="table-primary">
                        <th>Total Tax Payable</th>
                        <th class="text-end">₹<?php echo number_format($tax_record['total_tax_payable'], 2); ?></th>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>