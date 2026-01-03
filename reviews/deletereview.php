<?php
// reviews/deletereview.php

// 1. CORRECCIÓN DE RUTAS (Usamos ../ para ir atrás)
require_once '../security.php';
require_once '../db.php';

if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (!isset($_SESSION['user_id'])) { 
    // Redirigir a login (ruta relativa)
    header('Location: ../login.php'); 
    exit(); 
}

$user_id = $_SESSION['user_id'];
$review_id = $_GET['id'] ?? null;

if (!$review_id) { 
    header('Location: ../home.php'); 
    exit(); 
}

// Verificar propiedad de la reseña
$stmt = $pdo->prepare("SELECT * FROM REVIEW WHERE ID = ? AND USER_ID = ?");
$stmt->execute([$review_id, $user_id]);
$resena = $stmt->fetch();

if (!$resena) {
    die("Error: No se puede eliminar esta reseña o no tienes permiso.");
}
$film_id = $resena['FILM_ID']; 

// Procesar Borrado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $delSql = "DELETE FROM REVIEW WHERE ID = ?";
    $delStmt = $pdo->prepare($delSql);
    if ($delStmt->execute([$review_id])) {
        // Redirigir a la página de la película (ruta relativa)
        header("Location: ../film_page.php?id=$film_id");
        exit();
    }
}

// Incluir header desde la carpeta padre
include '../header.php';
?>

<div class="container d-flex align-items-center justify-content-center py-5" style="min-height: 60vh;">
    <div class="glass-card text-center fade-in-up" style="max-width: 500px;">
        
        <i class="fas fa-trash-alt text-danger mb-4" style="font-size: 3rem;"></i>
        
        <h2 class="fw-bold mb-3">Delete Review?</h2>
        <p class="text-white-50 mb-4">Are you sure you want to delete this review? This action cannot be undone.</p>

        <form action="" method="POST">
            <div class="d-flex justify-content-center gap-3">
                <button type="submit" class="btn btn-danger px-4 fw-bold">Yes, Delete</button>
                <a href="../film_page.php?id=<?php echo $film_id; ?>" class="btn btn-secondary px-4">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include '../footer.php'; ?>