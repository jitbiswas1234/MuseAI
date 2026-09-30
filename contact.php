<?php
// contact.php
// User contact and support feedback form

$title = "Contact Support";
$description = "Get in touch with the MuseAI support team for feature requests, questions, or bugs.";
require_once 'includes/header.php';

$errors = [];
$success = false;
$name = '';
$email = '';
$subject = '';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $subject = sanitize($_POST['subject'] ?? '');
    $message = sanitize($_POST['message'] ?? '');
    
    // Validations
    if (empty($name)) {
        $errors[] = "Name is required.";
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "A valid email address is required.";
    }
    if (empty($subject)) {
        $errors[] = "Subject is required.";
    }
    if (empty($message) || strlen($message) < 10) {
        $errors[] = "Message is required and must be at least 10 characters long.";
    }
    
    if (empty($errors)) {
        $success = true;
        // In a full production system, we'd mail() this or insert it into a contacts table.
        // We'll reset the form fields on success
        $name = $email = $subject = $message = '';
    }
}
?>

<div class="row justify-content-center my-3">
    <div class="col-md-8 col-lg-6">
        <div class="card glass-card border-0 shadow-lg">
            <div class="card-body p-4 p-md-5">
                <div class="text-center mb-4">
                    <div class="contact-icon mb-3">
                        <i class="fa-regular fa-envelope gradient-text fs-1"></i>
                    </div>
                    <h2 class="font-outfit fw-bold text-light">Contact Support</h2>
                    <p class="text-secondary">Have a suggestion or found a bug? Send us a message.</p>
                </div>
                
                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fa-solid fa-circle-check me-2"></i>Your message has been received! We'll get back to you shortly.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <ul class="mb-0 ps-3">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <form action="contact.php" method="POST">
                    <!-- Full Name -->
                    <div class="mb-3">
                        <label for="name" class="form-label text-secondary-light small font-outfit">Your Name</label>
                        <input type="text" class="form-control bg-dark-deep border-dark-subtle text-light" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" placeholder="John Doe" required>
                    </div>
                    
                    <!-- Email Address -->
                    <div class="mb-3">
                        <label for="email" class="form-label text-secondary-light small font-outfit">Email Address</label>
                        <input type="email" class="form-control bg-dark-deep border-dark-subtle text-light" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" placeholder="you@example.com" required>
                    </div>
                    
                    <!-- Subject -->
                    <div class="mb-3">
                        <label for="subject" class="form-label text-secondary-light small font-outfit">Subject</label>
                        <input type="text" class="form-control bg-dark-deep border-dark-subtle text-light" id="subject" name="subject" value="<?php echo htmlspecialchars($subject); ?>" placeholder="e.g. Generation Error, Business Proposal" required>
                    </div>
                    
                    <!-- Message -->
                    <div class="mb-4">
                        <label for="message" class="form-label text-secondary-light small font-outfit">Your Message</label>
                        <textarea class="form-control bg-dark-deep border-dark-subtle text-light" id="message" name="message" rows="5" placeholder="Write your comments here..." required><?php echo htmlspecialchars($message); ?></textarea>
                    </div>
                    
                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-purple w-100 py-3 font-outfit fw-bold rounded-3">
                        Send Message <i class="fa-regular fa-paper-plane ms-2 small"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
