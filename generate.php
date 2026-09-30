<?php
// generate.php
// Interactive Poetry Generation Workbench

$title = "Creation Workbench";
$description = "Select style, tone, and length configurations to generate custom poetry using MuseAI's generation logic.";
require_once 'includes/header.php';

// Fetch user defaults if logged in to pre-fill the form
$pref_style = 'romantic';
$pref_tone = 'calm';
$pref_length = 'medium';

if (is_logged_in()) {
    $userId = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT preferred_style, preferred_tone, preferred_length FROM user_preferences WHERE user_id = ? LIMIT 1");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $pref_style = $row['preferred_style'];
        $pref_tone = $row['preferred_tone'];
        $pref_length = $row['preferred_length'];
    }
    $stmt->close();
}

// Fetch default suggestions
$featured_themes_str = get_setting($conn, 'featured_themes', 'love,rain,nature,sadness,hope,dreams,motivation');
$suggestions = array_map('trim', explode(',', $featured_themes_str));
?>

<div class="row g-4 my-3">
    <!-- Workbench Options (Left Column) -->
    <div class="col-lg-4">
        <div class="card glass-card shadow border-0 h-100">
            <div class="card-body p-4">
                <h3 class="font-outfit fw-bold text-light mb-4">
                    <i class="fa-solid fa-sliders text-cyan me-2 small"></i>Configure
                </h3>
                
                <form id="generateForm" novalidate>
                    <!-- Prompt -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label for="prompt" class="form-label text-secondary-light small font-outfit mb-0">Poem Topic / Prompt</label>
                            <button type="button" class="btn btn-xs btn-outline-cyan font-outfit rounded-pill px-2 py-0" onclick="autogenerateTopic()">
                                <i class="fa-solid fa-wand-magic-sparkles me-1"></i>Surprise Me
                            </button>
                        </div>
                        <textarea class="form-control bg-dark-deep border-dark-subtle text-light" id="prompt" name="prompt" rows="3" placeholder="Describe the feeling, setting, or theme (e.g., 'Rainy evening by the sea' or 'A mother's smile')..." required></textarea>
                        
                        <!-- Suggestions pills -->
                        <div class="mt-2">
                            <span class="text-secondary small d-block mb-1">Ideas:</span>
                            <div class="d-flex flex-wrap gap-1">
                                <?php foreach ($suggestions as $sug): ?>
                                    <button type="button" class="btn btn-dark-deep btn-xs px-2 py-1 text-secondary-light border-0 hover-cyan transition rounded-pill" onclick="setPrompt('<?php echo ucfirst($sug); ?>')">
                                        #<?php echo ucfirst($sug); ?>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Title (Optional) -->
                    <div class="mb-3">
                        <label for="poem_title" class="form-label text-secondary-light small font-outfit">Poem Title (Optional)</label>
                        <input type="text" class="form-control bg-dark-deep border-dark-subtle text-light" id="poem_title" name="title" placeholder="Leave empty for auto-title">
                    </div>
                    
                    <!-- Style selection -->
                    <div class="mb-3">
                        <label for="style" class="form-label text-secondary-light small font-outfit">Poetic Style</label>
                        <select class="form-select bg-dark-deep border-dark-subtle text-light" id="style" name="style">
                            <option value="romantic" <?php echo ($pref_style === 'romantic') ? 'selected' : ''; ?>>Romantic (Stanza Rhymes)</option>
                            <option value="sad" <?php echo ($pref_style === 'sad') ? 'selected' : ''; ?>>Sad / Melancholic</option>
                            <option value="nature" <?php echo ($pref_style === 'nature') ? 'selected' : ''; ?>>Nature & Imagery</option>
                            <option value="inspirational" <?php echo ($pref_style === 'inspirational') ? 'selected' : ''; ?>>Inspirational / Motivational</option>
                            <option value="modern" <?php echo ($pref_style === 'modern') ? 'selected' : ''; ?>>Modern (Free Verse)</option>
                            <option value="classic" <?php echo ($pref_style === 'classic') ? 'selected' : ''; ?>>Classic (Shakespearean)</option>
                        </select>
                    </div>
                    
                    <!-- Tone Selection -->
                    <div class="mb-3">
                        <label for="tone" class="form-label text-secondary-light small font-outfit">Emotional Tone</label>
                        <select class="form-select bg-dark-deep border-dark-subtle text-light" id="tone" name="tone">
                            <option value="calm" <?php echo ($pref_tone === 'calm') ? 'selected' : ''; ?>>Calm & Peaceful</option>
                            <option value="happy" <?php echo ($pref_tone === 'happy') ? 'selected' : ''; ?>>Happy & Bright</option>
                            <option value="dramatic" <?php echo ($pref_tone === 'dramatic') ? 'selected' : ''; ?>>Dramatic & Intense</option>
                            <option value="melancholic" <?php echo ($pref_tone === 'melancholic') ? 'selected' : ''; ?>>Melancholic & Somber</option>
                            <option value="motivational" <?php echo ($pref_tone === 'motivational') ? 'selected' : ''; ?>>Motivational & Strong</option>
                        </select>
                    </div>
                    
                    <!-- Length selection -->
                    <div class="mb-4">
                        <label class="form-label text-secondary-light small font-outfit d-block">Poem Length</label>
                        <div class="btn-group w-100" role="group" aria-label="Length choice">
                            <input type="radio" class="btn-check" name="length" id="len_short" value="short" <?php echo ($pref_length === 'short') ? 'checked' : ''; ?>>
                            <label class="btn btn-outline-dark-subtle text-secondary-light border-dark-subtle py-2 font-outfit" for="len_short">Short</label>
                            
                            <input type="radio" class="btn-check" name="length" id="len_medium" value="medium" <?php echo ($pref_length === 'medium') ? 'checked' : ''; ?>>
                            <label class="btn btn-outline-dark-subtle text-secondary-light border-dark-subtle py-2 font-outfit" for="len_medium">Medium</label>
                            
                            <input type="radio" class="btn-check" name="length" id="len_long" value="long" <?php echo ($pref_length === 'long') ? 'checked' : ''; ?>>
                            <label class="btn btn-outline-dark-subtle text-secondary-light border-dark-subtle py-2 font-outfit" for="len_long">Long</label>
                        </div>
                    </div>
                    
                    <!-- Submit Button -->
                    <button type="submit" id="btnGenerate" class="btn btn-purple w-100 py-3 font-outfit fw-bold rounded-3">
                        <i class="fa-solid fa-wand-magic-sparkles me-2"></i>Generate Verse
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Poetry Canvas (Right Column) -->
    <div class="col-lg-8">
        <div class="card glass-card shadow border-0 h-100 d-flex flex-column">
            <div class="card-body p-4 p-md-5 d-flex flex-column flex-grow-1 justify-content-center text-center relative">
                <!-- Welcome placeholder -->
                <div id="canvasPlaceholder" class="py-5">
                    <div class="placeholder-icon mb-4">
                        <i class="fa-solid fa-feather-pointed text-purple-light opacity-50 fs-1"></i>
                    </div>
                    <h3 class="font-outfit fw-bold text-light mb-2">Poetry Canvas</h3>
                    <p class="text-secondary mx-auto max-width-400">Configure settings on the left and click Generate. Your custom poem will appear here with editing and sharing options.</p>
                </div>
                
                <!-- Loading Screen -->
                <div id="canvasLoader" class="d-none py-5">
                    <div class="spinner-border text-cyan mb-4" role="status" style="width: 3rem; height: 3rem;">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <h4 class="font-outfit fw-bold text-cyan mb-2" id="loadingStep">Analyzing prompt...</h4>
                    <p class="text-secondary italic font-playfair fs-5">"Poetry is the core of our quiet shadows."</p>
                </div>
                
                <!-- Poem Display Canvas -->
                <div id="canvasResult" class="d-none flex-grow-1 flex-column justify-content-between">
                    <!-- Top Ribbon info -->
                    <div class="d-flex align-items-center justify-content-between border-bottom border-dark-subtle pb-3 mb-4">
                        <span class="badge bg-dark-deep border border-secondary text-secondary-light rounded-pill px-3 py-2 font-outfit">
                            <i class="fa-solid fa-cpu me-1 text-cyan"></i><span id="resEngine">Rule-Based Engine</span>
                        </span>
                        <span class="text-secondary small font-outfit" id="resMeta">Romantic | Calm</span>
                    </div>
                    
                    <!-- Main Poem Body -->
                    <div class="poem-body-container my-4 py-2">
                        <h2 class="font-playfair font-weight-bold text-light mb-4 italic" id="resTitle">Untitled Poem</h2>
                        <div class="poem-text font-playfair fs-4 text-light-silver lh-lg whitespace-pre-line" id="resText">
                            <!-- Poem text goes here -->
                        </div>
                    </div>
                    
                    <!-- Action Bar -->
                    <div class="d-flex flex-wrap gap-2 justify-content-center border-top border-dark-subtle pt-4 mt-4">
                        <!-- Save to collection -->
                        <?php if (is_logged_in()): ?>
                            <button id="btnSave" class="btn btn-purple btn-sm px-3 py-2 d-flex align-items-center gap-2">
                                <i class="fa-regular fa-bookmark"></i><span>Save to Collection</span>
                            </button>
                        <?php else: ?>
                            <a href="login.php" class="btn btn-outline-cyan btn-sm px-3 py-2 d-flex align-items-center gap-2" title="Login to save poems">
                                <i class="fa-solid fa-arrow-right-to-bracket"></i><span>Login to Save</span>
                            </a>
                        <?php endif; ?>
                        
                        <!-- Copy to clipboard -->
                        <button id="btnCopy" class="btn btn-outline-light-silver btn-sm px-3 py-2 d-flex align-items-center gap-2">
                            <i class="fa-regular fa-copy"></i><span>Copy Text</span>
                        </button>
                        
                        <!-- Share button -->
                        <button id="btnShare" class="btn btn-outline-light-silver btn-sm px-3 py-2 d-flex align-items-center gap-2">
                            <i class="fa-solid fa-share-nodes"></i><span>Share</span>
                        </button>
                        
                        <!-- Exporters -->
                        <div class="dropdown">
                            <button class="btn btn-outline-light-silver btn-sm px-3 py-2 dropdown-toggle d-flex align-items-center gap-2" type="button" id="exportMenu" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fa-solid fa-file-export"></i><span>Export</span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-dark custom-dropdown" aria-labelledby="exportMenu">
                                <li>
                                    <button class="dropdown-item" onclick="exportPoem('pdf')">
                                        <i class="fa-regular fa-file-pdf fa-fw me-2 text-danger"></i>PDF Document
                                    </button>
                                </li>
                                <li>
                                    <button class="dropdown-item" onclick="exportPoem('txt')">
                                        <i class="fa-regular fa-file-lines fa-fw me-2 text-info"></i>Text File (.txt)
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// helper to set prompt suggestion
function setPrompt(val) {
    document.getElementById('prompt').value = val;
}

let typingInterval = null;
function autogenerateTopic() {
    if (typingInterval) {
        clearInterval(typingInterval);
    }
    const topics = [
        "A lonely lighthouse guiding ships through a violet storm",
        "Whispers of ancient trees in a forgotten autumn forest",
        "The first warm ray of spring melting a crystal tear",
        "Midnight jazz under the neon lights of a rainy city",
        "Echoes of stars falling into a silent desert",
        "A child chasing paper boats in a summer rain puddle",
        "An old leather book holding secrets of a bygone era",
        "Sunlight filtering through the leaves of a green canopy",
        "Fading footprints on a misty winter shoreline",
        "The silent conversation of two dancers in the dark",
        "A cup of warm tea on a frosty December morning",
        "Golden Hour casting long shadows on an empty street",
        "A clockwork butterfly seeking a flower of steel",
        "The heavy silence of a library at midnight",
        "Morning mist rising from a calm mountain lake",
        "A train traveling into the horizon of a setting sun",
        "The soft rustle of fallen leaves in the wind",
        "An forgotten piano playing alone in an empty theater",
        "Wildflowers blooming in the cracks of concrete",
        "A paper plane carrying wishes to the sky",
        "The gentle hum of bees in a lavender field",
        "Stardust trapped inside a glass jar",
        "The quiet reflection of city lights on a wet pavement",
        "A warm campfire under a sky full of constellations",
        "The scent of old rain on dry earth",
        "A sailor navigating by the guidance of the North Star",
        "The first snowfall drifting onto green pine branches",
        "Waves crashing softly against a sleeping cliffside",
        "Two cups of coffee on a table by a rainy window",
        "A hummingbird hovering near a scarlet rose"
    ];
    const textarea = document.getElementById('prompt');
    const randomTopic = topics[Math.floor(Math.random() * topics.length)];
    
    textarea.value = '';
    let i = 0;
    textarea.placeholder = "Weaving a surprise theme...";
    
    typingInterval = setInterval(() => {
        if (i < randomTopic.length) {
            textarea.value += randomTopic.charAt(i);
            i++;
        } else {
            clearInterval(typingInterval);
            textarea.placeholder = "Describe the feeling, setting, or theme...";
        }
    }, 20);
}
</script>

<?php require_once 'includes/footer.php'; ?>
