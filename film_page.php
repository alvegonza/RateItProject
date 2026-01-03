<?php
require_once 'security.php';
require_once 'db.php';

// validate request
if (!isset($_GET['id'])) { header('Location: home.php'); exit(); }
$film_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// fetch film details
$stmt = $pdo->prepare("SELECT * FROM FILM WHERE ID = ?");
$stmt->execute([$film_id]);
$pelicula = $stmt->fetch();
if (!$pelicula) { header('Location: home.php'); exit(); }

// fetch reviews joined with user info
$sqlReviews = "SELECT r.*, u.USERNAME, u.ID as AUTHOR_ID, u.pfp, u.PFP 
               FROM REVIEW r 
               JOIN USER u ON r.USER_ID = u.ID 
               WHERE r.FILM_ID = ? 
               ORDER BY r.CREATED_AT DESC";
$stmtRev = $pdo->prepare($sqlReviews);
$stmtRev->execute([$film_id]);
$resenas = $stmtRev->fetchAll();

// calculate stats (average & check if user reviewed)
$sumaPuntos = 0;
$totalResenas = count($resenas);
$usuarioYaReseno = false;

foreach ($resenas as $r) {
    $sumaPuntos += $r['RATING'];
    if ($r['AUTHOR_ID'] == $user_id) $usuarioYaReseno = true;
}

$mediaRaw = ($totalResenas > 0) ? $sumaPuntos / $totalResenas : 0;
$mediaEstrellas = round($mediaRaw / 2, 1); // 0-5 scale

// handle poster image path
$nombreImagen = $pelicula['IMAGE'];
$rutaPeli = "img/Posters/" . $nombreImagen;
if (empty($nombreImagen) || !file_exists($rutaPeli)) { 
    $rutaPeli = "img/Posters/unknownFilm.png"; 
}   

include 'header.php';
?>

<link rel="stylesheet" href="css/filmpagestyle.css">

<div class="container py-5">
    <div class="row g-5"> 
        <div class="col-lg-8 d-flex flex-column gap-4">
            
            <div class="glass-card">
                <div class="d-flex justify-content-between align-items-start border-bottom border-secondary pb-3 mb-3">
                    <h1 class="display-5 film-title mb-0">
                        <?php echo htmlspecialchars($pelicula['NAME']); ?>
                    </h1>
                    <span class="badge bg-light text-dark fs-6 align-self-center">
                        <?php echo htmlspecialchars($pelicula['YEAR']); ?>
                    </span>
                </div>
                
                <h5 class="text-white-50 mb-3">
                    Directed by <span class="text-white"><?php echo htmlspecialchars($pelicula['DIRECTOR']); ?></span>
                </h5>

                <div class="row text-white-50 mb-4">
                    <div class="col-auto"><i class="far fa-clock me-1"></i> <?php echo htmlspecialchars($pelicula['DURATION']); ?> min</div>
                    <div class="col-auto"><i class="fas fa-users me-1"></i> Age +<?php echo htmlspecialchars($pelicula['RECOMMENDED_AGE']); ?></div>
                </div>

                <div class="p-3 rounded" style="background: rgba(0,0,0,0.2);">
                    <h6 class="label-subtitle">Synopsis</h6>
                    <p class="mb-0 text-light" style="line-height: 1.6;">
                        <?php echo htmlspecialchars($pelicula['DESCRIPTION']); ?>
                    </p>
                </div>
            </div>

            <div class="glass-card">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3 class="fw-bold m-0"><i class="fas fa-comments me-2 text-accent"></i>Reviews</h3>
                    <span class="badge bg-dark border border-secondary"><?php echo $totalResenas; ?> comments</span>
                </div>

                <div class="reviews-scrollor">
                    <?php if ($totalResenas > 0): ?>
                        <?php foreach ($resenas as $resena): ?>
                            <?php 
                                // avatar resolution logic
                                $carpetaBase = "img/Users/";
                                $fotoDB = $resena['pfp'] ?? $resena['PFP'] ?? ''; 
                                
                                if (!empty($fotoDB)) {
                                    $rutaAvatar = $carpetaBase . $fotoDB;
                                } elseif (file_exists($carpetaBase . "user_" . $resena['AUTHOR_ID'] . ".jpg")) {
                                    $rutaAvatar = $carpetaBase . "user_" . $resena['AUTHOR_ID'] . ".jpg";
                                } else {
                                    $rutaAvatar = $carpetaBase . "default_pfp.jpg";
                                }
                                
                                // rating logic
                                $puntosUsuario = $resena['RATING'] / 2;
                                $enterasUsuario = floor($puntosUsuario);
                                $mediaUsuario = ($puntosUsuario - $enterasUsuario) >= 0.5;

                                // format date
                                $fechaFormateada = date("d M Y", strtotime($resena['CREATED_AT']));
                            ?>
                            
                            <div class="review-item position-relative">
                                <?php if ($resena['AUTHOR_ID'] == $user_id): ?>
                                    <div class="position-absolute top-0 end-0 p-3">
                                        <a href="reviews/editreview.php?id=<?php echo $resena['ID']; ?>" class="text-white-50 hover-light me-2"><i class="fas fa-pen"></i></a>
                                        <a href="reviews/deletereview.php?id=<?php echo $resena['ID']; ?>&film_id=<?php echo $film_id; ?>" class="text-danger hover-light"><i class="fas fa-trash"></i></a>
                                    </div>
                                <?php endif; ?>

                                <div class="d-flex align-items-center mb-3">
                                    <img src="<?php echo $rutaAvatar; ?>?t=<?php echo time(); ?>" class="rounded-circle resena-avatar me-3" alt="Avatar">
                                    <div>
                                        <a href="perfil.php?id=<?php echo $resena['AUTHOR_ID']; ?>" class="text-white fw-bold text-decoration-none">
                                            <?php echo htmlspecialchars($resena['USERNAME']); ?>
                                        </a>
                                        <div class="stars-small mt-1 d-flex align-items-center">
                                            <span class="me-2 text-warning">
                                                <?php 
                                                    for($i=0; $i < $enterasUsuario; $i++) echo '<i class="fa-solid fa-star"></i>';
                                                    if ($mediaUsuario) echo '<i class="fa-solid fa-star-half-stroke"></i>';
                                                ?>
                                            </span>
                                            
                                            <span class="text-white-50 border-start border-secondary ps-2" style="font-size: 0.9rem;">
                                                <i class="far fa-calendar-alt me-1 text-white-50"></i>
                                                <?php echo $fechaFormateada; ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <h5 class="fw-bold text-white mb-2"><?php echo htmlspecialchars($resena['TITLE']); ?></h5>
                                <p class="text-white-50 mb-0"><?php echo nl2br(htmlspecialchars($resena['TEXT'])); ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-5 text-white-50">
                            <i class="far fa-comment-dots fa-3x mb-3"></i>
                            <p>No reviews yet. Be the first to share your opinion!</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="sticky-top" style="top: 100px; z-index: 1;">
                
                <div class="poster-container mb-4">
                    <img src="<?php echo $rutaPeli; ?>" 
                         alt="<?php echo htmlspecialchars($pelicula['NAME']); ?>" 
                         class="foto-derecha img-fluid">
                </div>

                <div class="glass-card text-center mb-3">
                    <h6 class="text-white-50 text-uppercase letter-spacing-2 mb-2">Global Rating</h6>
                    
                    <div class="stars-display fs-2 mb-2">
                        <?php 
                            $enteros = floor($mediaEstrellas);
                            for($i=0; $i < $enteros; $i++) echo '<i class="fa-solid fa-star"></i>';
                            if ($mediaEstrellas - $enteros >= 0.5) echo '<i class="fa-solid fa-star-half-stroke"></i>';
                            
                            $vacios = 5 - ceil($mediaEstrellas);
                            for($i=0; $i < $vacios; $i++) echo '<i class="fa-regular fa-star text-muted opacity-25"></i>';
                        ?>
                    </div>
                    <h2 class="fw-bold mb-0">
                        <?php echo $mediaEstrellas; ?>
                        <span class="fs-6 text-white-pure">/5</span>
                    </h2>                
                </div>

                <?php if (!$usuarioYaReseno): ?>
                    <a href="reviews/addreview.php?film_id=<?php echo $pelicula['ID']; ?>" 
                       class="btn btn-primary-gradient w-100 py-3 rounded-pill shadow-lg mb-3">
                       <i class="fas fa-plus-circle me-2"></i> Add Review
                    </a>
                <?php else: ?>
                    <div class="glass-card p-3 text-center mb-3">
                        <i class="fas fa-check-circle text-success mb-2 fs-4"></i>
                        <p class="mb-0 small text-white-50">You've already reviewed this film.</p>
                    </div>
                <?php endif; ?>

                <a href="home.php" class="btn btn-outline-light w-100 rounded-pill opacity-75">
                    <i class="fas fa-arrow-left me-2"></i> Back to Home
                </a>
            </div>
        </div>

    </div>
</div>

<?php include 'footer.php'; ?>