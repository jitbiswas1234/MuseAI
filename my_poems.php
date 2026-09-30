<?php
// my_poems.php
// User saved poems library

$title = "My Poetry Collection";
$description = "Browse, search, and manage your saved AI-generated poems.";
require_once 'includes/header.php';
require_once 'includes/auth.php';

// Enforce authentication
require_login();

$user_id = $_SESSION['user_id'];

// Get filter parameters
$search = sanitize($_GET['search'] ?? '');
$style = sanitize($_GET['style'] ?? '');
$tone = sanitize($_GET['tone'] ?? '');

// Build search query
$query = "SELECT * FROM poems WHERE user_id = ?";
$params = [$user_id];
$types = "i";

if (!empty($search)) {
    $query .= " AND (title LIKE ? OR prompt LIKE ? OR poem_text LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    $types .= "sss";
}

if (!empty($style)) {
    $query .= " AND style = ?";
    $params[] = $style;
    $types .= "s";
}

if (!empty($tone)) {
    $query .= " AND tone = ?";
    $params[] = $tone;
    $types .= "s";
}

$query .= " ORDER BY created_at DESC";

$stmt = $conn->prepare($query);
if (!empty($types)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$poems = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<div class="row align-items-center mb-4">
    <div class="col-md-6">
        <h2 class="font-outfit fw-bold text-light mb-1">
            <i class="fa-solid fa-book-open text-purple-light me-2 small"></i>My Collection
        </h2>
        <p class="text-secondary mb-0">You have generated and saved <?php echo count($poems); ?> poems.</p>
    </div>
    <div class="col-md-6 text-md-end mt-3 mt-md-0">
        <a href="generate.php" class="btn btn-purple btn-sm font-outfit px-3 py-2">
            <i class="fa-solid fa-wand-magic-sparkles me-2"></i>Create New Verse
        </a>
    </div>
</div>

<!-- Filters Toolbar (Glassmorphic Card) -->
<div class="card glass-card shadow-sm border-0 mb-4">
    <div class="card-body p-3">
        <form action="my_poems.php" method="GET" class="row g-2">
            <!-- Search field -->
            <div class="col-md-4">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-dark-deep border-end-0 border-dark-subtle text-secondary">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </span>
                    <input type="text" class="form-control bg-dark-deep border-start-0 border-dark-subtle text-light" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search by title, prompt...">
                </div>
            </div>
            
            <!-- Style Filter -->
            <div class="col-md-3">
                <select class="form-select form-select-sm bg-dark-deep border-dark-subtle text-light" name="style">
                    <option value="">All Styles</option>
                    <option value="romantic" <?php echo ($style === 'romantic') ? 'selected' : ''; ?>>Romantic</option>
                    <option value="sad" <?php echo ($style === 'sad') ? 'selected' : ''; ?>>Sad / Melancholic</option>
                    <option value="nature" <?php echo ($style === 'nature') ? 'selected' : ''; ?>>Nature</option>
                    <option value="inspirational" <?php echo ($style === 'inspirational') ? 'selected' : ''; ?>>Inspirational</option>
                    <option value="modern" <?php echo ($style === 'modern') ? 'selected' : ''; ?>>Modern</option>
                    <option value="classic" <?php echo ($style === 'classic') ? 'selected' : ''; ?>>Classic</option>
                </select>
            </div>
            
            <!-- Tone Filter -->
            <div class="col-md-3">
                <select class="form-select form-select-sm bg-dark-deep border-dark-subtle text-light" name="tone">
                    <option value="">All Tones</option>
                    <option value="calm" <?php echo ($tone === 'calm') ? 'selected' : ''; ?>>Calm</option>
                    <option value="happy" <?php echo ($tone === 'happy') ? 'selected' : ''; ?>>Happy</option>
                    <option value="dramatic" <?php echo ($tone === 'dramatic') ? 'selected' : ''; ?>>Dramatic</option>
                    <option value="melancholic" <?php echo ($tone === 'melancholic') ? 'selected' : ''; ?>>Melancholic</option>
                    <option value="motivational" <?php echo ($tone === 'motivational') ? 'selected' : ''; ?>>Motivational</option>
                </select>
            </div>
            
            <!-- Buttons -->
            <div class="col-md-2 d-grid gap-2 d-md-flex">
                <button type="submit" class="btn btn-cyan btn-sm flex-grow-1 font-outfit">Filter</button>
                <?php if (!empty($search) || !empty($style) || !empty($tone)): ?>
                    <a href="my_poems.php" class="btn btn-outline-secondary btn-sm" title="Clear filters">
                        <i class="fa-solid fa-arrow-rotate-left"></i>
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Poems List -->
<?php if (empty($poems)): ?>
    <div class="card glass-card text-center border-0 p-5 shadow">
        <div class="card-body">
            <div class="empty-icon mb-4 text-secondary">
                <i class="fa-solid fa-scroll fs-1 opacity-25"></i>
            </div>
            <h3 class="font-outfit fw-bold text-light mb-2">No Poems Found</h3>
            <p class="text-secondary max-width-400 mx-auto">
                <?php if (!empty($search) || !empty($style) || !empty($tone)): ?>
                    We couldn't find any matches for your current filter settings. Try clearing some filters.
                <?php else: ?>
                    Your collection is currently empty. Unleash your creativity on the workbench page.
                <?php endif; ?>
            </p>
            <?php if (empty($search) && empty($style) && empty($tone)): ?>
                <a href="generate.php" class="btn btn-purple btn-sm mt-3 font-outfit px-3">
                    Go to Workbench
                </a>
            <?php endif; ?>
        </div>
    </div>
<?php else: ?>
    <div class="row g-4">
        <?php foreach ($poems as $poem): ?>
            <?php
            // Extract a preview snippet of the poem (first 2 lines)
            $lines = explode("\n", $poem['poem_text']);
            $snippet_lines = array_slice(array_filter(array_map('trim', $lines)), 0, 2);
            $snippet = implode("<br>", $snippet_lines) . "...";
            ?>
            <div class="col-md-6 col-lg-4">
                <div class="card glass-card h-100 hover-lift border-0 shadow-sm d-flex flex-column justify-content-between">
                    <div class="card-body p-4">
                        <!-- Top tags and status -->
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="badge bg-dark-deep border border-dark-subtle text-secondary-light font-outfit rounded-pill">
                                <?php echo ucfirst($poem['style']); ?>
                            </span>
                            <span class="text-secondary small font-outfit">
                                <i class="fa-regular fa-clock me-1 text-cyan-light"></i><?php echo relative_time($poem['created_at']); ?>
                            </span>
                        </div>
                        
                        <!-- Title & prompt snippet -->
                        <h4 class="font-playfair text-light mb-2 fw-bold italic">
                            <a href="poem_view.php?id=<?php echo $poem['id']; ?>" class="text-light text-decoration-none hover-cyan">
                                <?php echo htmlspecialchars($poem['title']); ?>
                            </a>
                        </h4>
                        <p class="text-secondary small italic font-outfit mb-3">
                            Topic: "<?php echo htmlspecialchars(strlen($poem['prompt']) > 45 ? substr($poem['prompt'], 0, 42) . '...' : $poem['prompt']); ?>"
                        </p>
                        
                        <!-- Poem snippet -->
                        <div class="poem-preview font-playfair fs-6 text-light-silver lh-base border-start border-dark-subtle ps-3 mb-4">
                            <?php echo $snippet; ?>
                        </div>
                    </div>
                    
                    <!-- Footer action items -->
                    <div class="card-footer bg-dark-deep border-top border-dark-subtle p-3 d-flex align-items-center justify-content-between">
                        <div>
                            <?php if ($poem['is_shared']): ?>
                                <span class="text-cyan small font-outfit" title="Shared publicly">
                                    <i class="fa-solid fa-globe me-1"></i>Public
                                </span>
                            <?php else: ?>
                                <span class="text-secondary small font-outfit" title="Private to you">
                                    <i class="fa-solid fa-lock me-1"></i>Private
                                </span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <!-- View -->
                            <a href="poem_view.php?id=<?php echo $poem['id']; ?>" class="btn btn-outline-cyan btn-xs" title="View details">
                                <i class="fa-regular fa-eye"></i>
                            </a>
                            <!-- Delete -->
                            <a href="delete_poem.php?id=<?php echo $poem['id']; ?>" class="btn btn-outline-danger btn-xs" onclick="return confirm('Are you sure you want to delete this poem?');" title="Delete poem">
                                <i class="fa-regular fa-trash-can"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
