<?php
require_once 'config.php';
require('lib/FPDF-master/fpdf.php');
session_start();

// Require login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

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

// Get user details
$user_stmt = $conn->prepare("SELECT full_name, email FROM users WHERE id = ?");
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user = $user_result->fetch_assoc();

// Create PDF
class TaxPDF extends FPDF {
    function Header() {
        $this->SetFont('Arial', 'B', 20);
        $this->SetTextColor(0, 102, 204);
        $this->Cell(0, 15, 'Tax Ease - Tax Calculation Report', 0, 1, 'C');
        $this->SetFont('Arial', 'I', 10);
        $this->SetTextColor(0);
        $this->Cell(0, 10, 'Generated on: ' . date('d-m-Y'), 0, 1, 'R');
        $this->Ln(5);
    }
    
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Page ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }

    function SectionTitle($title) {
        $this->SetFont('Arial', 'B', 12);
        $this->SetFillColor(220, 230, 240);
        $this->Cell(0, 8, $title, 1, 1, 'L', true);
        $this->Ln(2);
    }

    function DataRow($label, $value, $isHighlighted = false) {
        if ($isHighlighted) {
            $this->SetFont('Arial', 'B', 11);
        } else {
            $this->SetFont('Arial', '', 11);
        }
        
        $this->Cell(100, 8, $label, 1);
        $this->Cell(90, 8, $value, 1, 1, 'R');
    }
}

// Initialize PDF
$pdf = new TaxPDF();
$pdf->AliasNbPages();
$pdf->AddPage();

// User Information
$pdf->SectionTitle('User Information');
$pdf->DataRow('Name', $user['full_name']);
$pdf->DataRow('Email', $user['email']);
$pdf->Ln(5);

// Tax Year & Regime
$pdf->SectionTitle('Tax Assessment Details');
$pdf->DataRow('Financial Year', $tax_record['financial_year']);
$pdf->DataRow('Tax Regime', ($tax_record['total_deductions'] > 50000) ? 'Old Regime' : 'New Regime');
$pdf->Ln(5);

// Income Details
$pdf->SectionTitle('Income Details');
$pdf->DataRow('Basic Salary', 'Rs.' . number_format($tax_record['basic_salary'], 2));
$pdf->DataRow('HRA', 'Rs.' . number_format($tax_record['hra'], 2));
$pdf->DataRow('Special Allowance', 'Rs.' . number_format($tax_record['special_allowance'], 2));
$pdf->DataRow('Bonus', 'Rs.' . number_format($tax_record['bonus'], 2));
$pdf->DataRow('Other Income', 'Rs.' . number_format($tax_record['other_income'], 2));
$pdf->DataRow('Total Income', 'Rs.' . number_format($tax_record['total_income'], 2), true);
$pdf->Ln(5);

// Deductions
$pdf->SectionTitle('Deductions');
$pdf->DataRow('Standard Deduction', 'Rs.' . number_format($tax_record['standard_deduction'], 2));
$pdf->DataRow('Section 80C', 'Rs.' . number_format($tax_record['section_80c'], 2));
$pdf->DataRow('Section 80D', 'Rs.' . number_format($tax_record['section_80d'], 2));
$pdf->DataRow('Section 80G', 'Rs.' . number_format($tax_record['section_80g'], 2));
$pdf->DataRow('Other Deductions', 'Rs.' . number_format($tax_record['other_deductions'], 2));
$pdf->DataRow('Total Deductions', 'Rs.' . number_format($tax_record['total_deductions'], 2), true);
$pdf->Ln(5);

// Tax Computation
$pdf->SectionTitle('Tax Computation');
$pdf->DataRow('Taxable Income', 'Rs.' . number_format($tax_record['taxable_income'], 2));
$pdf->DataRow('Tax Amount', 'Rs.' . number_format($tax_record['tax_amount'], 2));
$pdf->DataRow('Health & Education Cess (4%)', 'Rs.' . number_format($tax_record['cess_amount'], 2));
$pdf->DataRow('Total Tax Payable', 'Rs.' . number_format($tax_record['total_tax_payable'], 2), true);
$pdf->Ln(5);

// Notes
$pdf->SectionTitle('Notes');
$pdf->SetFont('Arial', '', 10);
$pdf->MultiCell(0, 6, 'This is a computer-generated report and does not require a signature. Please consult with a tax professional for advice specific to your situation. Tax calculations are based on the information provided and current tax laws as applicable.');

// Output PDF
$pdf->Output('Tax_Report_' . $record_id . '.pdf', 'I');
exit;
?>