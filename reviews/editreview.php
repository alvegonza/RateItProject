<?php
// reviews/editreview.php

require_once '../security.php';
require_once '../db.php';

if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['user_id'])) { header('Location: ../login.php'); exit(); }

$user_id = $_SESSION['user_id'];
$review_id = $_GET['id'] ?? null;

if (!$review_id) { header('Location: ../home.php'); exit(); }

// 1. OBTENER DATOS DE LA RESEÑA EXISTENTE
$stmt = $pdo->prepare("SELECT * FROM REVIEW WHERE ID = ? AND USER_ID = ?");
$stmt->execute([$review_id, $user_id]);
$resena = $stmt->fetch();

// Si no existe la reseña o no es tuya, fuera
if (!$resena) {
    header('Location: ../home.php');
    exit();
}

// Variables para pre-rellenar el formulario
$film_id = $resena['FILM_ID'];
$tituloActual = $resena['TITLE'];
$textoActual = $resena['TEXT'];
$puntuacionActual = $resena['RATING']; // Valor de 0 a 10

echo '<base href="../">'; 
include '../header.php';
?>

<style>
    /* Mismos estilos que en addreview.php */
    .star-rating-container {
        display: inline-flex;
        font-size: 2rem;
        cursor: pointer;
        color: #444; 
        position: relative;
    }
    
    .star-icon {
        padding: 0 2px;
        transition: color 0.1s, transform 0.1s;
    }

    .star-icon.full, .star-icon.half {
        color: #E50914; 
    }
    
    .hover-active {
        color: #E50914 !important;
    }

    .text-red-score {
        color: #E50914;
    }
</style>

<div class="container d-flex justify-content-center pt-5">
    <div class="glass-card fade-in-up"> 
        <h2 class="fw-bold text-center mb-4">Edit Review</h2>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <form action="reviews/editreviewlogic.php" method="POST" id="reviewForm">
            
            <input type="hidden" name="review_id" value="<?php echo $review_id; ?>">
            <input type="hidden" name="film_id" value="<?php echo $film_id; ?>">

            <div class="mb-4">
                <label for="titulo" class="form-label">Title</label>
                <input type="text" name="titulo" id="titulo" class="form-control form-control-glass" 
                       value="<?php echo htmlspecialchars($tituloActual); ?>" required>
            </div>

            <div class="mb-4 text-center">
                <label class="form-label d-block text-start">Rating</label>
                
                <div class="star-rating-container" id="starContainer">
                    <i class="far fa-star star-icon" data-index="0"></i>
                    <i class="far fa-star star-icon" data-index="1"></i>
                    <i class="far fa-star star-icon" data-index="2"></i>
                    <i class="far fa-star star-icon" data-index="3"></i>
                    <i class="far fa-star star-icon" data-index="4"></i>
                </div>

                <input type="hidden" name="puntuacion" id="puntuacionInput" value="<?php echo $puntuacionActual; ?>">
                
                <div class="mt-2 fw-bold" id="ratingText"></div>
            </div>

            <div class="mb-4">
                <label for="texto" class="form-label">Review</label>
                <textarea name="texto" id="texto" rows="5" class="form-control form-control-glass" required><?php echo htmlspecialchars($textoActual); ?></textarea>
            </div>

            <div class="d-flex justify-content-center gap-3 mt-4">
                <button type="submit" class="btn btn-primary px-4">Save Changes</button>
                <a href="../film_page.php?id=<?php echo $film_id; ?>" class="btn btn-outline-light px-4">Cancel</a>
            </div>

        </form>
    </div>
</div>

<?php include '../footer.php'; ?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const stars = document.querySelectorAll('.star-icon');
        const container = document.getElementById('starContainer');
        const input = document.getElementById('puntuacionInput');
        const textDisplay = document.getElementById('ratingText');
        
        // CAMBIO IMPORTANTE: Inicializamos con el valor que viene de la base de datos
        let currentRating = parseInt(input.value); 

        // Pintamos las estrellas nada más cargar la página
        renderStars(currentRating);

        function renderStars(value) {
            const starsCount = value / 2; 
            
            stars.forEach((star, index) => {
                star.className = 'star-icon far fa-star'; 
                
                if (starsCount >= index + 1) {
                    star.className = 'star-icon fas fa-star hover-active';
                } else if (starsCount > index && starsCount < index + 1) {
                    star.className = 'star-icon fas fa-star-half-stroke hover-active';
                }
            });

            if (value > 0) {
                textDisplay.innerText = (value / 2) + " stars";
                textDisplay.className = "mt-2 text-red-score fw-bold"; 
            } else {
                textDisplay.innerText = "Rate this film";
                textDisplay.className = "mt-2 text-white-50 fw-bold";
            }
        }

        container.addEventListener('mousemove', function(e) {
            const rect = container.getBoundingClientRect();
            const x = e.clientX - rect.left; 
            const width = rect.width;
            
            const numberOfStars = 5;
            const percent = x / width;
            let rawScore = percent * 10; 
            
            let score = Math.ceil(rawScore);
            if (score > 10) score = 10;
            if (score < 0) score = 0;

            renderStars(score);
        });

        container.addEventListener('mouseleave', function() {
            renderStars(currentRating); // Vuelve a la puntuación guardada
        });

        container.addEventListener('click', function(e) {
            const rect = container.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const width = rect.width;
            let score = Math.ceil((x / width) * 10);
            if (score > 10) score = 10;
            if (score < 0) score = 0;

            currentRating = score;
            input.value = currentRating; 
            
            container.style.transform = "scale(1.1)";
            setTimeout(() => container.style.transform = "scale(1)", 150);
        });
    });
</script>