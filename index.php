<?php
// index.php
include 'header.php'; 

// Retrieve user_id after header has started the session
$user_id = $_SESSION['user_id'] ?? null;
?>

<link rel="stylesheet" href="css/indexStyle.css">

<main>
    
    <section class="hero-section text-center">
        <img src="img/index/pulpFictionBG.png" alt="Cinema Background" class="hero-bg-image">
        
        <div class="hero-overlay"></div>

        <div class="position-relative" style="z-index: 2; max-width: 1000px;">
            <h1 class="hero-title text-white">
                Your Personal
                <span class="text-gradient">Cinema Diary</span>
            </h1>
            
            <p class="hero-subtitle">
                Save, Rate, Share. Discover, rate, and organize all the movies you've watched. 
                Share your opinion and find your next great story.
            </p>

            <div class="d-flex flex-column flex-md-row justify-content-center gap-3 mt-4">
                <?php if ($user_id): ?>
                    <a href="home.php" class="btn-modern-primary">
                        <i class="fas fa-film me-2"></i> Go to My Films
                    </a>
                    <a href="logout.php" class="btn-modern-outline">
                        <i class="fas fa-sign-out-alt me-2"></i> Logout
                    </a>
                <?php else: ?>
                    <a href="register.php" class="btn-modern-primary">
                        <i class="fas fa-user-plus me-2"></i> Register
                    </a>
                    <a href="login.php" class="btn-modern-outline">
                        <i class="fas fa-sign-in-alt me-2"></i> Login
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <section class="section-container">
        
        <div class="feature-grid">
            <div class="glass-card">
                <div class="icon-box">
                    <i class="fas fa-star"></i>
                </div>
                <h4 class="card-title">Rate & Review</h4>
                <p class="card-desc">Give your honest ratings and write detailed reviews for every movie you watch.</p>
            </div>
            
            <div class="glass-card">
                <div class="icon-box">
                    <i class="fas fa-film"></i>
                </div>
                <h4 class="card-title">Track Journey</h4>
                <p class="card-desc">Build a visual diary of your movie-watching journey with beautiful posters.</p>
            </div>

            <div class="glass-card">
                <div class="icon-box">
                    <i class="fas fa-heart"></i>
                </div>
                <h4 class="card-title">Create Favorites</h4>
                <p class="card-desc">Curate your personal collection of must-watch films and favorites.</p>
            </div>

            <div class="glass-card">
                <div class="icon-box">
                    <i class="fas fa-chart-line"></i>
                </div>
                <h4 class="card-title">Discover Trends</h4>
                <p class="card-desc">Explore what others are watching and discover hidden gems.</p>
            </div>
        </div>
    </section>

</main>

<?php include 'footer.php'; ?>