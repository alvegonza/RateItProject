<?php

require_once 'db.php'; 
require_once 'security.php'; 

// 1. Check method and user session
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Redirect if accessed directly without POST data
    header('Location: addfilm.php');
    exit();
}

// CRITICAL: Get the logged-in user ID from the session
$user_id = $_SESSION['user_id'] ?? null; 

if (!$user_id) {
    // If no USER_ID, redirect to login
    header('Location: login.php'); 
    exit();
}

// 2. Retrieve and sanitize form data
$titulo = trim($_POST['titulo'] ?? '');
$director = trim($_POST['director'] ?? '');
$sinopsis = trim($_POST['sinopsis'] ?? '');
$duracion = (int)($_POST['duracion'] ?? 0);
$anio = (int)($_POST['anio'] ?? 0);
$edadRecomendada = (int)($_POST['edadRecomendada'] ?? 1);

// 3. Basic validation
if (empty($titulo) || empty($director) || $anio <= 0) {
    header('Location: addfilm.php?error=validation');
    exit();
}

try {
    
    // 4. STEP 1: INSERT into the FILM table (Create the film record)
    // Using column names from FILM.sql: NAME, DIRECTOR, DURATION, RECOMMENDED_AGE, YEAR, DESCRIPTION
    $sql_film = "INSERT INTO FILM 
                 (NAME, DIRECTOR, DURATION, RECOMMENDED_AGE, YEAR, DESCRIPTION) 
                 VALUES (?, ?, ?, ?, ?, ?)";
    
    $stmt_film = $pdo->prepare($sql_film);
    $stmt_film->execute([
        $titulo, 
        $director, 
        $duracion, 
        $edadRecomendada, 
        $anio, 
        $sinopsis // Mapped to DESCRIPTION column
    ]);
    
    // Get the ID of the new film (CRUCIAL!)
    $new_film_id = $pdo->lastInsertId();

    // 5. Image Upload and DB Update
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $file_tmp_path = $_FILES['imagen']['tmp_name'];
        
        // Target directory and filename (Consistent format: film_[ID].jpg)
        $target_dir = "img/Posters/"; 
        $image_filename = "film_" . $new_film_id . ".jpg";
        $target_file = $target_dir . $image_filename;
        
        // Ensure the folder exists
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        if (move_uploaded_file($file_tmp_path, $target_file)) {
            
            // CRITICAL STEP: Update the FILM table to store the filename
            $sql_update_image = "UPDATE FILM SET IMAGE = ? WHERE ID = ?";
            $stmt_update_image = $pdo->prepare($sql_update_image);
            $stmt_update_image->execute([$image_filename, $new_film_id]);
            
        } else {
            error_log("Error moving uploaded file for film ID: " . $new_film_id);
        }
    }
    
    // 6. STEP 2: INSERT into the USER_FILM table (Link the film to the user)
    $sql_user_film = "INSERT INTO USER_FILM (USER_ID, FILM_ID) VALUES (?, ?)";
    $stmt_user_film = $pdo->prepare($sql_user_film);
    $stmt_user_film->execute([$user_id, $new_film_id]);
    
    // 7. Success and Redirect to the user's home page
    header('Location: home.php?status=film_created');
    exit();
    
} catch (PDOException $e) {
    // 8. Handle database errors
    error_log("PDO Error in savefilm.php: " . $e->getMessage());
    header('Location: addfilm.php?error=db_error');
    exit();
}
?>