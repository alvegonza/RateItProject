<?php
// search.php
require_once 'db.php';

// 1. Session Check
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit(); }

$currentUserId = $_SESSION['user_id'];

// --- CONFIGURATION ---
$apiKey = 'a371d353396b679bf29ba7c742d32bd2'; // Consider moving to a config file in production
$imageBaseUrl = "https://image.tmdb.org/t/p/w500";

// --- HELPER FUNCTION: ROBUST CURL ---
function callTmdb($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    // Disable SSL verification for local dev (XAMPP/WAMP). Enable in production!
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if (curl_errno($ch)) { return null; }
    curl_close($ch);

    if ($httpCode != 200) { return null; }

    return json_decode($response, true);
}

// --- IMPORT & LINK LOGIC (Runs when a movie is clicked) ---
if (isset($_GET['tmdb_id'])) {
    $tmdbId = $_GET['tmdb_id'];
    
    // 1. Fetch details from TMDB
    $url = "https://api.themoviedb.org/3/movie/$tmdbId?api_key=$apiKey&append_to_response=credits,release_dates";
    $data = callTmdb($url);
    
    if ($data) {
        $title = $data['title'];
        $finalFilmId = 0; 

        // 2. Check if film exists in global DB (by Name)
        // Note: Ideally, check by a TMDB_ID column if you add one to your DB schema later.
        $stmtCheck = $pdo->prepare("SELECT ID FROM FILM WHERE NAME = ? LIMIT 1");
        $stmtCheck->execute([$title]);
        $existingFilm = $stmtCheck->fetch();

        if ($existingFilm) {
            $finalFilmId = $existingFilm['ID'];
        } else {
            // 3. New Film - Parse Data
            $desc = $data['overview'];
            $year = substr($data['release_date'] ?? '', 0, 4);
            $duration = $data['runtime'] ?? 0;
            
            // Director
            $director = "Unknown";
            if (isset($data['credits']['crew'])) {
                foreach ($data['credits']['crew'] as $crew) {
                    if ($crew['job'] === 'Director') {
                        $director = $crew['name'];
                        break;
                    }
                }
            }

            // Age Rating (US Certification)
            $age = 0;
            if (isset($data['release_dates']['results'])) {
                foreach ($data['release_dates']['results'] as $res) {
                    if ($res['iso_3166_1'] === 'US') {
                        foreach ($res['release_dates'] as $rd) {
                            if (!empty($rd['certification'])) {
                                $c = $rd['certification'];
                                if ($c == 'R') $age = 18;
                                elseif ($c == 'PG-13') $age = 13;
                                elseif ($c == 'PG') $age = 7;
                                break 2;
                            }
                        }
                    }
                }
            }

            // Image Download
            $posterDir = "img/Posters/";
            // Ensure directory exists
            if (!is_dir($posterDir)) { mkdir($posterDir, 0777, true); }

            $localImgName = "tmdb_" . $tmdbId . ".jpg";
            $posterUrl = $data['poster_path'] ? $imageBaseUrl . $data['poster_path'] : null;
            
            if ($posterUrl) {
                $imgContent = file_get_contents($posterUrl, false, stream_context_create([
                    "ssl" => ["verify_peer" => false, "verify_peer_name" => false]
                ]));
                if ($imgContent) {
                    file_put_contents($posterDir . $localImgName, $imgContent);
                } else {
                    $localImgName = "unknownFilm.png";
                }
            } else {
                $localImgName = "unknownFilm.png";
            }

            // Insert into FILM
            try {
                $stmt = $pdo->prepare("INSERT INTO FILM (NAME, DESCRIPTION, IMAGE, DIRECTOR, YEAR, DURATION, RECOMMENDED_AGE) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$title, $desc, $localImgName, $director, $year, $duration, $age]);
                $finalFilmId = $pdo->lastInsertId();
            } catch (Exception $e) {
                // Log error silently or handle gracefully
                error_log("Error inserting film: " . $e->getMessage());
            }
        }

        // 4. Link to User
        if ($finalFilmId > 0) {
            $stmtLinkCheck = $pdo->prepare("SELECT * FROM USER_FILM WHERE USER_ID = ? AND FILM_ID = ?");
            $stmtLinkCheck->execute([$currentUserId, $finalFilmId]);
            
            if (!$stmtLinkCheck->fetch()) {
                $stmtLink = $pdo->prepare("INSERT INTO USER_FILM (USER_ID, FILM_ID) VALUES (?, ?)");
                $stmtLink->execute([$currentUserId, $finalFilmId]);
            }

            header("Location: home.php");
            exit();
        }
    }
}

// --- SEARCH LOGIC ---
$searchTerm = "";
$results = [];

if (isset($_GET['q']) && !empty($_GET['q'])) {
    $searchTerm = trim($_GET['q']);
    $queryEncoded = urlencode($searchTerm);
    $urlSearch = "https://api.themoviedb.org/3/search/movie?api_key=$apiKey&query=$queryEncoded";
    $dataSearch = callTmdb($urlSearch);
    if ($dataSearch && isset($dataSearch['results'])) {
        $results = $dataSearch['results'];
    }
}

// Include Header AFTER logic (to allow redirects)
include 'header.php';
?>

<link rel="stylesheet" href="css/searchstyle.css">

<div class="container d-flex flex-column align-items-center justify-content-center py-5">
    
    <div class="glass-search-container fade-in-up">
        
        <div class="text-center mb-5">
            <h1 class="fw-bold display-5 text-white mb-2">Explore Movies</h1>
            <p class="text-white-50">Search the global database to add to your diary</p>
        </div>

        <form action="search.php" method="GET" class="search-wrapper mb-5">
            <div class="position-relative">
                <input type="text" 
                       name="q" 
                       value="<?php echo htmlspecialchars($searchTerm); ?>" 
                       class="form-control custom-search-input rounded-pill ps-4" 
                       placeholder="Type a movie name (e.g. Interstellar)..." 
                       autocomplete="off">
                <button type="submit" class="search-btn-icon">
                    <i class="fas fa-search fa-lg"></i>
                </button>
            </div>
        </form>

        <div class="row g-4 justify-content-center">
            <?php if (!empty($searchTerm) && count($results) > 0): ?>
                <?php foreach ($results as $film): ?>
                    <?php 
                        $posterSrc = $film['poster_path'] ? $imageBaseUrl . $film['poster_path'] : "img/Posters/unknownFilm.png";
                        $year = substr($film['release_date'] ?? '', 0, 4);
                    ?>
                
                    <div class="col-6 col-md-4 col-lg-3">
                        <a href="search.php?tmdb_id=<?php echo $film['id']; ?>" 
                           class="movie-card-link"
                           onclick="this.style.opacity='0.5'; this.innerHTML='<div class=\'text-white text-center mt-5\'><i class=\'fas fa-spinner fa-spin fa-2x\'></i><br>Adding...</div>';"
                           title="Add to my Diary">
                           
                            <div class="poster-wrapper">
                                <div class="position-absolute top-0 end-0 m-2">
                                  <span class="badge badge-add shadow-sm py-2 px-3">
                                        <i class="fas fa-plus me-1"></i> ADD
                                    </span>
                                </div>
                                <img src="<?php echo $posterSrc; ?>" class="poster-img" alt="<?php echo htmlspecialchars($film['title']); ?>">
                            </div>
                            
                            <p class="film-title-text text-truncate mt-2 mb-0">
                                <?php echo htmlspecialchars($film['title']); ?>
                            </p>
                            <span class="text-white-50 small"><?php echo $year; ?></span>
                        </a>
                    </div>
                <?php endforeach; ?>

            <?php elseif (!empty($searchTerm)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-search-minus fa-3x text-white-50 mb-3"></i>
                    <h4 class="text-white">No results found</h4>
                    <p class="text-white-50">Try checking the spelling or searching for another title.</p>
                </div>
            <?php else: ?>
                <div class="text-center py-4">
                    <i class="fas fa-globe-americas fa-3x text-white-50 mb-3 opacity-25"></i>
                    <p class="text-white-50">Start typing above to search thousands of movies.</p>
                </div>
            <?php endif; ?>
        </div>

        <div class="mt-5 text-center border-top border-secondary border-opacity-25 pt-4">
            <a href="home.php" class="btn btn-outline-light rounded-pill px-5">
                <i class="fas fa-arrow-left me-2"></i> Back to Dashboard
            </a>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>