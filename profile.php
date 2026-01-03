<?php
// profile.php
require_once 'db.php';

if (session_status() === PHP_SESSION_NONE) { session_start(); }

// 1. Determine which profile to show
$logged_user_id = $_SESSION['user_id'] ?? null;
$profile_id = $_GET['id'] ?? $logged_user_id;

if (!$profile_id) {
    header('Location: login.php');
    exit();
}

// 2. Fetch User Data
$stmt = $pdo->prepare("SELECT * FROM USER WHERE ID = ?");
$stmt->execute([$profile_id]);
$user = $stmt->fetch();

if (!$user) {
    // Basic error handling if user ID doesn't exist
    header("HTTP/1.0 404 Not Found");
    die("User not found.");
}

// 3. Fetch Statistics
$stmtFilms = $pdo->prepare("SELECT COUNT(*) FROM USER_FILM WHERE USER_ID = ?");
$stmtFilms->execute([$profile_id]);
$numFilms = $stmtFilms->fetchColumn();

$stmtReviews = $pdo->prepare("SELECT COUNT(*) FROM REVIEW WHERE USER_ID = ?");
$stmtReviews->execute([$profile_id]);
$numReviews = $stmtReviews->fetchColumn();

// 4. Image Logic
// Null coalescing operator (??) handles cases where 'pfp' might be uppercase or lowercase in different DB versions
$dbImage = $user['pfp'] ?? $user['PFP'] ?? ''; 
$pfpPath = "img/Users/default_pfp.jpg"; // Default fallback

if (!empty($dbImage)) {
    $checkPath = "img/Users/" . $dbImage;
    if (file_exists($checkPath)) {
        $pfpPath = $checkPath;
    }
}

// 5. User Details
$username = $user['USERNAME'] ?? $user['NAME'] ?? 'User';
$email = $user['EMAIL'] ?? $user['email'] ?? '';

include 'header.php';
?>

<style>
    .profile-img {
        width: 150px;
        height: 150px;
        object-fit: cover;
        border: 3px solid #E50914;
        padding: 3px;
        box-shadow: 0 0 20px rgba(229, 9, 20, 0.3);
        background-color: #000;
        transition: transform 0.3s ease;
    }
    
    .profile-img:hover {
        transform: scale(1.02);
    }
    
    .stat-number {
        color: #ffffff;
        font-size: 3.5rem;
        font-weight: 800;
        text-shadow: 0 0 10px rgba(229, 9, 20, 0.4);
        line-height: 1;
    }

    .stat-label {
        color: #b3b3b3;
        font-size: 1.1rem;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    
    /* Responsive borders */
    .border-end-glass { border-right: 1px solid rgba(255, 255, 255, 0.1); }
    .border-start-glass { border-left: 1px solid rgba(255, 255, 255, 0.1); }

    @media (max-width: 768px) {
        .border-end-glass, .border-start-glass {
            border: none;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
    }
</style>

<div class="container d-flex justify-content-center pt-5 mt-4">
    <div class="glass-card fade-in-up w-100" style="max-width: 900px;"> 
        
        <div class="row align-items-center text-center py-4">

            <div class="col-md-3 border-end-glass">
                <div class="stat-number"><?php echo $numFilms; ?></div>
                <div class="stat-label mt-2">Films Watched</div>
            </div>

            <div class="col-md-6 py-3">
                <div class="mb-4">
                    <img src="<?php echo $pfpPath; ?>?t=<?php echo time(); ?>" 
                         alt="Profile Picture" 
                         class="rounded-circle profile-img">
                </div>

                <h2 class="fw-bold mb-1"><?php echo htmlspecialchars($username); ?></h2>
                <p class="text-white-50 mb-4"><?php echo htmlspecialchars($email); ?></p>

                <?php if ($logged_user_id == $user['ID']): ?>
                    <div class="d-flex justify-content-center gap-3 mt-3">
                        <a href="select_avatar.php" class="btn btn-outline-light px-4">
                            <i class="fas fa-images me-2"></i>Change Avatar
                        </a>
                        <a href="logout.php" class="btn btn-primary px-4">
                            Log Out
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <div class="col-md-3 border-start-glass">
                <div class="stat-number"><?php echo $numReviews; ?></div>
                <div class="stat-label mt-2">Reviews Written</div>
            </div>

        </div>
    </div>
</div>

<?php include 'footer.php'; ?>