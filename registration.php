<?php
require_once 'header.php';

// Check if user is already logged in
if (isLoggedIn()) {
    header("Location: dashboard.php");
    exit();
}

$errors = [];
$success = false;

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate username
    if (empty($_POST['username'])) {
        $errors['username'] = "Username is required";
    } elseif (strlen($_POST['username']) < 3) {
        $errors['username'] = "Username must be at least 3 characters";
    }

    // Validate email
    if (empty($_POST['email'])) {
        $errors['email'] = "Email is required";
    } elseif (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Please enter a valid email";
    }

    // Validate full name
    if (empty($_POST['full_name'])) {
        $errors['full_name'] = "Full name is required";
    }

    // Validate password
    if (empty($_POST['password'])) {
        $errors['password'] = "Password is required";
    } elseif (strlen($_POST['password']) < 8) {
        $errors['password'] = "Password must be at least 8 characters";
    }

    // Validate confirm password
    if ($_POST['password'] !== $_POST['confirm_password']) {
        $errors['confirm_password'] = "Passwords do not match";
    }

    // If no errors, create user
    if (empty($errors)) {
        // Check if username or email already exists
        $check_stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $check_stmt->bind_param("ss", $_POST['username'], $_POST['email']);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        
        if ($result->num_rows > 0) {
            $errors['exists'] = "Username or email already exists";
        } else {
            // Hash password
            $hashed_password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            
            // Insert user into database
            $insert_stmt = $conn->prepare("INSERT INTO users (username, email, password, full_name) VALUES (?, ?, ?, ?)");
            $insert_stmt->bind_param("ssss", $_POST['username'], $_POST['email'], $hashed_password, $_POST['full_name']);
            
            if ($insert_stmt->execute()) {
                $success = true;
            } else {
                $errors['db'] = "Database error: " . $conn->error;
            }
        }
    }
}
?>

<div class="container content py-5">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="auth-form bg-white">
                <h2 class="text-center mb-4">Create Account</h2>
                
                <?php if ($success): ?>
    <div class="alert alert-success">
        Registration successful! Redirecting to <a href="login.php">Login</a>...
    </div>
    <?php
    header("refresh:3;url=login.php"); // ✅ Redirects to login.php after 3 seconds
    exit();
    ?>
<?php else: ?>
    <?php if (isset($errors['exists']) || isset($errors['db'])): ?>
        <div class="alert alert-danger">
            <?php echo isset($errors['exists']) ? $errors['exists'] : $errors['db']; ?>
        </div>
    <?php endif; ?>
<?php endif; ?>

                    
                    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control <?php echo isset($errors['username']) ? 'is-invalid' : ''; ?>" 
                                id="username" name="username" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                            <?php if (isset($errors['username'])): ?>
                                <div class="invalid-feedback"><?php echo $errors['username']; ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email address</label>
                            <input type="email" class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" 
                                id="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                            <?php if (isset($errors['email'])): ?>
                                <div class="invalid-feedback"><?php echo $errors['email']; ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mb-3">
                            <label for="full_name" class="form-label">Full Name</label>
                            <input type="text" class="form-control <?php echo isset($errors['full_name']) ? 'is-invalid' : ''; ?>" 
                                id="full_name" name="full_name" value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>">
                            <?php if (isset($errors['full_name'])): ?>
                                <div class="invalid-feedback"><?php echo $errors['full_name']; ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control <?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>" 
                                id="password" name="password">
                            <?php if (isset($errors['password'])): ?>
                                <div class="invalid-feedback"><?php echo $errors['password']; ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control <?php echo isset($errors['confirm_password']) ? 'is-invalid' : ''; ?>" 
                                id="confirm_password" name="confirm_password">
                            <?php if (isset($errors['confirm_password'])): ?>
                                <div class="invalid-feedback"><?php echo $errors['confirm_password']; ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100">Register</button>
                    </form>
                    
                    <div class="text-center mt-3">
                        Already have an account? <a href="login.php">Login</a>
                    </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once 'footer.php';
?>
