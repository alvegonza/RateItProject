<?php
// register.php
require_once 'db.php'; 

if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: home.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirmarPassword'] ?? ''; 
    
    // Validation
    if (empty($username) || empty($email) || empty($password) || empty($confirmPassword)) {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format.';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match.';
    } else {
        try {
            // Hash Password
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            
            // Default Avatar Filename
            $defaultAvatar = 'default_pfp.jpg';

            // Insert User (Including default pfp directly)
            // Note: Ensure your DB column is named 'pfp' or 'PFP' matches this query
            $sql = "INSERT INTO USER (USERNAME, EMAIL, PASSWORD, pfp) VALUES (?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$username, $email, $passwordHash, $defaultAvatar]);
            
            // Redirect to Login
            header('Location: login.php?status=registered');
            exit();
            
        } catch (PDOException $e) {
            // Error Code 23000 usually means Duplicate Entry (unique constraint violation)
            if ($e->getCode() == 23000) {
                $error = 'Username or Email already exists.';
            } else {
                error_log("Registration Error: " . $e->getMessage()); // Log internal error
                $error = 'System error. Please try again later.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Create Account | RateIt!</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/authstyle.css">
</head>
<body>

    <div class="blob-container">
        <div class="blob blob-1"></div>
        <div class="blob blob-2"></div>
    </div>

    <div class="glass-card">
        <div class="text-center mb-4">
            <h1 class="h2 mb-1 text-gradient-logo">Create Account</h1>
            <p class="text-white-50 small">Join your personal cinema diary</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error d-flex align-items-center mb-4 p-3" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                <div><?php echo htmlspecialchars($error); ?></div>
            </div>
        <?php endif; ?>

        <form method="POST" action="register.php"> 
            
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <div class="input-group">
                    <span class="input-group-text border-0 bg-transparent ps-0">
                        <i class="fas fa-user"></i>
                    </span>
                    <input type="text" id="username" name="username" 
                           class="form-control form-control-glass" 
                           required placeholder="Your username" 
                           autocomplete="username"
                           value="<?php echo htmlspecialchars($username ?? ''); ?>"/>
                </div>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email Address</label>
                <div class="input-group">
                    <span class="input-group-text border-0 bg-transparent ps-0">
                        <i class="fas fa-envelope"></i>
                    </span>
                    <input type="email" id="email" name="email" 
                           class="form-control form-control-glass" 
                           required placeholder="name@example.com" 
                           autocomplete="email"
                           value="<?php echo htmlspecialchars($email ?? ''); ?>"/>
                </div>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text border-0 bg-transparent ps-0">
                        <i class="fas fa-lock"></i>
                    </span>
                    <input type="password" id="password" name="password" 
                           class="form-control form-control-glass" 
                           required placeholder="••••••••"
                           autocomplete="new-password"/>
                </div>
            </div>

            <div class="mb-4">
                <label for="confirmarPassword" class="form-label">Confirm Password</label>
                <div class="input-group">
                    <span class="input-group-text border-0 bg-transparent ps-0">
                        <i class="fas fa-check-circle"></i>
                    </span>
                    <input type="password" id="confirmarPassword" name="confirmarPassword" 
                           class="form-control form-control-glass" 
                           required placeholder="••••••••"
                           autocomplete="new-password"/>
                </div>
            </div>

            <div class="d-grid gap-2 mb-4">
                <button type="submit" class="btn btn-brand">Sign Up</button>
            </div>

            <div class="text-center border-top border-secondary border-opacity-25 pt-4">
                <p class="text-white-50 mb-0 small">Already have an account?</p>
                <a href="login.php" class="link-accent">Log in here</a>
            </div>
            
        </form>
    </div>

</body>
</html>