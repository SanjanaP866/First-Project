<?php
require_once 'header.php';
require('lib/FPDF-master/fpdf.php');

// Require login for this page
requireLogin();

$financial_years = ["2024-25", "2023-24", "2022-23"];
$success = false;
$tax_data = null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $financial_year = $_POST['financial_year'];
    $tax_regime = $_POST['tax_regime']; // New or Old Regime
    
    $basic_salary = floatval($_POST['basic_salary']);
    $hra = floatval($_POST['hra']);
    $special_allowance = floatval($_POST['special_allowance']);
    $bonus = floatval($_POST['bonus']);
    $other_income = floatval($_POST['other_income']);
    
    $section_80c = floatval($_POST['section_80c']);
    $section_80d = floatval($_POST['section_80d']);
    $section_80g = floatval($_POST['section_80g']);
    $other_deductions = floatval($_POST['other_deductions']);
    
    $total_income = $basic_salary + $hra + $special_allowance + $bonus + $other_income;
    $standard_deduction = 50000;
    $total_deductions = 0;
    $taxable_income = 0;
    $tax_amount = 0;
    $cess_amount = 0;
    $total_tax_payable = 0;
    
    if ($tax_regime === "old") {
        // Old Regime - Apply Deductions
        $section_80c = min($section_80c, 150000); // Section 80C cap
        $total_deductions = $standard_deduction + $section_80c + $section_80d + $section_80g + $other_deductions;
        $taxable_income = max(0, $total_income - $total_deductions);
        
        // Old Tax Regime Slabs
        if ($taxable_income <= 250000) {
            $tax_amount = 0;
        } elseif ($taxable_income <= 500000) {
            $tax_amount = ($taxable_income - 250000) * 0.05;
        } elseif ($taxable_income <= 1000000) {
            $tax_amount = (250000 * 0.05) + ($taxable_income - 500000) * 0.2;
        } else {
            $tax_amount = (250000 * 0.05) + (500000 * 0.2) + ($taxable_income - 1000000) * 0.3;
        }
        
    } else {
        // New Regime - No Deductions, Different Slabs
        $taxable_income = $total_income; // No deductions in the new regime
        
        if ($taxable_income <= 300000) {
            $tax_amount = 0;
        } elseif ($taxable_income <= 600000) {
            $tax_amount = ($taxable_income - 300000) * 0.05;
        } elseif ($taxable_income <= 900000) {
            $tax_amount = (300000 * 0.05) + ($taxable_income - 600000) * 0.1;
        } elseif ($taxable_income <= 1200000) {
            $tax_amount = (300000 * 0.05) + (300000 * 0.1) + ($taxable_income - 900000) * 0.15;
        } elseif ($taxable_income <= 1500000) {
            $tax_amount = (300000 * 0.05) + (300000 * 0.1) + (300000 * 0.15) + ($taxable_income - 1200000) * 0.2;
        } else {
            $tax_amount = (300000 * 0.05) + (300000 * 0.1) + (300000 * 0.15) + (300000 * 0.2) + ($taxable_income - 1500000) * 0.3;
        }
    }
    
    // Section 87A rebate (tax becomes 0 if taxable income <= ₹5L in Old Regime, ₹7L in New Regime)
    if (($tax_regime === "old" && $taxable_income <= 500000) || ($tax_regime === "new" && $taxable_income <= 700000)) {
        $tax_amount = 0;
    }
    
    // Cess Calculation (4% of tax)
    $cess_amount = $tax_amount * 0.04;
    $total_tax_payable = $tax_amount + $cess_amount;
    
    // Save calculation to database
    $stmt = $conn->prepare("INSERT INTO tax_records (user_id, financial_year, total_income, basic_salary, hra, 
                           special_allowance, bonus, other_income, standard_deduction, section_80c, section_80d, 
                           section_80g, other_deductions, total_deductions, taxable_income, tax_amount, 
                           cess_amount, total_tax_payable) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $stmt->bind_param("isdddddddddddddddd", 
                      $_SESSION['user_id'], 
                      $financial_year, 
                      $total_income, 
                      $basic_salary, 
                      $hra, 
                      $special_allowance, 
                      $bonus, 
                      $other_income, 
                      $standard_deduction, 
                      $section_80c, 
                      $section_80d, 
                      $section_80g, 
                      $other_deductions, 
                      $total_deductions, 
                      $taxable_income, 
                      $tax_amount, 
                      $cess_amount, 
                      $total_tax_payable);
    
    if ($stmt->execute()) {
        $record_id = $conn->insert_id;
        $success = true;
        
        // Store calculation data in session for PDF download
        $_SESSION['tax_calculation'] = [
            'id' => $record_id,
            'financial_year' => $financial_year,
            'tax_regime' => $tax_regime,
            'basic_salary' => $basic_salary,
            'hra' => $hra,
            'special_allowance' => $special_allowance,
            'bonus' => $bonus,
            'other_income' => $other_income,
            'total_income' => $total_income,
            'standard_deduction' => $standard_deduction,
            'section_80c' => $section_80c,
            'section_80d' => $section_80d,
            'section_80g' => $section_80g,
            'other_deductions' => $other_deductions,
            'total_deductions' => $total_deductions,
            'taxable_income' => $taxable_income,
            'tax_amount' => $tax_amount,
            'cess_amount' => $cess_amount,
            'total_tax_payable' => $total_tax_payable
        ];
    } else {
        // Handle database error
        $error = "Database error: " . $conn->error;
    }
}
?>

<div class="container content py-5">
    <h2>Tax Calculator</h2>
    <p class="lead">Choose your tax regime and enter your details to calculate tax.</p>

    <form method="post">
        <div class="card calculator-form mb-4">
            <div class="card-body">
                <div class="mb-3">
                    <label for="financial_year" class="form-label">Financial Year:</label>
                    <select name="financial_year" id="financial_year" class="form-control">
                        <?php foreach ($financial_years as $year): ?>
                            <option value="<?php echo $year; ?>"><?php echo $year; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="tax_regime" class="form-label">Select Tax Regime:</label>
                    <select name="tax_regime" id="tax_regime" class="form-control" required onchange="toggleDeductions()">
                        <option value="old">Old Regime (with deductions)</option>
                        <option value="new">New Regime (lower tax, no deductions)</option>
                    </select>
                </div>

                <h4 class="mt-4">Income Details</h4>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="basic_salary" class="form-label">Basic Salary:</label>
                        <input type="number" name="basic_salary" id="basic_salary" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="hra" class="form-label">HRA:</label>
                        <input type="number" name="hra" id="hra" class="form-control" value="0" required>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="special_allowance" class="form-label">Special Allowance:</label>
                        <input type="number" name="special_allowance" id="special_allowance" class="form-control" value="0" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="bonus" class="form-label">Bonus:</label>
                        <input type="number" name="bonus" id="bonus" class="form-control" value="0" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="other_income" class="form-label">Other Income:</label>
                        <input type="number" name="other_income" id="other_income" class="form-control" value="0" required>
                    </div>
                </div>

                <div id="deductions-section">
                    <h4 class="mt-4">Deductions</h4>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="section_80c" class="form-label">Section 80C (Max ₹1,50,000):</label>
                            <input type="number" name="section_80c" id="section_80c" class="form-control" value="0" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="section_80d" class="form-label">Section 80D (Health Insurance):</label>
                            <input type="number" name="section_80d" id="section_80d" class="form-control" value="0" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="section_80g" class="form-label">Section 80G (Donations):</label>
                            <input type="number" name="section_80g" id="section_80g" class="form-control" value="0" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="other_deductions" class="form-label">Other Deductions:</label>
                            <input type="number" name="other_deductions" id="other_deductions" class="form-control" value="0" required>
                        </div>
                    </div>
                </div>

                <div class="mt-3">
                    <button type="submit" class="btn btn-primary w-100">Calculate Tax</button>
                </div>
            </div>
        </div>
    </form>

    <?php if ($success): ?>
        <div class="results-section">
            <h3 class="mb-4">Tax Calculation Result</h3>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="card mb-3">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Income Summary</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm">
                                <tbody>
                                    <tr>
                                        <td>Basic Salary</td>
                                        <td class="text-end">₹<?php echo number_format($basic_salary, 2); ?></td>
                                    </tr>
                                    <tr>
                                        <td>HRA</td>
                                        <td class="text-end">₹<?php echo number_format($hra, 2); ?></td>
                                    </tr>
                                    <tr>
                                        <td>Special Allowance</td>
                                        <td class="text-end">₹<?php echo number_format($special_allowance, 2); ?></td>
                                    </tr>
                                    <tr>
                                        <td>Bonus</td>
                                        <td class="text-end">₹<?php echo number_format($bonus, 2); ?></td>
                                    </tr>
                                    <tr>
                                        <td>Other Income</td>
                                        <td class="text-end">₹<?php echo number_format($other_income, 2); ?></td>
                                    </tr>
                                    <tr class="table-primary">
                                        <th>Total Income</th>
                                        <th class="text-end">₹<?php echo number_format($total_income, 2); ?></th>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card mb-3">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Tax Details</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm">
                                <tbody>
                                    <tr>
                                        <td>Financial Year</td>
                                        <td class="text-end"><?php echo htmlspecialchars($financial_year); ?></td>
                                    </tr>
                                    <tr>
                                        <td>Tax Regime</td>
                                        <td class="text-end"><?php echo ($tax_regime === 'old') ? 'Old Regime' : 'New Regime'; ?></td>
                                    </tr>
                                    <tr>
                                        <td>Taxable Income</td>
                                        <td class="text-end">₹<?php echo number_format($taxable_income, 2); ?></td>
                                    </tr>
                                    <tr>
                                        <td>Tax Amount</td>
                                        <td class="text-end">₹<?php echo number_format($tax_amount, 2); ?></td>
                                    </tr>
                                    <tr>
                                        <td>Health & Education Cess (4%)</td>
                                        <td class="text-end">₹<?php echo number_format($cess_amount, 2); ?></td>
                                    </tr>
                                    <tr class="table-primary">
                                        <th>Total Tax Payable</th>
                                        <th class="text-end">₹<?php echo number_format($total_tax_payable, 2); ?></th>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="text-center mt-4">
                <a href="download_pdf.php?id=<?php echo $record_id; ?>" class="btn btn-primary btn-lg" target="_blank">
                    <i class="fas fa-file-pdf me-2"></i>Download Tax Report as PDF
                </a>
            </div>
        </div>
    <?php elseif (isset($error)): ?>
        <div class="alert alert-danger">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>
</div>

<script>
function toggleDeductions() {
    const taxRegime = document.getElementById("tax_regime").value;
    const deductionsSection = document.getElementById("deductions-section");
    
    if (taxRegime === "new") {
        deductionsSection.style.display = "none";
    } else {
        deductionsSection.style.display = "block";
    }
}

// Call function on page load
document.addEventListener('DOMContentLoaded', function() {
    toggleDeductions();
});
</script>

<?php require_once 'footer.php'; ?>