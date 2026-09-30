<?php
// dashboard.php
// User profile, statistics, and preference center

$title = "Dashboard";
$description = "Access user settings, statistics, and favorite styles in your personal poetry hub.";
require_once 'includes/header.php';
require_once 'includes/auth.php';

// Enforce login
require_login();

$user_id = $_SESSION['user_id'];

// 1. Fetch User Profile Info
$stmt = $conn->prepare("SELECT name, email, created_at FROM users WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_info = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Null safety defaults
if (!$user_info) {
    $user_info = ['name' => $_SESSION['user_name'] ?? 'User', 'email' => '', 'created_at' => date('Y-m-d H:i:s')];
}

// 2. Fetch User Preferences
$favorite_theme = 'nature';
$preferred_style = 'romantic';
$preferred_tone = 'calm';
$preferred_length = 'medium';

$stmt = $conn->prepare("SELECT * FROM user_preferences WHERE user_id = ? LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();
if ($row = $res->fetch_assoc()) {
    $favorite_theme = $row['favorite_theme'] ?? 'nature';
    $preferred_style = $row['preferred_style'] ?? 'romantic';
    $preferred_tone = $row['preferred_tone'] ?? 'calm';
    $preferred_length = $row['preferred_length'] ?? 'medium';
}
$stmt->close();

// 3. Aggregate statistics
// Total saved
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM poems WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$total_saved = $row ? $row['total'] : 0;
$stmt->close();

// Total generated (logs)
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM prompt_logs WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$total_generated = $row ? $row['total'] : 0;
$stmt->close();

// Favorite Style
$favorite_style = 'N/A';
$stmt = $conn->prepare("SELECT style, COUNT(*) as cnt FROM poems WHERE user_id = ? GROUP BY style ORDER BY cnt DESC LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$fav_style_res = $stmt->get_result()->fetch_assoc();
if ($fav_style_res && !empty($fav_style_res['style'])) {
    $favorite_style = ucfirst($fav_style_res['style']);
}
$stmt->close();

// Favorite Tone
$favorite_tone = 'N/A';
$stmt = $conn->prepare("SELECT tone, COUNT(*) as cnt FROM poems WHERE user_id = ? GROUP BY tone ORDER BY cnt DESC LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$fav_tone_res = $stmt->get_result()->fetch_assoc();
if ($fav_tone_res && !empty($fav_tone_res['tone'])) {
    $favorite_tone = ucfirst($fav_tone_res['tone']);
}
$stmt->close();

// 4. Fetch 5 Recent Poems
$stmt = $conn->prepare("SELECT id, title, prompt, created_at, is_shared FROM poems WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$recent_poems = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<div class="row g-4 my-3">
    <!-- User summary & stats (Left Column) -->
    <div class="col-lg-4">
        <!-- Profile Card -->
        <div class="card glass-card border-0 shadow-sm mb-4">
            <div class="card-body p-4 text-center">
                <div class="avatar-container mb-3 d-inline-block position-relative">
                    <div class="avatar-placeholder rounded-circle bg-purple-light text-purple fs-2 font-outfit d-flex align-items-center justify-content-center" style="width: 70px; height: 70px; margin:0 auto;">
                        <?php echo strtoupper(substr($user_info['name'], 0, 1)); ?>
                    </div>
                </div>
                <h4 class="font-outfit text-light fw-bold mb-1"><?php echo htmlspecialchars($user_info['name']); ?></h4>
                <p class="text-secondary small mb-3"><?php echo htmlspecialchars($user_info['email']); ?></p>
                <div class="badge bg-dark-deep border border-dark-subtle text-secondary-light font-outfit px-3 py-2 rounded-pill">
                    Member since <?php echo date('M Y', strtotime($user_info['created_at'])); ?>
                </div>
            </div>
        </div>
        
        <!-- Stats Widgets -->
        <div class="row g-3">
            <div class="col-6">
                <div class="card glass-card border-0 p-3 shadow-sm text-center">
                    <span class="text-secondary small d-block font-outfit mb-1">Total Saved</span>
                    <span class="fs-2 text-cyan font-outfit fw-bold"><?php echo $total_saved; ?></span>
                </div>
            </div>
            <div class="col-6">
                <div class="card glass-card border-0 p-3 shadow-sm text-center">
                    <span class="text-secondary small d-block font-outfit mb-1">Generated</span>
                    <span class="fs-2 text-violet font-outfit fw-bold"><?php echo $total_generated; ?></span>
                </div>
            </div>
            <div class="col-6">
                <div class="card glass-card border-0 p-3 shadow-sm text-center">
                    <span class="text-secondary small d-block font-outfit mb-1">Fav Style</span>
                    <span class="fs-5 text-light-silver font-outfit fw-semibold text-truncate d-block mt-1"><?php echo $favorite_style; ?></span>
                </div>
            </div>
            <div class="col-6">
                <div class="card glass-card border-0 p-3 shadow-sm text-center">
                    <span class="text-secondary small d-block font-outfit mb-1">Fav Tone</span>
                    <span class="fs-5 text-light-silver font-outfit fw-semibold text-truncate d-block mt-1"><?php echo $favorite_tone; ?></span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Preferences & Activity (Right Column) -->
    <div class="col-lg-8">
        
        <!-- Preferences manager -->
        <div class="card glass-card border-0 shadow-sm mb-4">
            <div class="card-body p-4 p-md-5">
                <h3 class="font-outfit fw-bold text-light mb-4">
                    <i class="fa-solid fa-sliders text-cyan me-2 small"></i>Generation Preferences
                </h3>
                <div id="prefAlertContainer"></div>
                
                <form id="preferencesForm">
                    <div class="row g-3">
                        <!-- Theme -->
                        <div class="col-md-6">
                            <label for="favorite_theme" class="form-label text-secondary small font-outfit">Favorite Theme / Keyword</label>
                            <input type="text" class="form-control bg-dark border-dark-subtle text-light" id="favorite_theme" name="favorite_theme" value="<?php echo htmlspecialchars($favorite_theme); ?>" placeholder="e.g. rain, love, starlight">
                        </div>
                        
                        <!-- Style -->
                        <div class="col-md-6">
                            <label for="preferred_style" class="form-label text-secondary small font-outfit">Preferred Style</label>
                            <select class="form-select bg-dark border-dark-subtle text-light" id="preferred_style" name="preferred_style">
                                <option value="romantic" <?php echo ($preferred_style === 'romantic') ? 'selected' : ''; ?>>Romantic</option>
                                <option value="sad" <?php echo ($preferred_style === 'sad') ? 'selected' : ''; ?>>Sad / Melancholic</option>
                                <option value="nature" <?php echo ($preferred_style === 'nature') ? 'selected' : ''; ?>>Nature</option>
                                <option value="inspirational" <?php echo ($preferred_style === 'inspirational') ? 'selected' : ''; ?>>Inspirational</option>
                                <option value="modern" <?php echo ($preferred_style === 'modern') ? 'selected' : ''; ?>>Modern</option>
                                <option value="classic" <?php echo ($preferred_style === 'classic') ? 'selected' : ''; ?>>Classic</option>
                            </select>
                        </div>
                        
                        <!-- Tone -->
                        <div class="col-md-6">
                            <label for="preferred_tone" class="form-label text-secondary small font-outfit">Preferred Tone</label>
                            <select class="form-select bg-dark border-dark-subtle text-light" id="preferred_tone" name="preferred_tone">
                                <option value="calm" <?php echo ($preferred_tone === 'calm') ? 'selected' : ''; ?>>Calm</option>
                                <option value="happy" <?php echo ($preferred_tone === 'happy') ? 'selected' : ''; ?>>Happy</option>
                                <option value="dramatic" <?php echo ($preferred_tone === 'dramatic') ? 'selected' : ''; ?>>Dramatic</option>
                                <option value="melancholic" <?php echo ($preferred_tone === 'melancholic') ? 'selected' : ''; ?>>Melancholic</option>
                                <option value="motivational" <?php echo ($preferred_tone === 'motivational') ? 'selected' : ''; ?>>Motivational</option>
                            </select>
                        </div>
                        
                        <!-- Length -->
                        <div class="col-md-6">
                            <label for="preferred_length" class="form-label text-secondary small font-outfit">Preferred Length</label>
                            <select class="form-select bg-dark border-dark-subtle text-light" id="preferred_length" name="preferred_length">
                                <option value="short" <?php echo ($preferred_length === 'short') ? 'selected' : ''; ?>>Short</option>
                                <option value="medium" <?php echo ($preferred_length === 'medium') ? 'selected' : ''; ?>>Medium</option>
                                <option value="long" <?php echo ($preferred_length === 'long') ? 'selected' : ''; ?>>Long</option>
                            </select>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-purple btn-sm mt-4 px-4 py-2 font-outfit">
                        Save Preferences
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Recent saved poems -->
        <div class="card glass-card border-0 shadow-sm">
            <div class="card-body p-4 p-md-5">
                <h3 class="font-outfit fw-bold text-light mb-4">
                    <i class="fa-solid fa-scroll text-violet me-2 small"></i>Recent Creations
                </h3>
                
                <?php if (empty($recent_poems)): ?>
                    <div class="py-4 text-center">
                        <p class="text-secondary mb-3">You haven't saved any poems yet.</p>
                        <a href="generate.php" class="btn btn-outline-cyan btn-sm font-outfit px-3">
                            Start Writing
                        </a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-dark table-hover table-striped align-middle custom-table">
                            <thead>
                                <tr>
                                    <th class="font-outfit small text-secondary">Title</th>
                                    <th class="font-outfit small text-secondary">Prompt / Topic</th>
                                    <th class="font-outfit small text-secondary">Visibility</th>
                                    <th class="font-outfit small text-secondary">Saved</th>
                                    <th class="font-outfit small text-secondary text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_poems as $poem): ?>
                                    <tr>
                                        <td>
                                            <a href="poem_view.php?id=<?php echo $poem['id']; ?>" class="text-light-silver hover-cyan font-playfair italic fw-bold text-decoration-none">
                                                <?php echo htmlspecialchars($poem['title']); ?>
                                            </a>
                                        </td>
                                        <td>
                                            <span class="text-secondary small">
                                                "<?php echo htmlspecialchars(strlen($poem['prompt']) > 30 ? substr($poem['prompt'], 0, 27) . '...' : $poem['prompt']); ?>"
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($poem['is_shared']): ?>
                                                <span class="text-cyan small"><i class="fa-solid fa-globe me-1"></i>Public</span>
                                            <?php else: ?>
                                                <span class="text-secondary small"><i class="fa-solid fa-lock me-1"></i>Private</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="small text-secondary"><?php echo relative_time($poem['created_at']); ?></td>
                                        <td class="text-end">
                                            <a href="poem_view.php?id=<?php echo $poem['id']; ?>" class="btn btn-outline-cyan btn-xs" title="Open Viewer">
                                                <i class="fa-regular fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-end mt-2">
                        <a href="my_poems.php" class="text-cyan text-decoration-none small font-outfit">
                            View All Collection <i class="fa-solid fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
