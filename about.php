<?php
// about.php
// Informational page detailing the AI engine mechanics

$title = "About Engine";
$description = "Learn how MuseAI's poetry generator merges rule-based structure matrices with large language model APIs.";
require_once 'includes/header.php';
?>

<div class="row justify-content-center my-3">
    <div class="col-lg-10">
        <h2 class="font-outfit fw-bold text-light mb-1">
            <i class="fa-solid fa-circle-info text-cyan me-2 small"></i>About MuseAI Poetry Engine
        </h2>
        <p class="text-secondary mb-4">Understanding the technical stack, architecture, and logic behind the generation models.</p>
        
        <div class="row g-4">
            <!-- Engine mechanics -->
            <div class="col-md-7">
                <!-- Engine overview -->
                <div class="card glass-card border-0 p-4 p-md-5 shadow-sm mb-4">
                    <h4 class="font-outfit fw-bold text-cyan mb-3">Core Poetry Architecture</h4>
                    <p class="text-secondary small">MuseAI operates on a dual-engine architecture designed to ensure continuous availability, high performance, and poetic diversity.</p>
                    
                    <h5 class="font-outfit text-light fw-semibold mt-4 mb-2">1. Rule-Based Syntax Matrix (Default)</h5>
                    <p class="text-secondary small">
                        When configured in local mode, the engine uses natural language templates combined with dynamic word classification directories. It strips common English stop words from the user's prompt, isolates core concepts, and maps the prompt's tone (e.g. calm, dramatic) to vocabulary matrices. Random lists of adjectives, verbs, and nouns are mixed and injected into structural rhyming stanzas.
                    </p>
                    
                    <h5 class="font-outfit text-light fw-semibold mt-4 mb-2">2. LLM API Integration (Gemini 1.5 Flash)</h5>
                    <p class="text-secondary small">
                        For deep contextual interpretation, MuseAI can be switched to API mode in the admin panel. The engine wraps the user prompt, selected style, tone, and length constraints into a structured system template and fetches the response directly from the Gemini API via PHP cURL.
                    </p>
                    
                    <div class="alert bg-dark-deep border-dark-subtle p-3 rounded-3 mt-4 mb-0">
                        <p class="text-secondary-light small mb-0">
                            <i class="fa-solid fa-shield-halved text-cyan me-2"></i>
                            <strong>Fallback Safety:</strong> If API quota limits are hit or the internet connection is disrupted, the engine automatically catches the error and executes the local Rule-Based engine so the generation pipeline never breaks.
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- Tech Stack & specifications -->
            <div class="col-md-5">
                <!-- Stack breakdown -->
                <div class="card glass-card border-0 p-4 shadow-sm mb-4">
                    <h4 class="font-outfit fw-bold text-violet mb-3">Technology Stack</h4>
                    <ul class="list-unstyled mb-0">
                        <li class="mb-3 d-flex align-items-start gap-2">
                            <span class="text-cyan"><i class="fa-brands fa-php fs-5 fa-fw"></i></span>
                            <div>
                                <strong class="text-light-silver font-outfit small d-block">Backend Processing</strong>
                                <span class="text-secondary small">PHP (OOP & MySQLi bindings)</span>
                            </div>
                        </li>
                        <li class="mb-3 d-flex align-items-start gap-2">
                            <span class="text-violet"><i class="fa-solid fa-database fs-5 fa-fw"></i></span>
                            <div>
                                <strong class="text-light-silver font-outfit small d-block">Database & Logs</strong>
                                <span class="text-secondary small">MySQL (Prepared statements)</span>
                            </div>
                        </li>
                        <li class="mb-3 d-flex align-items-start gap-2">
                            <span class="text-purple-light"><i class="fa-brands fa-js fs-5 fa-fw"></i></span>
                            <div>
                                <strong class="text-light-silver font-outfit small d-block">Interactivity & AJAX</strong>
                                <span class="text-secondary small">Vanilla JS Fetch API & DOM events</span>
                            </div>
                        </li>
                        <li class="mb-3 d-flex align-items-start gap-2">
                            <span class="text-cyan-light"><i class="fa-brands fa-bootstrap fs-5 fa-fw"></i></span>
                            <div>
                                <strong class="text-light-silver font-outfit small d-block">Styling & UI</strong>
                                <span class="text-secondary small">Vanilla CSS3 & Bootstrap 5 Grid</span>
                            </div>
                        </li>
                        <li class="d-flex align-items-start gap-2">
                            <span class="text-danger"><i class="fa-regular fa-file-pdf fs-5 fa-fw"></i></span>
                            <div>
                                <strong class="text-light-silver font-outfit small d-block">Document Compilation</strong>
                                <span class="text-secondary small">Lightweight FPDF Library integration</span>
                            </div>
                        </li>
                    </ul>
                </div>
                
                <!-- Quick stats -->
                <div class="card glass-card border-0 p-4 shadow-sm text-center">
                    <h5 class="font-outfit text-light mb-3">Need to write poetry?</h5>
                    <p class="text-secondary small mb-3">Head over to the workbench and customize your verses now.</p>
                    <a href="generate.php" class="btn btn-purple btn-sm w-100 font-outfit fw-bold py-2">
                        Open Workbench
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
