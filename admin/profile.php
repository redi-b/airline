<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../actions/users/user.php';
require_once '../includes/messages.php';

check_session_timeout();
require_admin();

$user_id = $_SESSION['user_id'];
$current_user = get_user_by_id($user_id);

$info_success = false;
$pass_success = false;
$info_errors = [];
$pass_errors = [];

// Update admin info
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_info'])) {
    $fields = [];

    $fields['full_name'] = trim($_POST['full_name']);
    $fields['email'] = trim($_POST['email']);
    $fields['phone'] = trim($_POST['phone']);
    $fields['address'] = trim($_POST['address']);
    $fields['username'] = trim($_POST['username']);

    if (!filter_var($fields['email'], FILTER_VALIDATE_EMAIL)) {
        $info_errors[] = "Invalid email format.";
    }

    if (empty($info_errors)) {
        if (update_user($user_id, $fields)) {
            $info_success = true;
            $current_user = get_user_by_id($user_id); // Refresh
        } else {
            $info_errors[] = "Failed to update user info.";
        }
    }
}

// Change password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $old = $_POST['old_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (empty($old) || empty($new) || empty($confirm)) {
        $pass_errors[] = "All password fields are required.";
    } elseif ($new !== $confirm) {
        $pass_errors[] = "New passwords do not match.";
    } elseif (!verify_user_password($user_id, $old)) {
        $pass_errors[] = "Old password is incorrect.";
    }

    if (empty($pass_errors)) {
        if (update_user($user_id, ['password' => $new])) {
            $pass_success = true;
        } else {
            $pass_errors[] = "Failed to update password.";
        }
    }
}

$page_title = "User Profile";
?>

<?php include '../includes/header-start.php'; ?>
<link rel="stylesheet" href="../styles/profile.css">
<?php include '../includes/header-end.php'; ?>

<section class="profile">
    <div class="container">
        <h2 class="section-title">Your Profile</h2>
        <div class="profile-grid">
            <!-- Profile Information Form -->
            <div class="card">
                <div class="card-header">
                    <h3>Profile Information</h3>
                </div>
                <div class="card-content">
                    <?php if ($info_success): ?>
                        <p class="success">Profile updated successfully.</p>
                    <?php endif; ?>
                    <?php foreach ($info_errors as $e): ?>
                        <p class="error"><?php echo htmlspecialchars($e); ?></p>
                    <?php endforeach; ?>
                    <form method="POST" class="form">
                        <input type="hidden" name="update_info" value="1">
                        <div class="form-group">
                            <label for="full_name">Full Name</label>
                            <input type="text" id="full_name" name="full_name" class="form-input"
                                value="<?php echo htmlspecialchars($current_user['full_name'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="text" id="username" name="username" class="form-input"
                                value="<?php echo htmlspecialchars($current_user['username'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" class="form-input"
                                value="<?php echo htmlspecialchars($current_user['email'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone</label>
                            <input type="text" id="phone" name="phone" class="form-input"
                                value="<?php echo htmlspecialchars($current_user['phone'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label for="address">Address</label>
                            <textarea id="address" name="address"
                                class="form-textarea"><?php echo htmlspecialchars($current_user['address'] ?? ''); ?></textarea>
                        </div>
                        <button type="submit" class="submit-button">Update Profile</button>
                    </form>
                </div>
            </div>

            <!-- Change Password Form -->
            <div class="card">
                <div class="card-header">
                    <h3>Change Password</h3>
                </div>
                <div class="card-content">
                    <?php if ($pass_success): ?>
                        <p class="success">Password updated successfully.</p>
                    <?php endif; ?>
                    <?php foreach ($pass_errors as $e): ?>
                        <p class="error"><?php echo htmlspecialchars($e); ?></p>
                    <?php endforeach; ?>
                    <form method="POST" class="form">
                        <input type="hidden" name="change_password" value="1">
                        <div class="form-group">
                            <label for="old_password">Old Password <span class="required">*</span></label>
                            <input type="password" id="old_password" name="old_password" class="form-input" required>
                        </div>
                        <div class="form-group">
                            <label for="new_password">New Password <span class="required">*</span></label>
                            <input type="password" id="new_password" name="new_password" class="form-input" required>
                        </div>
                        <div class="form-group">
                            <label for="confirm_password">Confirm Password <span class="required">*</span></label>
                            <input type="password" id="confirm_password" name="confirm_password" class="form-input"
                                required>
                        </div>
                        <button type="submit" class="submit-button">Change Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>