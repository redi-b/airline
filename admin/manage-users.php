<?php
require_once '../includes/auth.php';
require_once '../includes/messages.php';
require_once '../actions/users/user.php';

check_session_timeout();
require_admin();

$users = get_all_users();
?>

<?php include '../includes/header-start.php'; ?>
<link rel="stylesheet" href="<?php echo BASE_URL; ?>styles/admin/users.css">
<?php include '../includes/header-end.php'; ?>

<div class="container">
    <h2 class="section-title">Manage Users</h2>

    <?php if (empty($users)): ?>
        <p class="no-users">No users found.</p>
    <?php else: ?>
        <div class="user-grid">
            <?php foreach ($users as $user): ?>
                <div class="user-card card">
                    <div class="card-header">
                        <h4><?php echo htmlspecialchars($user['full_name']); ?></h4>
                    </div>
                    <div class="card-content">
                        <div class="user-details">
                            <div class="detail">
                                <span class="label">Username:</span>
                                <span><?php echo htmlspecialchars($user['username']); ?></span>
                            </div>
                            <div class="detail">
                                <span class="label">Email:</span>
                                <span><?php echo htmlspecialchars($user['email']); ?></span>
                            </div>
                            <div class="detail detail-badge">
                                <span class="label">Role:</span>
                                <span class="badge badge-<?php echo $user['role']; ?>">
                                    <?php echo ucfirst(htmlspecialchars($user['role'])); ?>
                                </span>
                            </div>
                        </div>
                        <div class="user-actions">
                            <a href="edit-user.php?id=<?php echo $user['user_id']; ?>" class="action-btn edit-btn"
                                title="View or Edit User">
                                <img src="../assets/icons/edit.svg" alt="Edit">
                                <span>View / Edit</span>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>