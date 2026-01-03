<?php
require_once 'security.php'; 
require_once 'db.php';       

if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit(); }

$user_id = $_SESSION['user_id'];
$searchQuery = isset($_GET['q']) ? trim($_GET['q']) : '';

// fetch films associated with the user, including review data if it exists
if ($searchQuery) {
    $sql = "SELECT f.*, r.RATING, r.CREATED_AT as FECHA_RESENA 
            FROM FILM f
            JOIN USER_FILM uf ON f.ID = uf.FILM_ID
            LEFT JOIN REVIEW r ON f.ID = r.FILM_ID AND r.USER_ID = uf.USER_ID
            WHERE uf.USER_ID = ? AND f.NAME LIKE ? 
            ORDER BY f.ID DESC";
    $params = [$user_id, "%$searchQuery%"];
} else {
    $sql = "SELECT f.*, r.RATING, r.CREATED_AT as FECHA_RESENA 
            FROM FILM f
            JOIN USER_FILM uf ON f.ID = uf.FILM_ID
            LEFT JOIN REVIEW r ON f.ID = r.FILM_ID AND r.USER_ID = uf.USER_ID
            WHERE uf.USER_ID = ? 
            ORDER BY f.ID DESC";
    $params = [$user_id];
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

include 'header.php'; 
?>

<style> main { padding-top: 0 !important; } </style>

<div class="container pb-5 pt-3">
    
    <div class="row align-items-center mb-4 mt-0 gy-3">
        <div class="col-md-6">
            <h1 class="text-white fw-bold mb-1 mt-0 display-5">My Films</h1>
            <p class="mb-0" style="color: var(--text-muted);">Your personal cinema diary</p>
        </div>
        <div class="col-md-6 col-lg-4 ms-auto">
            <form action="home.php" method="GET" class="search-container">
                <i class="fas fa-search search-icon-overlay"></i>
                <input type="text" name="q" class="search-input" placeholder="Search films..." value="<?php echo htmlspecialchars($searchQuery); ?>" autocomplete="off">
            </form>
        </div>
    </div>

    <div class="custom-grid">
        
        <div class="grid-item">
            <a href="search.php" class="add-film-card">
                <i class="fas fa-plus add-icon"></i>
                <span class="fw-bold">Add Film</span>
            </a>
        </div>

        <?php while ($pelicula = $stmt->fetch()): ?>
            <?php
                // handle image path
                $nombreImagen = $pelicula['IMAGE'];
                $rutaImagen = "img/Posters/" . $nombreImagen;
                if (empty($pelicula['IMAGE']) || !file_exists($rutaImagen)) { $rutaImagen = "img/Posters/unknownFilm.png"; }

                // check if film is already rated
                $isRated = !empty($pelicula['RATING']) && $pelicula['RATING'] > 0;

                // date display logic: use review date if rated, otherwise release year
                if (!empty($pelicula['FECHA_RESENA'])) {
                    $fechaTexto = date("M Y", strtotime($pelicula['FECHA_RESENA']));
                    $iconoFecha = "fa-calendar-check";
                } else {
                    $fechaTexto = $pelicula['YEAR'];
                    $iconoFecha = "fa-calendar";
                }
            ?>
            
            <div class="grid-item">
                <div class="film-card-container">
                    
                    <?php if ($isRated): ?>
                        <div class="badge-top-right">
                            <i class="fas fa-star text-warning" style="font-size: 0.75rem;"></i> 
                            <span class="ms-1"><?php echo $pelicula['RATING'] / 2; ?></span>
                        </div>
                    <?php endif; ?>

                    <?php if ($isRated): ?>
                        <div class="badge-top-left">
                            <i class="far <?php echo $iconoFecha; ?>"></i>
                            <span><?php echo $fechaTexto; ?></span>
                        </div>
                    <?php endif; ?>

                    <img src="<?php echo $rutaImagen; ?>" alt="<?php echo htmlspecialchars($pelicula['NAME']); ?>" class="film-poster">
                    
                    <a href="film_page.php?id=<?php echo $pelicula['ID']; ?>" class="card-click-layer" title="View details"></a>

                    <div class="card-overlay">
                        <div class="text-center px-2 mb-2">
                             <h6 class="fw-bold text-white mb-0 text-shadow"><?php echo htmlspecialchars($pelicula['NAME']); ?></h6>
                        </div>

                        <div class="overlay-buttons">
                            <?php if ($isRated): ?>
                                <a href="reviews/editreview.php?id=<?php echo $pelicula['ID']; ?>" 
                                   class="action-btn btn-edit" 
                                   title="Edit Review">
                                    <i class="fas fa-pen fa-sm"></i>
                                    <span>Edit</span>
                                </a>
                            <?php endif; ?>
                            
                            <a href="deletefilm.php?id=<?php echo $pelicula['ID']; ?>" 
                               class="action-btn btn-delete" 
                               title="Delete Film">
                                <i class="fas fa-trash fa-sm"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
    
    <?php if ($stmt->rowCount() == 0): ?>
        <div class="text-center py-5 mt-4">
            <p style="color: var(--text-muted);">
                <?php echo $searchQuery ? "No films found matching your search." : "No films found yet."; ?>
            </p>
        </div>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>