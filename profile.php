<?php
require_once 'header.php';

requireLogin();

$user_id = $_SESSION['user_id'];
$success_message = '';
$error_message = '';

// Get user details
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['name']); 
    $email = trim($_POST['email']);
    $current_password = trim($_POST['current_password']);
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);
    
    $errors = [];

    if (empty($full_name)) {
        $errors[] = "Name is required";
    }

    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }

    // Check if email already exists for another user
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $stmt->bind_param("si", $email, $user_id);
    $stmt->execute();
    $email_check = $stmt->get_result();

    if ($email_check->num_rows > 0) {
        $errors[] = "Email already in use by another account";
    }

    $password_changed = false;

    if (!empty($new_password)) {
        if (empty($current_password)) {
            $errors[] = "Current password is required to change password";
        } else {
            $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $user_data = $result->fetch_assoc();

            if (!password_verify($current_password, $user_data['password'])) {
                $errors[] = "Current password is incorrect";
            } elseif (strlen($new_password) < 8) {
                $errors[] = "New password must be at least 8 characters";
            } elseif ($new_password !== $confirm_password) {
                $errors[] = "New password and confirm password do not match";
            } else {
                $new_password_hashed = password_hash($new_password, PASSWORD_DEFAULT);
                $password_changed = true;
            }
        }
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ?" . ($password_changed ? ", password = ?" : "") . " WHERE id = ?");
        if ($password_changed) {
            $stmt->bind_param("sssi", $full_name, $email, $new_password_hashed, $user_id);
        } else {
            $stmt->bind_param("ssi", $full_name, $email, $user_id);
        }
        $stmt->execute();

        $success_message = "Profile updated successfully";
    } else {
        $error_message = implode("<br>", $errors);
    }
}
?>

<div class="container py-5">
    <div class="center-form">
        <h2 class="text-center mb-4">Update Profile</h2>

        <?php if ($success_message): ?>
            <div class="alert alert-success text-center"><?php echo $success_message; ?></div>
        <?php elseif ($error_message): ?>
            <div class="alert alert-danger text-center"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <form method="post" action="">
            <div class="mb-3">
                <label class="form-label">Name:</label>
                <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Email:</label>
                <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Current Password:</label>
                <input type="password" class="form-control" name="current_password">
            </div>

            <div class="mb-3">
                <label class="form-label">New Password:</label>
                <input type="password" class="form-control" name="new_password">
            </div>

            <div class="mb-3">
                <label class="form-label">Confirm New Password:</label>
                <input type="password" class="form-control" name="confirm_password">
            </div>

            <button type="submit" class="btn btn-primary w-100">Update Profile</button>
        </form>
    </div>
</div>

<?php require_once 'footer.php'; ?>
