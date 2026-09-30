<?php
// admin/prompts.php
// Prompt logs audit and generation analytics panel

$title = "Prompt logs Audit";
$description = "Review generation statistics, query history logs, and style/tone distributions.";
require_once __DIR__ . '/../includes/header.php';

// Enforce Admin Access
require_admin();

// Handle Clear logs Action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'clear_logs') {
    $stmt = $conn->prepare("TRUNCATE TABLE prompt_logs");
    $stmt->execute();
    $stmt->close();
    flash('admin_success', 'Audit logs have been truncated successfully.', 'success');
    redirect('prompts.php');
}

// Fetch Aggregate stats
// 1. Most popular style
$stmt = $conn->prepare("SELECT style, COUNT(*) as cnt FROM prompt_logs GROUP BY style ORDER BY cnt DESC LIMIT 1");
$stmt->execute();
$pop_style_row = $stmt->get_result()->fetch_assoc();
$pop_style = $pop_style_row ? ucfirst($pop_style_row['style']) . ' (' . $pop_style_row['cnt'] . ')' : 'N/A';
$stmt->close();

// 2. Most popular tone
$stmt = $conn->prepare("SELECT tone, COUNT(*) as cnt FROM prompt_logs GROUP BY tone ORDER BY cnt DESC LIMIT 1");
$stmt->execute();
$pop_tone_row = $stmt->get_result()->fetch_assoc();
$pop_tone = $pop_tone_row ? ucfirst($pop_tone_row['tone']) . ' (' . $pop_tone_row['cnt'] . ')' : 'N/A';
$stmt->close();

// Fetch all logs
$stmt = $conn->prepare("SELECT l.*, u.name as user_name FROM prompt_logs l 
                        LEFT JOIN users u ON l.user_id = u.id 
                        ORDER BY l.created_at DESC");
$stmt->execute();
$logs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<div class="row align-items-center mb-4">
    <div class="col">
        <h2 class="font-outfit fw-bold text-light mb-1">
            <i class="fa-solid fa-clock-rotate-left text-purple-light me-2 small"></i>Prompt Logs & Analytics
        </h2>
        <p class="text-secondary mb-0">Total generated queries recorded: <?php echo count($logs); ?></p>
    </div>
    <div class="col-auto d-flex gap-2">
        <?php if (!empty($logs)): ?>
            <form action="prompts.php" method="POST" onsubmit="return confirm('Security warning: Are you sure you want to delete all historical logs? This cannot be undone.');">
                <input type="hidden" name="action" value="clear_logs">
                <button type="submit" class="btn btn-outline-danger btn-sm font-outfit">
                    <i class="fa-regular fa-trash-can me-1"></i>Clear Logs
                </button>
            </form>
        <?php endif; ?>
        <a href="dashboard.php" class="btn btn-outline-light-silver btn-sm font-outfit">
            <i class="fa-solid fa-arrow-left me-1"></i>Back to Admin
        </a>
    </div>
</div>

<?php flash('admin_success'); ?>

<!-- Analytics Summary Row -->
<div class="row g-3 mb-4">
    <div class="col-sm-6">
        <div class="card glass-card border-0 p-3 shadow-sm text-center">
            <span class="text-secondary small d-block font-outfit mb-1">Most Selected Style</span>
            <span class="fs-4 text-cyan font-outfit fw-bold"><?php echo $pop_style; ?></span>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="card glass-card border-0 p-3 shadow-sm text-center">
            <span class="text-secondary small d-block font-outfit mb-1">Most Selected Tone</span>
            <span class="fs-4 text-violet font-outfit fw-bold"><?php echo $pop_tone; ?></span>
        </div>
    </div>
</div>

<!-- Logs Details Card -->
<div class="card glass-card border-0 shadow-sm">
    <div class="card-body p-4 p-md-5">
        <?php if (empty($logs)): ?>
            <p class="text-secondary text-center py-4 mb-0">No prompt logs found.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-dark table-hover table-striped align-middle custom-table">
                    <thead>
                        <tr>
                            <th class="font-outfit small text-secondary">User</th>
                            <th class="font-outfit small text-secondary">IP Address</th>
                            <th class="font-outfit small text-secondary">Prompt</th>
                            <th class="font-outfit small text-secondary">Style</th>
                            <th class="font-outfit small text-secondary">Tone</th>
                            <th class="font-outfit small text-secondary">Length</th>
                            <th class="font-outfit small text-secondary">Generated Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td>
                                    <span class="text-light-silver small">
                                        <?php if ($log['user_id']): ?>
                                            <i class="fa-regular fa-user text-purple-light me-1 small"></i><?php echo htmlspecialchars($log['user_name']); ?>
                                        <?php else: ?>
                                            <i class="fa-solid fa-user-slash text-secondary me-1 small"></i>Guest
                                        <?php endif; ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="text-secondary small"><?php echo htmlspecialchars($log['ip_address'] ?? '127.0.0.1'); ?></span>
                                </td>
                                <td>
                                    <span class="text-light-silver small" title="<?php echo htmlspecialchars($log['prompt']); ?>">
                                        "<?php echo htmlspecialchars(strlen($log['prompt']) > 35 ? substr($log['prompt'], 0, 32) . '...' : $log['prompt']); ?>"
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-dark-deep border border-dark-subtle text-secondary-light font-outfit rounded-pill">
                                        <?php echo ucfirst($log['style']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="text-secondary small"><?php echo ucfirst($log['tone']); ?></span>
                                </td>
                                <td>
                                    <span class="text-secondary small"><?php echo ucfirst($log['length_type']); ?></span>
                                </td>
                                <td class="text-secondary small"><?php echo date('Y-m-d H:i', strtotime($log['created_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
