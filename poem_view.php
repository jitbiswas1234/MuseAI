<?php
// poem_view.php
// Detailed poem viewer and manager

require_once 'config/database.php';
require_once 'includes/functions.php';

$poem_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($poem_id <= 0) {
    flash('general_error', 'Invalid poem ID.', 'danger');
    redirect('dashboard.php');
}

// Fetch poem details and owner info
$stmt = $conn->prepare("SELECT p.*, u.name as author_name FROM poems p 
                        JOIN users u ON p.user_id = u.id 
                        WHERE p.id = ? LIMIT 1");
$stmt->bind_param("i", $poem_id);
$stmt->execute();
$res = $stmt->get_result();
$poem = $res->fetch_assoc();
$stmt->close();

if (!$poem) {
    flash('general_error', 'Poem not found.', 'danger');
    redirect('dashboard.php');
}

$is_owner = (is_logged_in() && $_SESSION['user_id'] == $poem['user_id']);
$is_admin = is_admin();

// Access check: must be owner, admin, or the poem must be shared publicly
if (!$is_owner && !$is_admin && !$poem['is_shared']) {
    flash('general_error', 'Access denied. This poem is private.', 'danger');
    redirect('index.php');
}

// Handle Title Update Form Submission
$update_success = false;
$update_error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_title') {
    if (!$is_owner) {
        $update_error = "Only the author can change the title.";
    } else {
        $new_title = sanitize($_POST['new_title'] ?? '');
        if (empty($new_title)) {
            $update_error = "Title cannot be empty.";
        } else {
            $stmt = $conn->prepare("UPDATE poems SET title = ? WHERE id = ?");
            $stmt->bind_param("si", $new_title, $poem_id);
            if ($stmt->execute()) {
                $poem['title'] = $new_title;
                $update_success = true;
            } else {
                $update_error = "Failed to update title in database.";
            }
            $stmt->close();
        }
    }
}

$title = htmlspecialchars($poem['title']);
$description = "Read '" . htmlspecialchars($poem['title']) . "' generated on MuseAI.";
require_once 'includes/header.php';
?>

<div class="row justify-content-center my-3">
    <div class="col-md-9 col-lg-8">
        
        <!-- Back Navigation -->
        <div class="mb-4">
            <a href="<?php echo $is_owner ? 'my_poems.php' : 'index.php'; ?>" class="text-secondary-light hover-cyan text-decoration-none transition">
                <i class="fa-solid fa-arrow-left me-2"></i>Back to <?php echo $is_owner ? 'My Collection' : 'Home'; ?>
            </a>
        </div>
        
        <!-- Inline Alert for updates -->
        <?php if ($update_success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                Poem title updated successfully!
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php elseif (!empty($update_error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $update_error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <!-- Main Poem Canvas -->
        <div class="card glass-card shadow-lg border-0 mb-4">
            <div class="card-body p-4 p-md-5 text-center relative">
                
                <!-- Ribbon Metadata -->
                <div class="d-flex align-items-center justify-content-between border-bottom border-dark-subtle pb-3 mb-4">
                    <span class="text-secondary small font-outfit">
                        Generated <?php echo relative_time($poem['created_at']); ?>
                    </span>
                    <span class="badge bg-dark-deep border border-dark-subtle text-secondary-light font-outfit rounded-pill px-3 py-1">
                        <?php echo ucfirst($poem['style']); ?> | <?php echo ucfirst($poem['tone']); ?>
                    </span>
                </div>
                
                <!-- Title and Author -->
                <div class="mb-4">
                    <h1 class="font-playfair text-light fw-bold italic display-5 mb-2" id="poemTitle"><?php echo htmlspecialchars($poem['title']); ?></h1>
                    <p class="text-secondary small font-outfit mb-0">
                        by <span class="text-light-silver"><?php echo htmlspecialchars($poem['author_name']); ?></span>
                        <?php if ($is_owner): ?>(You)<?php endif; ?>
                    </p>
                </div>
                
                <!-- Poem verses -->
                <div class="poem-body-container my-5">
                    <div class="poem-text font-playfair fs-3 text-light-silver lh-lg whitespace-pre-line" id="poemText"><?php echo nl2br(htmlspecialchars($poem['poem_text'])); ?></div>
                </div>
                
                <!-- Prompt reference -->
                <div class="card bg-dark-deep border-dark-subtle py-3 px-4 rounded-3 d-inline-block text-start max-width-500 mx-auto">
                    <span class="text-secondary small font-outfit d-block mb-1">Inspired by Prompt:</span>
                    <span class="text-light-silver italic font-playfair fs-6">"<?php echo htmlspecialchars($poem['prompt']); ?>"</span>
                </div>
                
                <!-- Action Controls (Owner Only) -->
                <?php if ($is_owner || $is_admin): ?>
                    <div class="d-flex flex-wrap gap-2 justify-content-center border-top border-dark-subtle pt-4 mt-5">
                        
                        <!-- Edit Title Trigger -->
                        <?php if ($is_owner): ?>
                            <button class="btn btn-outline-cyan btn-sm px-3 py-2" data-bs-toggle="collapse" data-bs-target="#editTitleCollapse" aria-expanded="false" aria-controls="editTitleCollapse">
                                <i class="fa-regular fa-pen-to-square me-1"></i>Edit Title
                            </button>
                        <?php endif; ?>
                        
                        <!-- Toggle public share -->
                        <?php if ($is_owner): ?>
                            <button id="btnShareToggle" class="btn btn-sm px-3 py-2 <?php echo $poem['is_shared'] ? 'btn-cyan' : 'btn-outline-light-silver'; ?>" onclick="togglePoemSharing(<?php echo $poem['id']; ?>)">
                                <i class="fa-solid <?php echo $poem['is_shared'] ? 'fa-globe' : 'fa-lock'; ?> me-1"></i>
                                <span><?php echo $poem['is_shared'] ? 'Shared Publicly' : 'Make Public'; ?></span>
                            </button>
                        <?php endif; ?>
                        
                        <!-- Exporters -->
                        <div class="dropdown">
                            <button class="btn btn-outline-light-silver btn-sm px-3 py-2 dropdown-toggle" type="button" id="exportMenu" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fa-solid fa-file-export me-1"></i>Export
                            </button>
                            <ul class="dropdown-menu dropdown-menu-dark custom-dropdown" aria-labelledby="exportMenu">
                                <li>
                                    <a class="dropdown-item" href="export_pdf.php?id=<?php echo $poem['id']; ?>" target="_blank">
                                        <i class="fa-regular fa-file-pdf fa-fw me-2 text-danger"></i>PDF Document
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="export_txt.php?id=<?php echo $poem['id']; ?>">
                                        <i class="fa-regular fa-file-lines fa-fw me-2 text-info"></i>Text File (.txt)
                                    </a>
                                </li>
                            </ul>
                        </div>
                        
                        <!-- Copy Text -->
                        <button class="btn btn-outline-light-silver btn-sm px-3 py-2" onclick="copyPoemText()">
                            <i class="fa-regular fa-copy me-1"></i>Copy Text
                        </button>
                        
                        <!-- Delete Poem -->
                        <a href="delete_poem.php?id=<?php echo $poem['id']; ?>" class="btn btn-outline-danger btn-sm px-3 py-2" onclick="return confirm('Are you sure you want to delete this poem? This action is permanent.');">
                            <i class="fa-regular fa-trash-can me-1"></i>Delete
                        </a>
                    </div>
                    
                    <!-- Edit Title Dropdown Form -->
                    <?php if ($is_owner): ?>
                        <div class="collapse mt-3" id="editTitleCollapse">
                            <div class="card bg-dark-deep border-dark-subtle p-3 rounded-3 max-width-400 mx-auto">
                                <form action="poem_view.php?id=<?php echo $poem['id']; ?>" method="POST">
                                    <input type="hidden" name="action" value="update_title">
                                    <div class="mb-3">
                                        <label for="new_title" class="form-label text-secondary small font-outfit text-start d-block">New Title</label>
                                        <input type="text" class="form-control form-control-sm bg-dark border-dark-subtle text-light" id="new_title" name="new_title" value="<?php echo htmlspecialchars($poem['title']); ?>" required>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-purple btn-sm px-3 font-outfit">Save Title</button>
                                        <button type="button" class="btn btn-outline-secondary btn-sm px-3" data-bs-toggle="collapse" data-bs-target="#editTitleCollapse">Cancel</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                <?php else: ?>
                    <!-- Guest View Actions (shared poem) -->
                    <div class="d-flex flex-wrap gap-2 justify-content-center border-top border-dark-subtle pt-4 mt-5">
                        <button class="btn btn-outline-light-silver btn-sm px-3 py-2" onclick="copyPoemText()">
                            <i class="fa-regular fa-copy me-1"></i>Copy Text
                        </button>
                        <a class="btn btn-outline-light-silver btn-sm px-3 py-2" href="export_pdf.php?id=<?php echo $poem['id']; ?>" target="_blank">
                            <i class="fa-regular fa-file-pdf me-1"></i>PDF
                        </a>
                        <a class="btn btn-outline-light-silver btn-sm px-3 py-2" href="export_txt.php?id=<?php echo $poem['id']; ?>">
                            <i class="fa-regular fa-file-lines me-1"></i>TXT
                        </a>
                    </div>
                <?php endif; ?>
                
                <!-- Shareable link container (visible when shared) -->
                <div id="shareLinkContainer" class="<?php echo $poem['is_shared'] ? 'd-block' : 'd-none'; ?> mt-4">
                    <div class="input-group input-group-sm max-width-500 mx-auto">
                        <span class="input-group-text bg-dark-deep border-dark-subtle text-secondary font-outfit small">Public Link</span>
                        <input type="text" class="form-control bg-dark-deep border-dark-subtle text-cyan-light font-outfit small" id="shareUrlField" readonly value="http://<?php echo $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>">
                        <button class="btn btn-outline-cyan font-outfit" type="button" onclick="copyShareUrl()"><i class="fa-regular fa-copy me-1"></i>Copy Link</button>
                    </div>
                </div>
                
            </div>
        </div>
        
    </div>
</div>

<script>
// JS Copy utilities
function copyPoemText() {
    // Extract text and copy
    const title = document.getElementById('poemTitle').innerText;
    const body = document.getElementById('poemText').innerText;
    const author = "<?php echo htmlspecialchars($poem['author_name']); ?>";
    const fullText = `${title}\nby ${author}\n\n${body}\n\nGenerated via MuseAI`;
    
    navigator.clipboard.writeText(fullText).then(() => {
        alert("Poem text copied to clipboard!");
    }).catch(err => {
        console.error("Failed to copy text: ", err);
    });
}

function copyShareUrl() {
    const field = document.getElementById('shareUrlField');
    field.select();
    field.setSelectionRange(0, 99999);
    navigator.clipboard.writeText(field.value).then(() => {
        alert("Public shareable link copied!");
    });
}
</script>

<?php require_once 'includes/footer.php'; ?>
