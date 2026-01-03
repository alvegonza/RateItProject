<?php
require_once 'db.php';

if (session_status() === PHP_SESSION_NONE) { session_start(); }

// security check
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';

// whitelist allowed avatars
$allowed_avatars = [
    'default_pfp.jpg',
    'pfp1.jpg',
    'pfp2.jpg',
    'pfp3.jpg',
    'pfp4.jpg',
    'pfp5.jpg'
];

// handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['selected_avatar'])) {
    $selection = $_POST['selected_avatar'];

    if (in_array($selection, $allowed_avatars)) {
        try {
            $stmt = $pdo->prepare("UPDATE USER SET pfp = ? WHERE ID = ?");
            $stmt->execute([$selection, $user_id]);
            
            header('Location: profile.php?status=avatar_updated');
            exit();
        } catch (PDOException $e) {
            $message = "Database error.";
        }
    } else {
        $message = "Invalid selection.";
    }
}

// fetch current avatar for UI state
$stmt = $pdo->prepare("SELECT pfp FROM USER WHERE ID = ?");
$stmt->execute([$user_id]);
$current_pfp = $stmt->fetchColumn();

if (!$current_pfp) { $current_pfp = 'default_pfp.jpg'; }

include 'header.php'; 
?>

<style>
    /* Radio button image replacement */
    .avatar-option input[type="radio"] {
        display: none;
    }

    .avatar-option img {
        width: 120px;
        height: 120px;
        object-fit: cover;
        border-radius: 50%;
        cursor: pointer;
        border: 4px solid transparent;
        transition: all 0.3s ease;
        opacity: 0.7;
    }

    .avatar-option img:hover {
        opacity: 1;
        transform: scale(1.05);
    }

    /* Selected state */
    .avatar-option input[type="radio"]:checked + img {
        border-color: #E50914;
        opacity: 1;
        box-shadow: 0 0 15px rgba(229, 9, 20, 0.6);
        transform: scale(1.1);
    }
</style>

<div class="container py-5 mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card bg-dark text-white shadow-lg border-secondary">
                <div class="card-header border-bottom border-secondary pt-4 pb-3">
                    <h2 class="text-center fw-bold">Choose Your Avatar</h2>
                    <p class="text-center text-white-50 mb-0">Select one of our predefined icons</p>
                </div>
                
                <div class="card-body p-5">
                    
                    <?php if ($message): ?>
                        <div class="alert alert-danger"><?php echo $message; ?></div>
                    <?php endif; ?>

                    <form method="POST" action="select_avatar.php">
                        
                        <div class="row g-4 justify-content-center mb-5">
                            <?php foreach ($allowed_avatars as $imgName): ?>
                                <div class="col-6 col-md-4 text-center">
                                    <label class="avatar-option">
                                        <input type="radio" name="selected_avatar" value="<?php echo $imgName; ?>" 
                                            <?php echo ($current_pfp === $imgName) ? 'checked' : ''; ?>>
                                        
                                        <img src="img/Users/<?php echo $imgName; ?>" alt="Avatar Option">
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="profile.php" class="btn btn-outline-light px-4">Cancel</a>
                            <button type="submit" class="btn btn-danger px-4 fw-bold">Save Changes</button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>