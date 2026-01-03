<?php 
// security check
require_once 'security.php';

// global header
include 'header.php'; 
?>

<main class="container d-flex align-items-center justify-content-center mt-5">
    <div class="card p-4 shadow-lg text-light bg-secondary" style="width: 100%; max-width: 600px;">
        <h1 class="fw-bold mb-0 fs-2 text-center mb-4">Add a New Film</h1>

        <form action="savefilmdb.php" method="POST" enctype="multipart/form-data">
            
            <div class="mb-3">
                <label for="titulo" class="form-label">Title:</label>
                <input type="text" name="titulo" id="titulo" class="form-control" required placeholder="Enter film title">
            </div>

            <div class="mb-3">
                <label for="director" class="form-label">Director:</label>
                <input type="text" name="director" id="director" class="form-control" required placeholder="Director's name">
            </div>

            <div class="mb-3">
                <label for="sinopsis" class="form-label">Synopsis:</label>
                <textarea name="sinopsis" id="sinopsis" class="form-control" rows="3" required placeholder="Short summary of the plot..."></textarea>
            </div>

            <div class="row">
                <div class="col-6 mb-3">
                    <label for="duracion" class="form-label">Duration (min):</label>
                    <input type="number" name="duracion" id="duracion" class="form-control" required min="1">
                </div>
                <div class="col-6 mb-3">
                    <label for="anio" class="form-label">Year:</label>
                    <input type="number" name="anio" id="anio" class="form-control" required min="1888" max="2099">
                </div>
            </div>

            <div class="mb-3">
                <label for="edadRecomendada" class="form-label">Recommended Age:</label>
                <select name="edadRecomendada" id="edadRecomendada" class="form-select">
                    <option value="1">All ages / General Audience</option>
                    <option value="7">+7 years</option>
                    <option value="12">+12 years</option>
                    <option value="16">+16 years</option>
                    <option value="18">+18 years</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="imagen" class="form-label">Image (Poster):</label>
                <input type="file" name="imagen" id="imagen" class="form-control" accept="image/*" required>
            </div>

            <div class="d-flex flex-row justify-content-center align-items-center gap-2 mt-4">
                <button type="submit" class="btn btn-primary">Add Film</button>
                <a href="index.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</main>

<?php 
include 'footer.php'; 
?>