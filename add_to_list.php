<?php

require_once 'security.php'; 
require_once 'db.php';

// check if we actually got an id
if (isset($_GET['id'])) {
    $film_id = $_GET['id'];
    $user_id = $_SESSION['user_id'];

    try {
        // avoid duplicates: check if user already has this film
        $checkSql = "SELECT * FROM USER_FILM WHERE USER_ID = ? AND FILM_ID = ?";
        $stmtCheck = $pdo->prepare($checkSql);
        $stmtCheck->execute([$user_id, $film_id]);

        if ($stmtCheck->rowCount() == 0) {
            // not found, add to list
            $insertSql = "INSERT INTO USER_FILM (USER_ID, FILM_ID) VALUES (?, ?)";
            $stmtInsert = $pdo->prepare($insertSql);
            $stmtInsert->execute([$user_id, $film_id]);
        }

        // done, go back to home
        header('Location: home.php?status=added');
        exit();

    } catch (PDOException $e) {
        header('Location: home.php?error=db');
        exit();
    }
} else {
    // missing params, send back to search
    header('Location: search.php');
    exit();
}
?>