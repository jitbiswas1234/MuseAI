<?php
// save_poem.php
// Endpoint to save generated poems to user collection

header('Content-Type: application/json');
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

if (!is_logged_in()) {
    echo json_encode([
        'status' => false,
        'message' => 'Authentication required. Please log in first.'
    ]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'status' => false,
        'message' => 'Invalid request method.'
    ]);
    exit();
}

$user_id = $_SESSION['user_id'];
$title = sanitize($_POST['title'] ?? '');
$prompt = sanitize($_POST['prompt'] ?? '');
$style = sanitize($_POST['style'] ?? 'romantic');
$tone = sanitize($_POST['tone'] ?? 'calm');
$length = sanitize($_POST['length'] ?? 'medium');
$poem_text = trim($_POST['poem_text'] ?? '');

if (empty($prompt) || empty($poem_text)) {
    echo json_encode([
        'status' => false,
        'message' => 'Invalid poem data. Cannot save empty poem.'
    ]);
    exit();
}

if (empty($title)) {
    $title = 'Untitled Poem';
}

try {
    $stmt = $conn->prepare("INSERT INTO poems (user_id, title, prompt, style, tone, length_type, poem_text) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssss", $user_id, $title, $prompt, $style, $tone, $length, $poem_text);
    $stmt->execute();
    
    $poem_id = $conn->insert_id;
    $stmt->close();
    
    echo json_encode([
        'status' => true,
        'message' => 'Poem saved to your collection successfully!',
        'id' => $poem_id
    ]);
} catch (Exception $e) {
    echo json_encode([
        'status' => false,
        'message' => 'Failed to save poem. Database error: ' . $e->getMessage()
    ]);
}
?>
