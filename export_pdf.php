<?php
// export_pdf.php
// Generates a beautifully formatted PDF version of a poem using FPDF

require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/fpdf/fpdf.php';

$poem_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($poem_id <= 0) {
    die("Invalid poem ID.");
}

// Fetch poem details
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

// Check permissions
if (!$is_owner && !$is_admin && !$poem['is_shared']) {
    die("Access denied. This poem is private.");
}

// Extend FPDF for custom headers, footers, and page borders
class MusePDF extends FPDF {
    
    // Page border and background
    function Header() {
        // Draw elegant double border around page
        $this->SetDrawColor(180, 180, 180); // Light grey border
        $this->SetLineWidth(0.5);
        $this->Rect(10, 10, 190, 277);
        
        $this->SetDrawColor(120, 100, 150); // Deep violet inner border
        $this->SetLineWidth(0.2);
        $this->Rect(12, 12, 186, 273);
        
        // Header Text
        $this->SetY(15);
        $this->SetFont('Helvetica', 'I', 8);
        $this->SetTextColor(120, 120, 120);
        $this->Cell(0, 5, 'MuseAI Creative Poetry Hub', 0, 0, 'R');
    }
    
    function Footer() {
        // Page number
        $this->SetY(-20);
        $this->SetFont('Helvetica', 'I', 8);
        $this->SetTextColor(120, 120, 120);
        $this->Cell(0, 5, 'Page ' . $this->PageNo(), 0, 0, 'C');
        
        // Brand logo
        $this->SetY(-15);
        $this->SetFont('Helvetica', 'B', 8);
        $this->SetTextColor(120, 100, 150);
        $this->Cell(0, 5, 'Generated via MuseAI', 0, 0, 'C');
    }
}

// Create new PDF instance
$pdf = new MusePDF('P', 'mm', 'A4');
$pdf->SetMargins(20, 25, 20);
$pdf->AddPage();
$pdf->AliasNbPages();

// 1. Poem Title
$pdf->SetY(35);
$pdf->SetFont('Times', 'BI', 26);
$pdf->SetTextColor(40, 40, 50); // Slate color
$title = htmlspecialchars_decode($poem['title'], ENT_QUOTES);
// Convert string to ISO-8859-1 for FPDF compatibility
$title_iso = iconv('UTF-8', 'windows-1252//TRANSLIT', $title);
$pdf->Cell(0, 12, $title_iso, 0, 1, 'C');

// 2. Author info
$pdf->SetFont('Helvetica', 'I', 11);
$pdf->SetTextColor(110, 110, 110);
$author = 'by ' . htmlspecialchars_decode($poem['author_name'], ENT_QUOTES);
$author_iso = iconv('UTF-8', 'windows-1252//TRANSLIT', $author);
$pdf->Cell(0, 6, $author_iso, 0, 1, 'C');

$pdf->Ln(12);

// 3. Divider Line
$pdf->SetDrawColor(220, 220, 220);
$pdf->SetLineWidth(0.3);
$pdf->Line(60, $pdf->GetY(), 150, $pdf->GetY());

$pdf->Ln(12);

// 4. Poem Body (Centered, Times Italic)
$pdf->SetFont('Times', 'I', 15);
$pdf->SetTextColor(60, 60, 70);

$poem_text = htmlspecialchars_decode($poem['poem_text'], ENT_QUOTES);
$poem_iso = iconv('UTF-8', 'windows-1252//TRANSLIT', $poem_text);
$stanzas = explode("\n\n", $poem_iso);

foreach ($stanzas as $stanza) {
    $lines = explode("\n", $stanza);
    
    // We print each stanza as a block, indented to look centered
    foreach ($lines as $line) {
        $pdf->Cell(25); // Left indent
        $pdf->Cell(0, 7, trim($line), 0, 1, 'L');
    }
    $pdf->Ln(6); // Space between stanzas
}

// 5. Prompt metadata block (Bottom of the page)
$pdf->SetY(-65);
$pdf->SetFillColor(245, 245, 248);
$pdf->SetDrawColor(225, 225, 230);
$pdf->SetLineWidth(0.2);

// Start box drawing
$pdf->Rect(20, $pdf->GetY(), 170, 32, 'DF');

// Meta content
$pdf->SetY($pdf->GetY() + 3);
$pdf->SetFont('Helvetica', 'B', 8);
$pdf->SetTextColor(100, 100, 110);
$pdf->Cell(5);
$pdf->Cell(0, 5, 'METADATA INSIGHTS', 0, 1, 'L');

$pdf->SetFont('Helvetica', 'B', 7);
$pdf->SetTextColor(130, 130, 140);
$pdf->Cell(5);
$pdf->Cell(35, 5, 'Style: ' . strtoupper($poem['style']), 0, 0, 'L');
$pdf->Cell(35, 5, 'Tone: ' . strtoupper($poem['tone']), 0, 0, 'L');
$pdf->Cell(45, 5, 'Length: ' . strtoupper($poem['length_type']), 0, 0, 'L');
$pdf->Cell(0, 5, 'Date: ' . date('Y-m-d H:i', strtotime($poem['created_at'])), 0, 1, 'L');

$pdf->Ln(2);

$pdf->SetFont('Helvetica', 'BI', 8);
$pdf->SetTextColor(90, 90, 100);
$pdf->Cell(5);
$prompt = 'Prompt: "' . htmlspecialchars_decode($poem['prompt'], ENT_QUOTES) . '"';
$prompt_iso = iconv('UTF-8', 'windows-1252//TRANSLIT', $prompt);
// Clip prompt if too long for the box
if (strlen($prompt_iso) > 95) {
    $prompt_iso = substr($prompt_iso, 0, 92) . '...';
}
$pdf->Cell(0, 5, $prompt_iso, 0, 1, 'L');

// Output PDF inline (to open in browser tab)
$safe_title = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $title));
$pdf->Output('I', 'museai-' . $safe_title . '.pdf');
exit();
?>
