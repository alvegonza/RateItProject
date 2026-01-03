<?php

require_once 'security.php';
require_once 'db.php';

// check for form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // get form inputs
    $titulo = $_POST['titulo'];
    $director = $_POST['director'];
    $sinopsis = $_POST['sinopsis'];
    $duracion = $_POST['duracion'];
    $anio = $_POST['anio'];
    $edad = $_POST['edadRecomendada'];

    // insert new film data
    $sql = "INSERT INTO FILM (NAME, DIRECTOR, DURATION, RECOMMENDED_AGE, YEAR, DESCRIPTION) 
            VALUES (?, ?, ?, ?, ?, ?)";
            
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$titulo, $director, $duracion, $edad, $anio, $sinopsis]);
        
        // get the new film ID for linking and image naming
        $last_id = $pdo->lastInsertId();

        // link film to current user
        $user_id = $_SESSION['user_id'];
        $sqlUserLink = "INSERT INTO USER_FILM (USER_ID, FILM_ID) VALUES (?, ?)";
        $stmtLink = $pdo->prepare($sqlUserLink);
        $stmtLink->execute([$user_id, $last_id]);

        // handle image upload if exists
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === 0) {
            
            $carpeta = 'img/Posters/';
            if (!is_dir($carpeta)) mkdir($carpeta, 0777, true);

            // standard naming: film_ID.jpg to avoid conflicts
            $nombre_archivo = 'film_' . $last_id . '.jpg';
            $ruta_destino = $carpeta . $nombre_archivo;
            
            if (move_uploaded_file($_FILES['imagen']['tmp_name'], $ruta_destino)) {
                
                // update db record with the image filename
                $sqlUpdateImg = "UPDATE FILM SET IMAGE = ? WHERE ID = ?";
                $stmtImg = $pdo->prepare($sqlUpdateImg);
                $stmtImg->execute([$nombre_archivo, $last_id]);
            }
        }
        
        // success
        header("Location: home.php?status=created");
        exit();

    } catch (PDOException $e) {
        echo "Error saving film: " . $e->getMessage();
    }
}
?>