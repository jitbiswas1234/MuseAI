<?php
// admin/poems.php
// Poetry directory and moderation console

$title = "Moderate Poems";
$description = "Review all generated poems saved on MuseAI, filter by users, and moderate content.";
require_once __DIR__ . '/../includes/header.php';

// Enforce Admin Access
require_admin();

// Fetch all saved poems with author name
$stmt = $conn->prepare("SELECT p.*, u.name as author_name FROM poems p 
                        JOIN users u ON p.user_id = u.id 
                        ORDER BY p.created_at DESC");
$stmt->execute();
$poems = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<div class="row align-items-center mb-4">
    <div class="col">
        <h2 class="font-outfit fw-bold text-light mb-1">
            <i class="fa-solid fa-scroll text-purple-light me-2 small"></i>Poetry Catalog
        </h2>
        <p class="text-secondary mb-0">Total saved poems on platform: <?php echo count($poems); ?></p>
    </div>
    <div class="col-auto">
        <a href="dashboard.php" class="btn btn-outline-light-silver btn-sm font-outfit">
            <i class="fa-solid fa-arrow-left me-1"></i>Back to Admin
        </a>
    </div>
</div>

<div class="card glass-card border-0 shadow-sm">
    <div class="card-body p-4 p-md-5">
        
        <?php if (empty($poems)): ?>
            <p class="text-secondary text-center py-4 mb-0">No poems have been saved to the database yet.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-dark table-hover table-striped align-middle custom-table">
                    <thead>
                        <tr>
                            <th class="font-outfit small text-secondary">Title</th>
                            <th class="font-outfit small text-secondary">Author</th>
                            <th class="font-outfit small text-secondary">Prompt / Topic</th>
                            <th class="font-outfit small text-secondary">Style / Tone</th>
                            <th class="font-outfit small text-secondary">Visibility</th>
                            <th class="font-outfit small text-secondary">Created</th>
                            <th class="font-outfit small text-secondary text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($poems as $poem): ?>
                            <tr>
                                <td>
                                    <a href="../poem_view.php?id=<?php echo $poem['id']; ?>" class="text-light-silver hover-cyan font-playfair italic fw-bold text-decoration-none">
                                        <?php echo htmlspecialchars($poem['title']); ?>
                                    </a>
                                </td>
                                <td>
                                    <span class="text-light-silver small"><?php echo htmlspecialchars($poem['author_name']); ?></span>
                                </td>
                                <td>
                                    <span class="text-secondary small" title="<?php echo htmlspecialchars($poem['prompt']); ?>">
                                        "<?php echo htmlspecialchars(strlen($poem['prompt']) > 25 ? substr($poem['prompt'], 0, 22) . '...' : $poem['prompt']); ?>"
                                    </span>
                                </td>
                                <td>
                                    <span class="text-secondary small"><?php echo ucfirst($poem['style']); ?> / <?php echo ucfirst($poem['tone']); ?></span>
                                </td>
                                <td>
                                    <?php if ($poem['is_shared']): ?>
                                        <span class="text-cyan small"><i class="fa-solid fa-globe me-1"></i>Public</span>
                                    <?php else: ?>
                                        <span class="text-secondary small"><i class="fa-solid fa-lock me-1"></i>Private</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-secondary small"><?php echo date('Y-m-d H:i', strtotime($poem['created_at'])); ?></td>
                                <td class="text-end">
                                    <div class="d-flex gap-2 justify-content-end">
                                        <!-- View -->
                                        <a href="../poem_view.php?id=<?php echo $poem['id']; ?>" class="btn btn-outline-cyan btn-xs" title="View details">
                                            <i class="fa-regular fa-eye"></i>
                                        </a>
                                        <!-- Delete -->
                                        <a href="../delete_poem.php?id=<?php echo $poem['id']; ?>" class="btn btn-outline-danger btn-xs" title="Delete content" onclick="return confirm('Moderate Content: Delete poem \'<?php echo htmlspecialchars($poem['title']); ?>\'?');">
                                            <i class="fa-regular fa-trash-can"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
        
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
