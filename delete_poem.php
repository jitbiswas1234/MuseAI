<?php
// delete_poem.php
// Endpoint/Controller to delete a poem from the collection

require_once 'config/database.php';
require_once 'includes/functions.php';

if (!is_logged_in()) {
    flash('login_error', 'Please log in to perform this action.', 'warning');
    redirect('login.php');
}

$poem_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$user_id = $_SESSION['user_id'];
$is_admin = is_admin();

if ($poem_id <= 0) {
    flash('general_error', 'Invalid poem ID.', 'danger');
    redirect('my_poems.php');
}

// Fetch poem to check ownership
$stmt = $conn->prepare("SELECT user_id FROM poems WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $poem_id);
$stmt->execute();
$res = $stmt->get_result();
$poem = $res->fetch_assoc();
$stmt->close();

if (!$poem) {
    flash('general_error', 'Poem not found.', 'danger');
    redirect('my_poems.php');
}

// Access check: must be owner or admin
if ($poem['user_id'] != $user_id && !$is_admin) {
    flash('general_error', 'Access denied. You do not have permission to delete this poem.', 'danger');
    redirect('my_poems.php');
}

// Execute deletion
try {
    $stmt = $conn->prepare("DELETE FROM poems WHERE id = ?");
    $stmt->bind_param("i", $poem_id);
    $stmt->execute();
    $stmt->close();
    
    flash('general_success', 'Poem deleted successfully.', 'success');
    
    // Redirect based on who deleted it
    if ($is_admin && $poem['user_id'] != $user_id) {
        redirect('admin/poems.php');
    } else {
        redirect('my_poems.php');
    }
} catch (Exception $e) {
    flash('general_error', 'Failed to delete poem: ' . $e->getMessage(), 'danger');
    redirect('my_poems.php');
}
?>
