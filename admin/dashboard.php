<?php
// admin/dashboard.php
// Administrator overview panel

$title = "Admin Dashboard";
$description = "System administration panel for monitoring prompt volume, user growth, and content logs.";
require_once __DIR__ . '/../includes/header.php';

// Enforce Admin access
require_admin();

// Fetch System Stats
// 1. Total users
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM users");
$stmt->execute();
$total_users = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

// 2. Total saved poems
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM poems");
$stmt->execute();
$total_poems = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

// 3. Total generation requests
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM prompt_logs");
$stmt->execute();
$total_generations = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

// 4. Current Mode
$gen_mode = get_setting($conn, 'generation_mode', 'rule_based');

// Fetch recent prompt logs (last 5 entries)
$stmt = $conn->prepare("SELECT l.*, u.name as user_name FROM prompt_logs l 
                        LEFT JOIN users u ON l.user_id = u.id 
                        ORDER BY l.created_at DESC LIMIT 5");
$stmt->execute();
$recent_logs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<div class="row align-items-center mb-4">
    <div class="col">
        <h2 class="font-outfit fw-bold text-light mb-1">
            <i class="fa-solid fa-unlock-keyhole text-purple-light me-2 small"></i>Admin Dashboard
        </h2>
        <p class="text-secondary mb-0">Platform monitoring and content management console.</p>
    </div>
    <div class="col-auto">
        <span class="badge bg-dark-deep border <?php echo ($gen_mode === 'api_based') ? 'border-cyan text-cyan' : 'border-secondary text-secondary-light'; ?> px-3 py-2 rounded-pill font-outfit">
            Mode: <?php echo ($gen_mode === 'api_based') ? 'Gemini API' : 'Rule-Based Engine'; ?>
        </span>
    </div>
</div>

<!-- Stats widgets grid -->
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card glass-card border-0 p-4 shadow-sm hover-lift text-center">
            <span class="text-secondary small font-outfit d-block mb-1">Registered Users</span>
            <span class="fs-1 text-cyan font-outfit fw-bold"><?php echo $total_users; ?></span>
            <a href="users.php" class="text-cyan small text-decoration-none font-outfit d-block mt-2">Manage Users <i class="fa-solid fa-arrow-right ms-1"></i></a>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card glass-card border-0 p-4 shadow-sm hover-lift text-center">
            <span class="text-secondary small font-outfit d-block mb-1">Saved Poems</span>
            <span class="fs-1 text-violet font-outfit fw-bold"><?php echo $total_poems; ?></span>
            <a href="poems.php" class="text-violet small text-decoration-none font-outfit d-block mt-2">Browse Catalog <i class="fa-solid fa-arrow-right ms-1"></i></a>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card glass-card border-0 p-4 shadow-sm hover-lift text-center">
            <span class="text-secondary small font-outfit d-block mb-1">Prompt Requests</span>
            <span class="fs-1 text-purple-light font-outfit fw-bold"><?php echo $total_generations; ?></span>
            <a href="prompts.php" class="text-purple-light small text-decoration-none font-outfit d-block mt-2">View Audit Logs <i class="fa-solid fa-arrow-right ms-1"></i></a>
        </div>
    </div>
</div>

<!-- Admin controls split -->
<div class="row g-4">
    <!-- Recent Activity Audit table -->
    <div class="col-lg-8">
        <div class="card glass-card border-0 shadow-sm h-100">
            <div class="card-body p-4">
                <h4 class="font-outfit fw-bold text-light mb-3">
                    <i class="fa-solid fa-clock-rotate-left text-cyan me-2 small"></i>Recent Activity Logs
                </h4>
                
                <?php if (empty($recent_logs)): ?>
                    <p class="text-secondary py-3 mb-0">No prompt logs recorded yet.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-dark table-striped align-middle custom-table">
                            <thead>
                                <tr>
                                    <th class="font-outfit small text-secondary">User</th>
                                    <th class="font-outfit small text-secondary">Prompt</th>
                                    <th class="font-outfit small text-secondary">Style</th>
                                    <th class="font-outfit small text-secondary">Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_logs as $log): ?>
                                    <tr>
                                        <td>
                                            <span class="text-light-silver small">
                                                <?php echo htmlspecialchars($log['user_name'] ?? 'Guest (' . ($log['ip_address'] ?? 'unknown') . ')'); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="text-secondary small" title="<?php echo htmlspecialchars($log['prompt']); ?>">
                                                "<?php echo htmlspecialchars(strlen($log['prompt']) > 32 ? substr($log['prompt'], 0, 29) . '...' : $log['prompt']); ?>"
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-dark-deep text-secondary-light border border-dark-subtle font-outfit rounded-pill">
                                                <?php echo ucfirst($log['style'] ?? 'romantic'); ?>
                                            </span>
                                        </td>
                                        <td class="text-secondary small"><?php echo relative_time($log['created_at']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-end mt-2">
                        <a href="prompts.php" class="text-cyan text-decoration-none small font-outfit">
                            Detailed Analytics <i class="fa-solid fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Quick settings panel -->
    <div class="col-lg-4">
        <div class="card glass-card border-0 shadow-sm h-100">
            <div class="card-body p-4">
                <h4 class="font-outfit fw-bold text-light mb-3">
                    <i class="fa-solid fa-gears text-violet me-2 small"></i>Quick Settings
                </h4>
                
                <p class="text-secondary small mb-4">Adjust engine configurations and review credentials settings.</p>
                
                <ul class="list-unstyled mb-4">
                    <li class="mb-3 d-flex justify-content-between align-items-center">
                        <span class="text-secondary small">Generation Mode:</span>
                        <span class="badge bg-purple font-outfit"><?php echo $gen_mode === 'api_based' ? 'Gemini API' : 'Rule-Based'; ?></span>
                    </li>
                    <li class="mb-3 d-flex justify-content-between align-items-center">
                        <span class="text-secondary small">Gemini Key:</span>
                        <span class="text-light-silver small">
                            <?php 
                            $key = get_setting($conn, 'gemini_api_key', '');
                            echo empty($key) ? '<span class="text-danger">Not Configured</span>' : '<span class="text-success">Active (***)</span>';
                            ?>
                        </span>
                    </li>
                    <li class="d-flex justify-content-between align-items-center">
                        <span class="text-secondary small">Featured Categories:</span>
                        <span class="text-light-silver small text-truncate max-width-150" title="<?php echo htmlspecialchars(get_setting($conn, 'featured_themes', '')); ?>">
                            <?php echo htmlspecialchars(get_setting($conn, 'featured_themes', '')); ?>
                        </span>
                    </li>
                </ul>
                
                <a href="settings.php" class="btn btn-purple btn-sm w-100 font-outfit py-2">
                    <i class="fa-solid fa-sliders me-1"></i>System Settings
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
