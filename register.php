<?php
// register.php
// User registration page

$title = "Join MuseAI";
$description = "Create a MuseAI account to write, save, and export your AI-generated poems.";
require_once 'includes/header.php';

// Redirect if already logged in
if (is_logged_in()) {
    redirect('dashboard.php');
}

$errors = [];
$name = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validations
    if (empty($name)) {
        $errors[] = "Name is required.";
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "A valid email address is required.";
    }
    if (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long.";
    }
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }
    
    // Check if email already exists
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->num_rows > 0) {
            $errors[] = "An account with this email address already exists.";
        }
        $stmt->close();
    }
    
    // Register the user
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Start transaction to ensure user and preferences are created together
        $conn->begin_transaction();
        
        try {
            $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'user')");
            $stmt->bind_param("sss", $name, $email, $hashed_password);
            $stmt->execute();
            
            $user_id = $conn->insert_id;
            $stmt->close();
            
            // Create user preferences default row
            $stmt_pref = $conn->prepare("INSERT INTO user_preferences (user_id, favorite_theme, preferred_style, preferred_tone, preferred_length) VALUES (?, 'nature', 'romantic', 'calm', 'medium')");
            $stmt_pref->bind_param("i", $user_id);
            $stmt_pref->execute();
            $stmt_pref->close();
            
            $conn->commit();
            
            // Auto login user
            $_SESSION['user_id'] = $user_id;
            $_SESSION['user_name'] = $name;
            $_SESSION['user_role'] = 'user';
            
            flash('general_success', "Welcome to MuseAI! Your account has been created successfully.", 'success');
            redirect('dashboard.php');
            
        } catch (Exception $e) {
            $conn->rollback();
            $errors[] = "Something went wrong. Please try again. Error: " . $e->getMessage();
        }
    }
}
?>

<div class="row justify-content-center my-5">
    <div class="col-md-6 col-lg-5">
        <div class="card glass-card shadow-lg border-0">
            <div class="card-body p-4 p-md-5">
                <div class="text-center mb-4">
                    <div class="auth-icon mb-3">
                        <i class="fa-solid fa-feather-pointed gradient-text fs-1"></i>
                    </div>
                    <h2 class="font-outfit fw-bold text-light">Create Account</h2>
                    <p class="text-secondary">Join MuseAI and unleash your inner poet</p>
                </div>
                
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
                
                <form action="register.php" method="POST" class="needs-validation" novalidate>
                    <!-- Full Name -->
                    <div class="mb-3">
                        <label for="name" class="form-label text-secondary-light small font-outfit">Full Name</label>
                        <div class="input-group">
                            <span class="input-group-text bg-dark-deep border-end-0 border-dark-subtle text-secondary">
                                <i class="fa-regular fa-user"></i>
                            </span>
                            <input type="text" class="form-control bg-dark-deep border-start-0 border-dark-subtle text-light" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" placeholder="John Doe" required>
                        </div>
                    </div>
                    
                    <!-- Email Address -->
                    <div class="mb-3">
                        <label for="email" class="form-label text-secondary-light small font-outfit">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text bg-dark-deep border-end-0 border-dark-subtle text-secondary">
                                <i class="fa-regular fa-envelope"></i>
                            </span>
                            <input type="email" class="form-control bg-dark-deep border-start-0 border-dark-subtle text-light" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" placeholder="you@example.com" required>
                        </div>
                    </div>
                    
                    <!-- Password -->
                    <div class="mb-3">
                        <label for="password" class="form-label text-secondary-light small font-outfit">Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-dark-deep border-end-0 border-dark-subtle text-secondary">
                                <i class="fa-solid fa-lock"></i>
                            </span>
                            <input type="password" class="form-control bg-dark-deep border-start-0 border-dark-subtle text-light" id="password" name="password" placeholder="At least 6 characters" required>
                        </div>
                    </div>
                    
                    <!-- Confirm Password -->
                    <div class="mb-4">
                        <label for="confirm_password" class="form-label text-secondary-light small font-outfit">Confirm Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-dark-deep border-end-0 border-dark-subtle text-secondary">
                                <i class="fa-solid fa-circle-check"></i>
                            </span>
                            <input type="password" class="form-control bg-dark-deep border-start-0 border-dark-subtle text-light" id="confirm_password" name="confirm_password" placeholder="Re-enter password" required>
                        </div>
                    </div>
                    
                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-purple w-100 py-2 font-outfit fw-bold rounded-3 mb-3">
                        Get Started <i class="fa-solid fa-arrow-right ms-1 small"></i>
                    </button>
                    
                    <div class="text-center mt-3">
                        <span class="text-secondary small">Already have an account?</span>
                        <a href="login.php" class="text-cyan text-decoration-none small ms-1 fw-medium transition hover-underline">Login here</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
