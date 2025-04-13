<?php
require_once 'header.php';
?>

<section class="hero-section">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <h1>Simplify Your Tax Calculations</h1>
                <p class="lead">Tax Ease helps you calculate your tax liabilities accurately and provides personalized strategies to optimize your finances.</p>
                <?php if (!isLoggedIn()): ?>
                    <div class="d-flex justify-content-center gap-3">
                        <a href="registration.php" class="btn btn-light btn-lg">Get Started</a>
                        <a href="login.php" class="btn btn-outline-light btn-lg">Login</a>
                    </div>
                <?php else: ?>
                    <a href="dashboard.php" class="btn btn-light btn-lg">Go to Dashboard</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<section class="features-section">
    <div class="container">
        <h2 class="text-center mb-5">Why Choose Tax Ease?</h2>
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card feature-card h-100">
                    <div class="card-body text-center">
                        <div class="feature-icon">
                            <i class="fas fa-calculator"></i>
                        </div>
                        <h3>Accurate Tax Calculation</h3>
                        <p>Get precise tax calculations based on your income, deductions, and applicable tax slabs.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card feature-card h-100">
                    <div class="card-body text-center">
                        <div class="feature-icon">
                            <i class="fas fa-robot"></i>
                        </div>
                        <h3>AI-Powered Advice</h3>
                        <p>Receive personalized tax-saving strategies and financial planning recommendations.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card feature-card h-100">
                    <div class="card-body text-center">
                        <div class="feature-icon">
                            <i class="fas fa-history"></i>
                        </div>
                        <h3>Track Your History</h3>
                        <p>Access your past tax calculations and download reports for your records.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="bg-light py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                <h2>How Tax Ease Works</h2>
                <p class="lead">Our platform simplifies the tax calculation process with just a few steps:</p>
                <ol class="mt-4 text-start d-inline-block">
                    <li class="mb-3">Create an account and securely log in</li>
                    <li class="mb-3">Enter your income details and applicable deductions</li>
                    <li class="mb-3">Get instant tax calculations with visual breakdowns</li>
                    <li class="mb-3">Receive AI-generated suggestions to optimize your taxes</li>
                    <li>Save and download your tax reports for future reference</li>
                </ol>
                <div class="mt-4">
                    <a href="registration.php" class="btn btn-primary btn-lg">Try It Now</a>
                </div>
            </div>
        </div>
    </div>
</section>


<?php
require_once 'footer.php';
?>