<?php
// reviews/updatereview.php

require_once '../security.php';
require_once '../db.php';

if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['user_id'])) { header('Location: ../login.php'); exit(); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $user_id = $_SESSION['user_id'];
    $review_id = $_POST['review_id']; // ID de la reseña a editar
    $film_id = $_POST['film_id'];     // ID de la película (para redirigir)
    
    $titulo = trim($_POST['titulo']);
    $puntuacion = (int) $_POST['puntuacion'];
    $texto = trim($_POST['texto']);

    if (!empty($titulo) && !empty($texto)) {
        // SQL: Actualizamos solo si el ID de la reseña y el Usuario coinciden (Seguridad)
        $sql = "UPDATE REVIEW SET TITLE = ?, TEXT = ?, RATING = ? WHERE ID = ? AND USER_ID = ?";
        $stmt = $pdo->prepare($sql);
        
        if ($stmt->execute([$titulo, $texto, $puntuacion, $review_id, $user_id])) {
            // ÉXITO: Volver a la película
            header("Location: ../film_page.php?id=$film_id");
            exit();
        } else {
            $_SESSION['error'] = "Error al actualizar la reseña.";
        }
    } else {
        $_SESSION['error'] = "Por favor, rellena todos los campos.";
    }

    // Si hay error, volvemos al formulario de edición
    header("Location: editreview.php?id=$review_id");
    exit();
} else {
    header('Location: ../home.php');
    exit();
}
?>