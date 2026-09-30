<?php
// index.php
// MuseAI Homepage / Landing Page

$title = "MuseAI | Write Poetry with Artificial Intelligence";
$description = "MuseAI is a state-of-the-art AI poetry generator that transforms prompts, moods, and styles into beautifully crafted verses instantly.";
require_once 'includes/header.php';

// Fetch a few public shared poems for the showcase
$stmt = $conn->prepare("SELECT p.*, u.name as author_name FROM poems p 
                        JOIN users u ON p.user_id = u.id 
                        WHERE p.is_shared = 1 
                        ORDER BY p.created_at DESC LIMIT 3");
$stmt->execute();
$public_showcase = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!-- Hero Banner Section -->
<section class="hero-section text-start py-5">
    <div class="row align-items-center g-5">
        <!-- Hero Text -->
        <div class="col-lg-6">
            <h1 class="hero-title font-outfit fw-bold text-light mb-3">
                Where Human Soul Meets <br>
                <span class="gradient-text">Artificial Intelligence</span>
            </h1>
            <p class="text-secondary fs-5 mb-4">
                Turn your thoughts, settings, or emotions into structured poetic stanzas instantly. Customize styles, adjust emotional tones, and print your creations.
            </p>
            
            <!-- Quick prompt selector / search -->
            <div class="card glass-card shadow-lg border-0 p-2 mb-4">
                <form action="generate.php" method="GET" class="row g-2 p-1">
                    <div class="col-sm-8 col-md-9">
                        <input type="text" name="prompt" class="form-control bg-dark-deep border-0 text-light py-3 px-3" placeholder="Enter a theme... (e.g. Moonlight shadows, Forest rain)" required>
                    </div>
                    <div class="col-sm-4 col-md-3 d-grid">
                        <button type="submit" class="btn btn-purple font-outfit fw-bold py-3">
                            Try It Free
                        </button>
                    </div>
                </form>
            </div>
            
            <div class="text-secondary small font-outfit">
                No account required to write poetry. <a href="register.php" class="text-cyan text-decoration-none ms-1 hover-underline fw-medium">Create library account <i class="fa-solid fa-arrow-right ms-1 small"></i></a>
            </div>
        </div>
        <!-- Hero Image -->
        <div class="col-lg-6 text-center">
            <div class="hero-image-wrapper p-2 bg-white rounded-4 shadow-lg border border-light">
                <img src="assets/images/hero_poetry.png" alt="AI Poetry Quill" class="img-fluid rounded-4 transition hover-lift">
            </div>
        </div>
    </div>
</section>

<!-- About Us Section -->
<section class="py-5 border-top border-dark-subtle">
    <div class="row align-items-center g-5">
        <!-- About Image -->
        <div class="col-lg-6 text-center order-2 order-lg-1">
            <div class="about-image-wrapper p-2 bg-white rounded-4 shadow-lg border border-light">
                <img src="assets/images/about_poetry.png" alt="AI and Quill Fusion" class="img-fluid rounded-4 transition hover-lift">
            </div>
        </div>
        <!-- About Text -->
        <div class="col-lg-6 order-1 order-lg-2">
            <span class="badge bg-purple-light text-cyan-light rounded-pill px-3 py-2 font-outfit mb-3">About MuseAI</span>
            <h2 class="font-outfit fw-bold text-light display-6 mb-3">Blending Art & Technology</h2>
            <p class="text-secondary fs-6 mb-4">
                MuseAI is a specialized AI-powered poetry platform created to explore the intersection of classical literary formats and modern artificial intelligence algorithms.
            </p>
            <p class="text-secondary fs-6 mb-4">
                Whether you are a professional writer looking for creative inspiration, a student studying poetic structures, or simply someone wanting to express complex emotions in verse, MuseAI provides a tailored, distraction-free environment to instantly write, store, and print original stanzas.
            </p>
            <a href="about.php" class="btn btn-outline-cyan font-outfit fw-bold px-4 py-2">
                Learn About Our Engine
            </a>
        </div>
    </div>
</section>

<!-- Features Grid Section -->
<section class="py-5 my-4 border-top border-dark-subtle">
    <div class="text-center mb-5">
        <h2 class="font-outfit fw-bold text-light display-6 mb-2">Platform Features</h2>
        <p class="text-secondary max-width-500 mx-auto">We combine advanced rule-based semantic structure algorithms with modern LLMs to craft unique poetic outputs.</p>
    </div>
    
    <div class="row g-4">
        <!-- Feature 1 -->
        <div class="col-md-4">
            <div class="card glass-card border-0 p-4 h-100 hover-lift text-center">
                <div class="fs-1 text-cyan mb-3"><i class="fa-solid fa-wand-magic-sparkles"></i></div>
                <h4 class="font-outfit text-light fw-semibold mb-2">Keyword Processing</h4>
                <p class="text-secondary small mb-0">Our engine parses your prompt to extract core sentiments, nouns, and contexts, seeding them directly into poetry structures.</p>
            </div>
        </div>
        
        <!-- Feature 2 -->
        <div class="col-md-4">
            <div class="card glass-card border-0 p-4 h-100 hover-lift text-center">
                <div class="fs-1 text-violet mb-3"><i class="fa-solid fa-sliders"></i></div>
                <h4 class="font-outfit text-light fw-semibold mb-2">Tone & Style Tuning</h4>
                <p class="text-secondary small mb-0">Select from Classical, Romantic, Sad, or Modern Free Verse styles and adjust the emotional pitch to match your vision.</p>
            </div>
        </div>
        
        <!-- Feature 3 -->
        <div class="col-md-4">
            <div class="card glass-card border-0 p-4 h-100 hover-lift text-center">
                <div class="fs-1 text-purple-light mb-3"><i class="fa-solid fa-folder-open"></i></div>
                <h4 class="font-outfit text-light fw-semibold mb-2">Personal Archive</h4>
                <p class="text-secondary small mb-0">Log in to save your generated masterpieces, toggle public visibility to share them, and export them into elegant files.</p>
            </div>
        </div>
    </div>
</section>

<!-- Public Showcase Section -->
<section class="py-5 border-top border-dark-subtle">
    <div class="text-center mb-5">
        <h2 class="font-outfit fw-bold text-light display-6 mb-2">Featured Showcase</h2>
        <p class="text-secondary max-width-500 mx-auto">Masterpieces written by our community using MuseAI generator.</p>
    </div>
    
    <?php if (empty($public_showcase)): ?>
        <!-- fallback cards if no public poems are saved in database yet -->
        <div class="row g-4">
            <!-- Fallback 1 -->
            <div class="col-md-4">
                <div class="card glass-card h-100 border-0 p-4 d-flex flex-column justify-content-between">
                    <div>
                        <span class="badge bg-dark-deep border border-dark-subtle text-secondary-light font-outfit rounded-pill mb-3">Romantic</span>
                        <h4 class="font-playfair text-light mb-3 italic fw-bold">Whispers of Spring</h4>
                        <div class="poem-preview font-playfair fs-6 text-light-silver lh-lg border-start border-dark-subtle ps-3 mb-4">
                            In the golden embrace of the quiet night,<br>
                            Your soul still glows like a fading light...
                        </div>
                    </div>
                    <div class="text-secondary small font-outfit border-top border-dark-subtle pt-2 mt-2">
                        by <span class="text-light-silver">Creative Poet</span>
                    </div>
                </div>
            </div>
            <!-- Fallback 2 -->
            <div class="col-md-4">
                <div class="card glass-card h-100 border-0 p-4 d-flex flex-column justify-content-between">
                    <div>
                        <span class="badge bg-dark-deep border border-dark-subtle text-secondary-light font-outfit rounded-pill mb-3">Sadness</span>
                        <h4 class="font-playfair text-light mb-3 italic fw-bold">Echoes of Winter</h4>
                        <div class="poem-preview font-playfair fs-6 text-light-silver lh-lg border-start border-dark-subtle ps-3 mb-4">
                            A cold tear fell where silence grew,<br>
                            The ancient memory fades in shades of blue...
                        </div>
                    </div>
                    <div class="text-secondary small font-outfit border-top border-dark-subtle pt-2 mt-2">
                        by <span class="text-light-silver">Lament Poet</span>
                    </div>
                </div>
            </div>
            <!-- Fallback 3 -->
            <div class="col-md-4">
                <div class="card glass-card h-100 border-0 p-4 d-flex flex-column justify-content-between">
                    <div>
                        <span class="badge bg-dark-deep border border-dark-subtle text-secondary-light font-outfit rounded-pill mb-3">Nature</span>
                        <h4 class="font-playfair text-light mb-3 italic fw-bold">Rhythms of Forest</h4>
                        <div class="poem-preview font-playfair fs-6 text-light-silver lh-lg border-start border-dark-subtle ps-3 mb-4">
                            The green leaf sings beneath the sky,<br>
                            While gentle winds go dancing softly by...
                        </div>
                    </div>
                    <div class="text-secondary small font-outfit border-top border-dark-subtle pt-2 mt-2">
                        by <span class="text-light-silver">Forest Warden</span>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($public_showcase as $poem): ?>
                <?php
                // Extract snippet
                $lines = explode("\n", $poem['poem_text']);
                $snippet_lines = array_slice(array_filter(array_map('trim', $lines)), 0, 2);
                $snippet = implode("<br>", $snippet_lines) . "...";
                ?>
                <div class="col-md-4">
                    <div class="card glass-card h-100 border-0 p-4 d-flex flex-column justify-content-between hover-lift">
                        <div>
                            <span class="badge bg-dark-deep border border-dark-subtle text-secondary-light font-outfit rounded-pill mb-3">
                                <?php echo ucfirst($poem['style']); ?>
                            </span>
                            <h4 class="font-playfair text-light mb-3 italic fw-bold">
                                <a href="poem_view.php?id=<?php echo $poem['id']; ?>" class="text-light text-decoration-none hover-cyan">
                                    <?php echo htmlspecialchars($poem['title']); ?>
                                </a>
                            </h4>
                            <div class="poem-preview font-playfair fs-6 text-light-silver lh-lg border-start border-dark-subtle ps-3 mb-4">
                                <?php echo $snippet; ?>
                            </div>
                        </div>
                        <div class="text-secondary small font-outfit border-top border-dark-subtle pt-2 mt-2 d-flex justify-content-between align-items-center">
                            <span>by <span class="text-light-silver"><?php echo htmlspecialchars($poem['author_name']); ?></span></span>
                            <a href="poem_view.php?id=<?php echo $poem['id']; ?>" class="text-cyan text-decoration-none">Read <i class="fa-solid fa-angle-right ms-1"></i></a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<?php require_once 'includes/footer.php'; ?>
