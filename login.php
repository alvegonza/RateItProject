<?php
// login.php
require_once 'db.php'; 

if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: home.php');
    exit();
}

$error = '';
$status_message = '';

// Check for registration success message
if (isset($_GET['status']) && $_GET['status'] == 'registered') {
    $status_message = '<div class="alert alert-success-glass d-flex align-items-center mb-4"><i class="fas fa-check-circle me-2"></i>Registration successful! Please log in.</div>';
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Please enter a username and password.';
    } else {
        try {
            // Fetch user data
            $sql = "SELECT ID, USERNAME, PASSWORD FROM USER WHERE USERNAME = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Verify password hash
            if ($user && password_verify($password, $user['PASSWORD'])) {
                $_SESSION['user_id'] = $user['ID'];
                $_SESSION['username'] = $user['USERNAME'];
                
                header('Location: home.php');
                exit();
            } else {
                $error = 'Incorrect username or password.';
            }
        } catch (PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            $error = 'A system error occurred. Please try again later.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log In | RateIt!</title>
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
            <h1 class="h2 mb-1 text-gradient-logo">Welcome Back</h1>
            <p class="text-white-50 small">Log in to your account</p>
        </div>

        <?php echo $status_message; ?>

        <?php if ($error): ?>
            <div class="alert alert-error d-flex align-items-center mb-4 p-3" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                <div><?php echo htmlspecialchars($error); ?></div>
            </div>
        <?php endif; ?>

        <form action="login.php" method="post"> 
            <div class="mb-3">
                <label for="nombre_usuario" class="form-label">Username</label>
                <div class="input-group">
                    <span class="input-group-text border-0 bg-transparent ps-0">
                        <i class="fas fa-user"></i>
                    </span>
                    <input type="text" id="nombre_usuario" name="username" 
                           class="form-control form-control-glass" 
                           required placeholder="Enter your username" 
                           autocomplete="username"
                           value="<?php echo htmlspecialchars($username ?? ''); ?>"/>
                </div>
            </div>

            <div class="mb-4">
                <label for="contrasena" class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text border-0 bg-transparent ps-0">
                        <i class="fas fa-lock"></i>
                    </span>
                    <input type="password" id="contrasena" name="password" 
                           class="form-control form-control-glass" 
                           required placeholder="••••••••"
                           autocomplete="current-password"/>
                </div>
            </div>

            <div class="d-grid gap-2">
                <button class="btn btn-brand" type="submit">Log In</button>
            </div>

            <div class="text-center border-top border-secondary border-opacity-25 pt-4 mt-4">
                <p class="text-white-50 mb-0 small">Don't have an account yet?</p>
                <a href="register.php" class="link-accent">Register here</a>
            </div>
        </form>
    </div>

</body> 
</html>