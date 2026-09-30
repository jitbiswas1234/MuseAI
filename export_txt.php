<?php
// export_txt.php
// Exports a poem as a plain text file for download

require_once 'config/database.php';
require_once 'includes/functions.php';

$poem_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($poem_id <= 0) {
    die("Invalid poem ID.");
}

// Fetch poem and author
$stmt = $conn->prepare("SELECT p.*, u.name as author_name FROM poems p 
                        JOIN users u ON p.user_id = u.id 
                        WHERE p.id = ? LIMIT 1");
$stmt->bind_param("i", $poem_id);
$stmt->execute();
$poem = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$poem) {
    die("Poem not found.");
}

$is_owner = (is_logged_in() && $_SESSION['user_id'] == $poem['user_id']);
$is_admin = is_admin();

// Check permission
if (!$is_owner && !$is_admin && !$poem['is_shared']) {
    die("Access denied. This poem is private.");
}

// Clean and prepare content
$title = htmlspecialchars_decode($poem['title'], ENT_QUOTES);
$author = htmlspecialchars_decode($poem['author_name'], ENT_QUOTES);
$prompt = htmlspecialchars_decode($poem['prompt'], ENT_QUOTES);
$style = ucfirst($poem['style']);
$tone = ucfirst($poem['tone']);
$date = date('F j, Y', strtotime($poem['created_at']));
$poem_text = htmlspecialchars_decode($poem['poem_text'], ENT_QUOTES);

// Build output string
$output = "=========================================================\n";
$output .= "                     MUSEAI POETRY                       \n";
$output .= "=========================================================\n\n";
$output .= "Title:      $title\n";
$output .= "Author:     $author\n";
$output .= "Date:       $date\n";
$output .= "Style:      $style\n";
$output .= "Tone:       $tone\n\n";
$output .= "Prompt:\n\"$prompt\"\n";
$output .= "---------------------------------------------------------\n\n";
$output .= $poem_text;
$output .= "\n\n---------------------------------------------------------\n";
$output .= "Generated automatically using MuseAI Poetry Generator.\n";
$output .= "=========================================================\n";

// Sanitize filename
$filename = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $title)) . '.txt';

// Headers for file download
header('Content-Type: text/plain; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . strlen($output));

echo $output;
exit();
?>
