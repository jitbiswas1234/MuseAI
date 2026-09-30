<?php
// admin/users.php
// User list and role moderation console

$title = "Manage Users";
$description = "Moderate platform registration accounts, promote roles, and audit access.";
require_once __DIR__ . '/../includes/header.php';

// Enforce Admin Access
require_admin();

$admin_user_id = $_SESSION['user_id'];

// Handle Actions (Promote/Demote/Delete)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && isset($_GET['id'])) {
    $action = sanitize($_GET['action']);
    $target_id = intval($_GET['id']);
    
    if ($target_id <= 0) {
        flash('admin_error', 'Invalid User ID.', 'danger');
    } elseif ($target_id === $admin_user_id && $action === 'delete') {
        // Prevent deleting oneself!
        flash('admin_error', 'Security constraint: You cannot delete your own account.', 'warning');
    } elseif ($target_id === 1 && $action === 'delete') {
        // Prevent deleting seed superadmin!
        flash('admin_error', 'Security constraint: Super Administrator account cannot be deleted.', 'warning');
    } else {
        if ($action === 'toggle_role') {
            // Fetch current role
            $stmt = $conn->prepare("SELECT role FROM users WHERE id = ? LIMIT 1");
            $stmt->bind_param("i", $target_id);
            $stmt->execute();
            $user_role = $stmt->get_result()->fetch_assoc()['role'] ?? '';
            $stmt->close();
            
            if ($user_role) {
                $new_role = ($user_role === 'admin') ? 'user' : 'admin';
                $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
                $stmt->bind_param("si", $new_role, $target_id);
                $stmt->execute();
                $stmt->close();
                flash('admin_success', 'User role updated successfully.', 'success');
            } else {
                flash('admin_error', 'User not found.', 'danger');
            }
        } elseif ($action === 'delete') {
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt->bind_param("i", $target_id);
            $stmt->execute();
            $stmt->close();
            flash('admin_success', 'User account and associated content deleted successfully.', 'success');
        }
        
        // Refresh page to clear params
        redirect('users.php');
    }
}

// Fetch all users sorted by joined date
$stmt = $conn->prepare("SELECT id, name, email, role, created_at FROM users ORDER BY created_at DESC");
$stmt->execute();
$users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<div class="row align-items-center mb-4">
    <div class="col">
        <h2 class="font-outfit fw-bold text-light mb-1">
            <i class="fa-solid fa-users text-purple-light me-2 small"></i>Manage Users
        </h2>
        <p class="text-secondary mb-0">Total registered members: <?php echo count($users); ?></p>
    </div>
    <div class="col-auto">
        <a href="dashboard.php" class="btn btn-outline-light-silver btn-sm font-outfit">
            <i class="fa-solid fa-arrow-left me-1"></i>Back to Admin
        </a>
    </div>
</div>

<?php flash('admin_error'); ?>
<?php flash('admin_success'); ?>

<div class="card glass-card border-0 shadow-sm">
    <div class="card-body p-4 p-md-5">
        <div class="table-responsive">
            <table class="table table-dark table-hover table-striped align-middle custom-table">
                <thead>
                    <tr>
                        <th class="font-outfit small text-secondary">Name</th>
                        <th class="font-outfit small text-secondary">Email</th>
                        <th class="font-outfit small text-secondary">Role</th>
                        <th class="font-outfit small text-secondary">Joined</th>
                        <th class="font-outfit small text-secondary text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle bg-dark-deep border border-secondary text-cyan-light font-outfit d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                        <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                                    </div>
                                    <span class="text-light-silver fw-semibold"><?php echo htmlspecialchars($user['name']); ?></span>
                                </div>
                            </td>
                            <td>
                                <span class="text-secondary small"><?php echo htmlspecialchars($user['email']); ?></span>
                            </td>
                            <td>
                                <span class="badge <?php echo ($user['role'] === 'admin') ? 'bg-purple text-light' : 'bg-dark-deep text-secondary-light border border-dark-subtle'; ?> font-outfit">
                                    <?php echo ucfirst($user['role']); ?>
                                </span>
                            </td>
                            <td class="text-secondary small"><?php echo date('Y-m-d H:i', strtotime($user['created_at'])); ?></td>
                            <td class="text-end">
                                <div class="d-flex gap-2 justify-content-end">
                                    <!-- Toggle Role -->
                                    <a href="users.php?action=toggle_role&id=<?php echo $user['id']; ?>" class="btn btn-outline-cyan btn-xs" title="Toggle admin role" onclick="return confirm('Toggle role for <?php echo htmlspecialchars($user['name']); ?>?');">
                                        <i class="fa-solid fa-arrows-spin"></i>
                                    </a>
                                    <!-- Delete -->
                                    <?php if ($user['id'] !== $admin_user_id && $user['id'] !== 1): ?>
                                        <a href="users.php?action=delete&id=<?php echo $user['id']; ?>" class="btn btn-outline-danger btn-xs" title="Delete account" onclick="return confirm('Are you sure you want to delete this user? All their saved poems will be permanently erased.');">
                                            <i class="fa-regular fa-trash-can"></i>
                                        </a>
                                    <?php else: ?>
                                        <button class="btn btn-outline-secondary btn-xs" disabled title="Locked">
                                            <i class="fa-solid fa-lock"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
