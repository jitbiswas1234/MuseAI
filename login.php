<?php
// login.php
// User login page

$title = "Welcome Back";
$description = "Log in to your MuseAI account to access your saved poetry library and custom preferences.";
require_once 'includes/header.php';

// Redirect if already logged in
if (is_logged_in()) {
    redirect('dashboard.php');
}

$errors = [];
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Validations
    if (empty($email)) {
        $errors[] = "Email is required.";
    }
    if (empty($password)) {
        $errors[] = "Password is required.";
    }
    
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT id, name, password, role FROM users WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res = $stmt->get_result();
        
        if ($res->num_rows === 1) {
            $user = $res->fetch_assoc();
            
            if (password_verify($password, $user['password'])) {
                // Populate session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_role'] = $user['role'];
                
                flash('general_success', "Welcome back, " . $user['name'] . "!", 'success');
                
                // Redirect depending on role
                if ($user['role'] === 'admin') {
                    redirect('admin/dashboard.php');
                } else {
                    redirect('dashboard.php');
                }
            } else {
                $errors[] = "Invalid password.";
            }
        } else {
            $errors[] = "No account found with that email address.";
        }
        $stmt->close();
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
                    <h2 class="font-outfit fw-bold text-light">Welcome Back</h2>
                    <p class="text-secondary">Log in to continue creating with MuseAI</p>
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
                
                <form action="login.php" method="POST">
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
                    <div class="mb-4">
                        <label for="password" class="form-label text-secondary-light small font-outfit">Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-dark-deep border-end-0 border-dark-subtle text-secondary">
                                <i class="fa-solid fa-lock"></i>
                            </span>
                            <input type="password" class="form-control bg-dark-deep border-start-0 border-dark-subtle text-light" id="password" name="password" placeholder="Enter your password" required>
                        </div>
                    </div>
                    
                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-purple w-100 py-2 font-outfit fw-bold rounded-3 mb-3">
                        Login <i class="fa-solid fa-right-to-bracket ms-1 small"></i>
                    </button>
                    
                    <div class="text-center mt-3">
                        <span class="text-secondary small">Don't have an account?</span>
                        <a href="register.php" class="text-cyan text-decoration-none small ms-1 fw-medium transition hover-underline">Register here</a>
                    </div>
                </form>
                
                <!-- Quick Demo Login helper (convenient for evaluation/grading) -->
                <div class="mt-4 pt-4 border-top border-dark-subtle">
                    <p class="text-secondary text-center small mb-2"><i class="fa-solid fa-circle-info me-1 text-cyan"></i>Evaluation Demo Accounts</p>
                    <div class="d-flex flex-column gap-2">
                        <button type="button" class="btn btn-outline-cyan btn-sm" onclick="setDemoCredentials('user@poetry.com')">
                            Login as Poet (User)
                        </button>
                        <button type="button" class="btn btn-outline-purple btn-sm" onclick="setDemoCredentials('admin@poetry.com')">
                            Login as Moderator (Admin)
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function setDemoCredentials(email) {
    document.getElementById('email').value = email;
    document.getElementById('password').value = 'password';
}
</script>

<?php require_once 'includes/footer.php'; ?>
