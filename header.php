<?php
// header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// determine base path for assets (css/img) depending on current directory
$base = '';
if (!file_exists('css') && file_exists('../css')) {
    $base = '../';
}

// ensure db connection
if (!isset($pdo)) {
    $dbPath = file_exists('db.php') ? 'db.php' : $base . 'db.php';
    if (file_exists($dbPath)) {
        require_once $dbPath;
    }
}

// default avatar
$headerPfp = $base . "img/Users/default_pfp.jpg";

// fetch specific user avatar if logged in
if (isset($_SESSION['user_id']) && isset($pdo)) {
    $stmtHeader = $pdo->prepare("SELECT pfp FROM USER WHERE ID = ?");
    $stmtHeader->execute([$_SESSION['user_id']]);
    $userHeader = $stmtHeader->fetch();

    if ($userHeader && !empty($userHeader['pfp'])) {
        $dbImage = $userHeader['pfp'];
        
        // verify file actually exists on server before serving
        $checkPath = ($base == '../' ? '../' : '') . "img/Users/" . $dbImage;

        if (file_exists($checkPath)) {
            $headerPfp = $base . "img/Users/" . $dbImage;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RateIt!</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet"/>
    
    <link rel="stylesheet" href="<?php echo $base; ?>css/generalstyle.css">
    <link rel="stylesheet" href="<?php echo $base; ?>css/homestyle.css">
</head>
<body class="d-flex flex-column min-vh-100" style="background-color: #000000; color: white;">

    <div class="blob-container">
        <div class="blob blob-1"></div>
        <div class="blob blob-2"></div>
    </div>

    <header class="sticky-top" style="background-color: rgba(0, 0, 0, 0.3); backdrop-filter: blur(12px); border-bottom: 1px solid rgba(255, 255, 255, 0.1);">
        <div class="container-fluid px-4 py-3">
            <div class="d-flex flex-wrap align-items-center justify-content-between">
                
                <a href="<?php echo $base; ?>home.php" class="d-flex align-items-center text-decoration-none">
                    <img src="<?php echo $base; ?>img/RATEIT.png" alt="RateIt Logo" class="me-2" style="width: 40px; height: 40px; object-fit: contain;">
                    <span class="fs-3 text-gradient-logo">RateIt!</span>
                </a>

                <div class="d-flex align-items-center gap-3">
                    <a href="<?php echo $base; ?>profile.php" class="d-flex align-items-center justify-content-center text-white text-decoration-none rounded-circle position-relative" 
                       style="width: 40px; height: 40px; background-color: rgba(255, 255, 255, 0.1); overflow: hidden;">
                        
                        <img src="<?php echo $headerPfp; ?>" alt="Profile" style="width: 100%; height: 100%; object-fit: cover;">
                        
                    </a>
                    
                    <button class="d-md-none btn p-0 d-flex align-items-center justify-content-center rounded-circle text-white" 
                            style="width: 40px; height: 40px; background-color: rgba(255, 255, 255, 0.1);">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>
            </div>
        </div>
    </header>