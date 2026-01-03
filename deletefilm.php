<?php
// eliminar.php

// 1. Seguridad y Conexión
require_once 'security.php';
require_once 'db.php';

if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// 2. Verificar que recibimos un ID
if (isset($_GET['id'])) {
    $film_id = $_GET['id'];
    $user_id = $_SESSION['user_id'];

    try {
        // INICIO DE TRANSACCIÓN (Para asegurar que se borra todo o nada)
        $pdo->beginTransaction();

        // PASO A: Borrar la asociación en USER_FILM (Quitar de la lista)
        $sqlList = "DELETE FROM USER_FILM WHERE USER_ID = ? AND FILM_ID = ?";
        $stmtList = $pdo->prepare($sqlList);
        $stmtList->execute([$user_id, $film_id]);

        // PASO B: (Opcional) Borrar también la reseña asociada si existe
        // Es buena práctica: si la quitas de tu lista, tu reseña debería irse también.
        $sqlReview = "DELETE FROM REVIEW WHERE USER_ID = ? AND FILM_ID = ?";
        $stmtReview = $pdo->prepare($sqlReview);
        $stmtReview->execute([$user_id, $film_id]);

        // CONFIRMAR CAMBIOS
        $pdo->commit();

    } catch (Exception $e) {
        // Si hay error, deshacer cambios
        $pdo->rollBack();
        // Opcional: registrar el error
        // error_log($e->getMessage());
    }
}

// 3. Redirigir de vuelta a home.php
header('Location: home.php');
exit();
?>