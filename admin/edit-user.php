<?php
require_once '../includes/auth.php';
require_once '../includes/messages.php';
require_once '../actions/users/user.php';

check_session_timeout();
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'update':
            if (!isset($_POST['user_id'])) {
                set_error("Missing user ID.");
                break;
            }

            $user_id = (int) $_POST['user_id'];
            $data = [];

            if (!empty($_POST['full_name']))
                $data['full_name'] = $_POST['full_name'];
            if (!empty($_POST['email']))
                $data['email'] = $_POST['email'];
            if (!empty($_POST['username']))
                $data['username'] = $_POST['username'];
            if (!empty($_POST['role']))
                $data['role'] = $_POST['role'];

            $success = update_user($user_id, $data);

            if ($success) {
                set_success("User updated successfully.");
            } else {
                set_error("Failed to update user.");
            }

            break;

        case "delete":
            if (!isset($_POST['user_id'])) {
                set_error("Missing user ID.");
                break;
            }

            $user_id = (int) $_POST['user_id'];

            if ($user_id === $_SESSION['user_id']) {
                set_error("You cannot delete your own account.");
                break;
            }

            $success = delete_user($user_id);

            if ($success) {
                set_success("User deleted successfully.");
                header("Location: manage-users.php");
                exit();
            } else {
                set_error("Failed to delete user.");
            }

            break;
    }
}

if (!isset($_GET['id'])) {
    set_error("User ID not provided.");
    header("Location: manage-users.php");
    exit();
}

$user_id = (int) $_GET['id'];
$target_user = get_user_by_id($user_id);

if (!$target_user) {
    set_error("User not found.");
    header("Location: manage-users.php");
    exit();
}
?>

<?php include '../includes/header-start.php'; ?>
<link rel="stylesheet" href="<?php echo BASE_URL; ?>styles/admin/user.css">
<?php include '../includes/header-end.php'; ?>

<div class="container">
    <h2 class="section-title">Edit User</h2>

    <div class="user-card card">
        <div class="card-header">
            <h4><?php echo htmlspecialchars($target_user['full_name']); ?></h4>
        </div>
        <div class="card-content">
            <form method="POST" class="update-form">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="user_id" value="<?php echo $target_user['user_id']; ?>">
                <div class="form-group">
                    <label for="full_name">Full Name</label>
                    <input type="text" name="full_name" id="full_name"
                        value="<?php echo htmlspecialchars($target_user['full_name']); ?>">
                </div>
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" name="username" id="username"
                        value="<?php echo htmlspecialchars($target_user['username']); ?>">
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email"
                        value="<?php echo htmlspecialchars($target_user['email']); ?>">
                </div>
                <div class="form-group">
                    <label for="role">Role</label>
                    <select name="role" id="role">
                        <option value="admin" <?php if ($target_user['role'] === 'admin')
                            echo 'selected'; ?>>Admin
                        </option>
                        <option value="passenger" <?php if ($target_user['role'] === 'passenger')
                            echo 'selected'; ?>>
                            Passenger</option>
                    </select>
                </div>
                <button type="submit" class="action-btn update-btn" title="Update User" <img
                    src="../assets/icons/edit.svg" alt="Update">
                    <span>Update</span>
                </button>
            </form>

            <form method="POST" class="delete-form"
                onsubmit="return confirm('Are you sure you want to delete this user?');">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="user_id" value="<?php echo $target_user['user_id']; ?>">
                <button type="submit" class="action-btn delete-btn" title="Delete User" <img
                    src="../assets/icons/delete.svg" alt="Delete">
                    <span>Delete</span>
                </button>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>