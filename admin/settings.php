<?php
// admin/settings.php
// Global system settings manager

$title = "System Settings";
$description = "Configure default generator modes, API keys, and suggestions taxonomy.";
require_once __DIR__ . '/../includes/header.php';

// Enforce Admin Access
require_admin();

$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mode = sanitize($_POST['generation_mode'] ?? 'rule_based');
    $key = trim($_POST['gemini_api_key'] ?? '');
    $themes = sanitize($_POST['featured_themes'] ?? '');
    
    // Validations
    if ($mode !== 'rule_based' && $mode !== 'api_based') {
        $error = "Invalid generation mode selected.";
    } elseif ($mode === 'api_based' && empty($key)) {
        $error = "API key is required if switching to API Generation Mode.";
    }
    
    if (empty($error)) {
        try {
            set_setting($conn, 'generation_mode', $mode);
            set_setting($conn, 'gemini_api_key', $key);
            set_setting($conn, 'featured_themes', $themes);
            $success = true;
        } catch (Exception $e) {
            $error = "Failed to update configurations: " . $e->getMessage();
        }
    }
}

// Fetch current configurations
$current_mode = get_setting($conn, 'generation_mode', 'rule_based');
$current_key = get_setting($conn, 'gemini_api_key', '');
$current_themes = get_setting($conn, 'featured_themes', 'love,rain,nature,sadness,hope,dreams,motivation');
?>

<div class="row align-items-center mb-4">
    <div class="col">
        <h2 class="font-outfit fw-bold text-light mb-1">
            <i class="fa-solid fa-sliders text-purple-light me-2 small"></i>System Settings
        </h2>
        <p class="text-secondary mb-0">Configure global platform generator parameters.</p>
    </div>
    <div class="col-auto">
        <a href="dashboard.php" class="btn btn-outline-light-silver btn-sm font-outfit">
            <i class="fa-solid fa-arrow-left me-1"></i>Back to Admin
        </a>
    </div>
</div>

<?php if ($success): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fa-solid fa-circle-check me-2"></i>Global settings updated successfully!
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fa-solid fa-circle-exclamation me-2"></i><?php echo $error; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="row g-4">
    <!-- Form settings -->
    <div class="col-lg-8">
        <div class="card glass-card border-0 shadow-sm">
            <div class="card-body p-4 p-md-5">
                <form action="settings.php" method="POST">
                    
                    <!-- Generation Mode -->
                    <div class="mb-4">
                        <label class="form-label text-light fw-semibold font-outfit mb-2">Generation Mode</label>
                        <p class="text-secondary small mb-3">Choose whether the platform uses rule-based templates locally or makes API requests to Gemini.</p>
                        
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="generation_mode" id="mode_rule" value="rule_based" <?php echo ($current_mode === 'rule_based') ? 'checked' : ''; ?> onchange="toggleKeyField(false)">
                            <label class="form-check-input-label text-light-silver small" for="mode_rule">
                                <strong>Rule-Based Template Engine (Default)</strong> - High speed, offline compatible, zero network costs.
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="generation_mode" id="mode_api" value="api_based" <?php echo ($current_mode === 'api_based') ? 'checked' : ''; ?> onchange="toggleKeyField(true)">
                            <label class="form-check-input-label text-light-silver small" for="mode_api">
                                <strong>Advanced AI API Engine (Gemini 1.5 Flash)</strong> - Generates context-rich, human-like unique poems.
                            </label>
                        </div>
                    </div>
                    
                    <hr class="border-secondary opacity-25 my-4">
                    
                    <!-- API Key Field -->
                    <div class="mb-4" id="apiKeyContainer">
                        <label for="gemini_api_key" class="form-label text-light fw-semibold font-outfit mb-2">Gemini API Key</label>
                        <p class="text-secondary small mb-3">Requires a valid API key from Google AI Studio. Stored securely in database.</p>
                        <div class="input-group">
                            <span class="input-group-text bg-dark-deep border-end-0 border-dark-subtle text-secondary">
                                <i class="fa-solid fa-key"></i>
                            </span>
                            <input type="password" class="form-control bg-dark-deep border-start-0 border-dark-subtle text-light" id="gemini_api_key" name="gemini_api_key" value="<?php echo htmlspecialchars($current_key); ?>" placeholder="AIzaSy...">
                            <button class="btn btn-outline-secondary border-dark-subtle" type="button" onclick="togglePasswordVisibility()"><i class="fa-regular fa-eye" id="toggleEye"></i></button>
                        </div>
                    </div>
                    
                    <hr class="border-secondary opacity-25 my-4">
                    
                    <!-- Featured Theme tags -->
                    <div class="mb-4">
                        <label for="featured_themes" class="form-label text-light fw-semibold font-outfit mb-2">Featured Workbench Tags</label>
                        <p class="text-secondary small mb-3">Comma-separated tags shown as suggestion pills under prompt inputs (e.g. love, nature, rain, etc.)</p>
                        <input type="text" class="form-control bg-dark-deep border-dark-subtle text-light" id="featured_themes" name="featured_themes" value="<?php echo htmlspecialchars($current_themes); ?>" required>
                    </div>
                    
                    <!-- Submit -->
                    <button type="submit" class="btn btn-purple font-outfit fw-bold px-4 py-2 mt-2">
                        Save System Settings
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Info sidebar -->
    <div class="col-lg-4">
        <div class="card glass-card border-0 p-4 shadow-sm">
            <h4 class="font-outfit text-light fw-semibold mb-3">API Setup Instructions</h4>
            <ol class="text-secondary small ps-3 mb-0">
                <li class="mb-2">Visit <a href="https://aistudio.google.com/" target="_blank" class="text-cyan text-decoration-none">Google AI Studio</a>.</li>
                <li class="mb-2">Sign in and click <strong>Get API Key</strong>.</li>
                <li class="mb-2">Copy the key and paste it on this settings page.</li>
                <li class="mb-2">Switch the generation mode above to <strong>API Engine</strong>.</li>
                <li class="mb-2">Go to the workbench and generate highly creative poems!</li>
            </ol>
        </div>
    </div>
</div>

<script>
// Toggle key field visibility based on radio checks
function toggleKeyField(isRequired) {
    const keyField = document.getElementById('gemini_api_key');
    if (isRequired) {
        keyField.setAttribute('required', 'required');
    } else {
        keyField.removeAttribute('required');
    }
}

// Toggle display password/text
function togglePasswordVisibility() {
    const keyField = document.getElementById('gemini_api_key');
    const eye = document.getElementById('toggleEye');
    if (keyField.type === "password") {
        keyField.type = "text";
        eye.className = "fa-regular fa-eye-slash";
    } else {
        keyField.type = "password";
        eye.className = "fa-regular fa-eye";
    }
}

// Initialize on load
document.addEventListener("DOMContentLoaded", function() {
    toggleKeyField(document.getElementById('mode_api').checked);
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
