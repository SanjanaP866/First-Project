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

<style>
.center-form {
    max-width: 400px;
    margin: 50px auto;
    padding: 20px;
    border: 1px solid #ddd;
    border-radius: 10px;
    background-color: #f9f9f9;
}

.center-form input, .center-form button {
    width: 100%;
    padding: 8px;
    margin-top: 10px;
}
</style>

<div class="center-form">
    <h2 style="text-align:center;">Update Profile</h2>

    <?php if ($success_message): ?>
        <p style="color: green; text-align:center;"><?php echo $success_message; ?></p>
    <?php elseif ($error_message): ?>
        <p style="color: red; text-align:center;"><?php echo $error_message; ?></p>
    <?php endif; ?>

    <form method="post" action="">
        <label>Name:</label>
        <input type="text" name="name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>

        <label>Email:</label>
        <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>

        <label>Current Password:</label>
        <input type="password" name="current_password">

        <label>New Password:</label>
        <input type="password" name="new_password">

        <label>Confirm New Password:</label>
        <input type="password" name="confirm_password">

        <button type="submit">Update</button>
    </form>
</div>
